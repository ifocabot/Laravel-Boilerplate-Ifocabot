<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalWorkflowStep extends Model
{
    protected $fillable = [
        'workflow_id',
        'step_order',
        'approver_type',
        'approver_value',
        'is_required',
        'can_skip_if_same',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'is_required' => 'boolean',
        'can_skip_if_same' => 'boolean',
    ];

    /**
     * Approver types
     */
    public const TYPE_DIRECT_SUPERVISOR = 'direct_supervisor';
    public const TYPE_POSITION_LEVEL = 'position_level';
    public const TYPE_SPECIFIC_USER = 'specific_user';
    public const TYPE_NEXT_LEVEL_UP = 'next_level_up';       // +1 level from requester
    public const TYPE_SECOND_LEVEL_UP = 'second_level_up';   // +2 levels from requester

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Resolve the approver User for this step based on the requester Employee
     */
    public function resolveApprover(Employee $requester): ?User
    {
        switch ($this->approver_type) {
            case self::TYPE_DIRECT_SUPERVISOR:
                return $this->resolveDirectSupervisor($requester);

            case self::TYPE_POSITION_LEVEL:
                return $this->resolveByPositionLevel($requester);

            case self::TYPE_SPECIFIC_USER:
                return $this->resolveSpecificUser();

            case self::TYPE_NEXT_LEVEL_UP:
                return $this->resolveByLevelUp($requester, 1);

            case self::TYPE_SECOND_LEVEL_UP:
                return $this->resolveByLevelUp($requester, 2);

            default:
                return null;
        }
    }

    /**
     * Get direct supervisor of the employee
     * Uses current_career.manager_id to find the manager
     */
    protected function resolveDirectSupervisor(Employee $requester): ?User
    {
        // ⭐ First, check manager_id from current_career (primary method)
        $currentCareer = $requester->current_career;
        if ($currentCareer && $currentCareer->manager_id) {
            $manager = Employee::find($currentCareer->manager_id);
            if ($manager?->user) {
                \Log::info('Resolved direct supervisor from current_career.manager_id', [
                    'requester' => $requester->full_name,
                    'manager' => $manager->full_name,
                ]);
                return $manager->user;
            }
        }

        // Fallback: Try department.manager_id
        if ($currentCareer && $currentCareer->department && $currentCareer->department->manager_id) {
            $deptManager = User::find($currentCareer->department->manager_id);
            if ($deptManager) {
                \Log::info('Resolved direct supervisor from department.manager_id', [
                    'requester' => $requester->full_name,
                    'manager' => $deptManager->name,
                ]);
                return $deptManager;
            }
        }

        // Fallback: Get first admin/HR user as approver
        $adminUser = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'hr', 'super-admin', 'Admin', 'Super Admin']);
        })->first();

        if ($adminUser) {
            \Log::warning('Fallback to admin as approver - no manager found', [
                'requester' => $requester->full_name,
                'admin' => $adminUser->name,
            ]);
            return $adminUser;
        }

        // Last resort: First user with any role
        return User::first();
    }

    /**
     * Resolve approver by position level
     * approver_value should be a Level ID
     */
    protected function resolveByPositionLevel(Employee $requester): ?User
    {
        if (!$this->approver_value) {
            return null;
        }

        // Find employee in same department with specified level
        $approver = Employee::where('department_id', $requester->department_id)
            ->where('id', '!=', $requester->id)
            ->where('level_id', $this->approver_value)
            ->first();

        return $approver?->user;
    }

    /**
     * Resolve specific user
     * approver_value should be a User ID
     */
    protected function resolveSpecificUser(): ?User
    {
        if (!$this->approver_value) {
            return null;
        }

        return User::find($this->approver_value);
    }

    /**
     * Resolve approver by level up from requester's level
     * With ESCALATION: if target level is vacant, automatically go to next higher level
     * 
     * @param Employee $requester The employee making the request
     * @param int $stepsUp Number of levels to go up (1 = immediate higher, 2 = skip-level)
     */
    protected function resolveByLevelUp(Employee $requester, int $stepsUp = 1): ?User
    {
        // Get requester's current level
        $currentCareer = $requester->current_career;
        if (!$currentCareer || !$currentCareer->level) {
            \Log::warning('Cannot resolve level-based approver: requester has no level', [
                'requester' => $requester->full_name,
            ]);
            return $this->resolveDirectSupervisor($requester);
        }

        $requesterLevel = $currentCareer->level;
        $departmentId = $currentCareer->department_id;

        // Get all levels higher than requester's level
        $higherLevels = Level::where('approval_order', '>', $requesterLevel->approval_order)
            ->orderBy('approval_order', 'asc')
            ->get();

        if ($higherLevels->isEmpty()) {
            \Log::info('No higher levels exist for escalation', [
                'requester' => $requester->full_name,
                'requester_level' => $requesterLevel->name,
            ]);
            return null;
        }

        // Skip to the N-th level up as starting point
        $startIndex = $stepsUp - 1;
        $levelsToCheck = $higherLevels->slice($startIndex);

        // ⭐ ESCALATION LOGIC: Try each higher level until we find an approver
        foreach ($levelsToCheck as $targetLevel) {
            // Try to find approver in same department first
            $approver = Employee::whereHas('current_career', function ($q) use ($targetLevel, $departmentId, $requester) {
                $q->where('level_id', $targetLevel->id)
                    ->where('department_id', $departmentId)
                    ->where('employee_id', '!=', $requester->id);
            })->first();

            if ($approver?->user) {
                \Log::info('Resolved level-up approver in same department', [
                    'requester' => $requester->full_name,
                    'approver' => $approver->full_name,
                    'target_level' => $targetLevel->name,
                    'escalated' => $targetLevel->approval_order > ($requesterLevel->approval_order + $stepsUp),
                ]);
                return $approver->user;
            }

            // Try any employee at this level across the organization
            $approver = Employee::whereHas('current_career', function ($q) use ($targetLevel, $requester) {
                $q->where('level_id', $targetLevel->id)
                    ->where('employee_id', '!=', $requester->id);
            })->first();

            if ($approver?->user) {
                \Log::info('Resolved level-up approver (escalated to other dept)', [
                    'requester' => $requester->full_name,
                    'approver' => $approver->full_name,
                    'target_level' => $targetLevel->name,
                ]);
                return $approver->user;
            }

            // No approver at this level, continue to next higher level (escalation)
            \Log::info('Level vacant, escalating to next level', [
                'requester' => $requester->full_name,
                'vacant_level' => $targetLevel->name,
            ]);
        }

        // No approver found at any level
        \Log::warning('No approver found at any higher level', [
            'requester' => $requester->full_name,
            'levels_checked' => $levelsToCheck->pluck('name')->toArray(),
        ]);

        return null;
    }

    /**
     * Get approver type label
     */
    public function getApproverTypeLabelAttribute(): string
    {
        return match ($this->approver_type) {
            self::TYPE_DIRECT_SUPERVISOR => 'Atasan Langsung',
            self::TYPE_POSITION_LEVEL => 'Berdasarkan Level',
            self::TYPE_SPECIFIC_USER => 'User Tertentu',
            self::TYPE_NEXT_LEVEL_UP => 'Level +1 (Atasan)',
            self::TYPE_SECOND_LEVEL_UP => 'Level +2 (Skip-Level)',
            default => $this->approver_type,
        };
    }
}
