<?php

namespace App\Services\Approval\Resolvers;

use App\Contracts\ApproverResolverInterface;
use App\Models\Employee;
use App\Models\Level;
use App\Services\Approval\ApproverResolution;
use Illuminate\Support\Facades\Log;

/**
 * Resolves approver based on relative level from requester
 * 
 * approver_value format:
 * - "+1" = 1 level up from requester
 * - "+2" = 2 levels up from requester  
 * - "3" = specific level ID (legacy support)
 */
class RelativeLevelResolver implements ApproverResolverInterface
{
    public function getType(): string
    {
        return 'relative_level';
    }

    public function resolve(Employee $requester, array $context, ?string $value): ApproverResolution
    {
        $currentCareer = $requester->current_career;

        if (!$currentCareer || !$currentCareer->level) {
            Log::warning('RelativeLevelResolver: Requester has no level', [
                'requester_id' => $requester->id,
            ]);
            return ApproverResolution::failed('Requester tidak memiliki level', [
                'requester_id' => $requester->id,
            ]);
        }

        $requesterLevel = $currentCareer->level;
        $departmentId = $currentCareer->department_id;

        // Parse value: "+1", "+2", or level_id
        $stepsUp = $this->parseStepsUp($value);

        // Get higher levels
        $higherLevels = Level::where('approval_order', '>', $requesterLevel->approval_order)
            ->orderBy('approval_order', 'asc')
            ->get();

        if ($higherLevels->isEmpty()) {
            return ApproverResolution::failed('Tidak ada level yang lebih tinggi', [
                'requester_level' => $requesterLevel->name,
            ]);
        }

        // Skip to N-th level up
        $levelsToCheck = $higherLevels->slice($stepsUp - 1);

        // Escalation: try each level until we find an approver
        foreach ($levelsToCheck as $targetLevel) {
            // Try same department first
            $approver = Employee::whereHas('current_career', function ($q) use ($targetLevel, $departmentId, $requester) {
                $q->where('level_id', $targetLevel->id)
                    ->where('department_id', $departmentId)
                    ->where('employee_id', '!=', $requester->id);
            })->first();

            if ($approver?->user_id) {
                Log::info('RelativeLevelResolver: Found approver in same department', [
                    'requester' => $requester->full_name,
                    'approver' => $approver->full_name,
                    'level' => $targetLevel->name,
                ]);
                return ApproverResolution::resolved($approver->user_id, [
                    'source' => 'same_department',
                    'level' => $targetLevel->name,
                    'escalated' => $targetLevel->approval_order > ($requesterLevel->approval_order + $stepsUp),
                ]);
            }

            // Try any department
            $approver = Employee::whereHas('current_career', function ($q) use ($targetLevel, $requester) {
                $q->where('level_id', $targetLevel->id)
                    ->where('employee_id', '!=', $requester->id);
            })->first();

            if ($approver?->user_id) {
                Log::info('RelativeLevelResolver: Escalated to other department', [
                    'requester' => $requester->full_name,
                    'approver' => $approver->full_name,
                    'level' => $targetLevel->name,
                ]);
                return ApproverResolution::resolved($approver->user_id, [
                    'source' => 'cross_department',
                    'level' => $targetLevel->name,
                    'escalated' => true,
                ]);
            }
        }

        return ApproverResolution::failed('Tidak ada approver pada level yang lebih tinggi', [
            'levels_checked' => $levelsToCheck->pluck('name')->toArray(),
        ]);
    }

    /**
     * Parse steps up from value
     */
    protected function parseStepsUp(?string $value): int
    {
        if (!$value) {
            return 1;
        }

        // "+1", "+2" format
        if (str_starts_with($value, '+')) {
            return max(1, (int) substr($value, 1));
        }

        // Legacy: assume it's steps up if small number
        $numValue = (int) $value;
        if ($numValue > 0 && $numValue <= 5) {
            return $numValue;
        }

        return 1;
    }
}
