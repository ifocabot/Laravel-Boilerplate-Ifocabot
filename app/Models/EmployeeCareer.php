<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class EmployeeCareer extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = [
        'employee_id',
        'department_id',
        'position_id',
        'level_id',
        'branch_id',
        'manager_id',
        'start_date',
        'end_date',
        'is_active',
        'is_current', // NEW - added
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean', // NEW - added
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    /**
     * Attributes to include in audit
     */
    protected $auditInclude = [
        'employee_id',
        'department_id',
        'position_id',
        'level_id',
        'branch_id',
        'manager_id',
        'start_date',
        'end_date',
        'is_active',
        'is_current', // NEW - added
    ];

    /**
     * Generate tags for the audit
     */
    public function generateTags(): array
    {
        return ['employee-career', 'hr'];
    }

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'branch_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function subordinates()
    {
        return self::where('manager_id', $this->employee_id)
            ->where('is_active', true)
            ->with('employee');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    public function scopeByManager($query, $managerId)
    {
        return $query->where('manager_id', $managerId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Accessors
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->start_date) {
            return null;
        }

        $endDate = $this->end_date ?? now();

        $years = $this->start_date->diffInYears($endDate);
        $months = $this->start_date->copy()->addYears($years)->diffInMonths($endDate);

        $parts = [];
        if ($years > 0) {
            $parts[] = $years . ' tahun';
        }
        if ($months > 0) {
            $parts[] = $months . ' bulan';
        }

        return !empty($parts) ? implode(' ', $parts) : '< 1 bulan';
    }

    // REMOVED: getIsCurrentAttribute() accessor - conflicts with is_current column
    // The is_current column will be used directly instead

    public function getSubordinatesCountAttribute(): int
    {
        return self::where('manager_id', $this->employee_id)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Methods
     */
    public function deactivate($endDate = null, $notes = null)
    {
        $this->update([
            'is_active' => false,
            'is_current' => false, // NEW - also set is_current to false
            'end_date' => $endDate ?? now(),
            'notes' => $notes,
        ]);
    }

    public function getPromotionInfo()
    {
        $previousCareer = self::where('employee_id', $this->employee_id)
            ->where('start_date', '<', $this->start_date)
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$previousCareer) {
            return [
                'is_promotion' => false,
                'type' => 'initial',
                'changes' => [],
            ];
        }

        $changes = [];
        $isPromotion = false;

        if ($previousCareer->position_id !== $this->position_id) {
            $changes[] = [
                'type' => 'position',
                'from' => $previousCareer->position->name,
                'to' => $this->position->name,
            ];
            $isPromotion = true;
        }

        if ($previousCareer->department_id !== $this->department_id) {
            $changes[] = [
                'type' => 'department',
                'from' => $previousCareer->department->name,
                'to' => $this->department->name,
            ];
        }

        if ($previousCareer->level_id !== $this->level_id) {
            $changes[] = [
                'type' => 'level',
                'from' => $previousCareer->level->grade_code,
                'to' => $this->level->grade_code,
            ];
            $isPromotion = true;
        }

        if ($previousCareer->branch_id !== $this->branch_id) {
            $changes[] = [
                'type' => 'branch',
                'from' => $previousCareer->branch ? $previousCareer->branch->name : '-',
                'to' => $this->branch ? $this->branch->name : '-',
            ];
        }

        return [
            'is_promotion' => $isPromotion,
            'type' => $isPromotion ? 'promotion' : 'mutation',
            'changes' => $changes,
        ];
    }

    /**
     * Audit Transformations
     */
    public function transformAudit(array $data): array
    {
        try {
            if (isset($data['old_values']['employee_id']) && $data['old_values']['employee_id']) {
                $oldEmp = Employee::find($data['old_values']['employee_id']);
                $data['old_values']['employee_name'] = $oldEmp ? $oldEmp->full_name : 'None';
            }

            if (isset($data['new_values']['employee_id']) && $data['new_values']['employee_id']) {
                $newEmp = Employee::find($data['new_values']['employee_id']);
                $data['new_values']['employee_name'] = $newEmp ? $newEmp->full_name : 'None';
            }

            if (isset($data['old_values']['department_id']) && $data['old_values']['department_id']) {
                $oldDept = Department::find($data['old_values']['department_id']);
                $data['old_values']['department_name'] = $oldDept ? $oldDept->name : 'None';
            }

            if (isset($data['new_values']['department_id']) && $data['new_values']['department_id']) {
                $newDept = Department::find($data['new_values']['department_id']);
                $data['new_values']['department_name'] = $newDept ? $newDept->name : 'None';
            }

            if (isset($data['old_values']['position_id']) && $data['old_values']['position_id']) {
                $oldPos = Position::find($data['old_values']['position_id']);
                $data['old_values']['position_name'] = $oldPos ? $oldPos->name : 'None';
            }

            if (isset($data['new_values']['position_id']) && $data['new_values']['position_id']) {
                $newPos = Position::find($data['new_values']['position_id']);
                $data['new_values']['position_name'] = $newPos ? $newPos->name : 'None';
            }

            if (isset($data['old_values']['level_id']) && $data['old_values']['level_id']) {
                $oldLevel = Level::find($data['old_values']['level_id']);
                $data['old_values']['level_name'] = $oldLevel ? $oldLevel->name : 'None';
            }

            if (isset($data['new_values']['level_id']) && $data['new_values']['level_id']) {
                $newLevel = Level::find($data['new_values']['level_id']);
                $data['new_values']['level_name'] = $newLevel ? $newLevel->name : 'None';
            }

            if (isset($data['old_values']['manager_id']) && $data['old_values']['manager_id']) {
                $oldMgr = Employee::find($data['old_values']['manager_id']);
                $data['old_values']['manager_name'] = $oldMgr ? $oldMgr->full_name : 'None';
            }

            if (isset($data['new_values']['manager_id']) && $data['new_values']['manager_id']) {
                $newMgr = Employee::find($data['new_values']['manager_id']);
                $data['new_values']['manager_name'] = $newMgr ? $newMgr->full_name : 'None';
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to transform employee career audit data', [
                'error' => $e->getMessage()
            ]);
        }

        return $data;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new active career, deactivate previous ones
        static::creating(function ($career) {
            if ($career->is_active) {
                // Deactivate all previous active careers
                self::where('employee_id', $career->employee_id)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'is_current' => false, // NEW
                        'end_date' => $career->start_date->copy()->subDay(),
                    ]);

                // Set new career as current if is_active
                if (!isset($career->is_current)) {
                    $career->is_current = true;
                }
            }
        });

        // When updating to active, deactivate others
        static::updating(function ($career) {
            if ($career->is_active && $career->isDirty('is_active')) {
                self::where('employee_id', $career->employee_id)
                    ->where('id', '!=', $career->id)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'is_current' => false, // NEW
                        'end_date' => $career->start_date,
                    ]);

                // Set this career as current
                $career->is_current = true;
            }

            // If marking as current, remove current flag from others
            if (isset($career->is_current) && $career->is_current && $career->isDirty('is_current')) {
                self::where('employee_id', $career->employee_id)
                    ->where('id', '!=', $career->id)
                    ->update(['is_current' => false]);
            }
        });
    }
}