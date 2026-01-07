<?php

namespace App\Services\Approval\Resolvers;

use App\Contracts\ApproverResolverInterface;
use App\Models\Employee;
use App\Models\User;
use App\Services\Approval\ApproverResolution;
use Illuminate\Support\Facades\Log;

/**
 * Resolves the direct supervisor of the requester
 */
class DirectSupervisorResolver implements ApproverResolverInterface
{
    public function getType(): string
    {
        return 'direct_supervisor';
    }

    public function resolve(Employee $requester, array $context, ?string $value): ApproverResolution
    {
        // Try manager_id from current_career first
        $currentCareer = $requester->current_career;

        if ($currentCareer && $currentCareer->manager_id) {
            $manager = Employee::find($currentCareer->manager_id);
            if ($manager?->user_id) {
                Log::info('DirectSupervisorResolver: Found manager from career', [
                    'requester' => $requester->full_name,
                    'manager' => $manager->full_name,
                ]);
                return ApproverResolution::resolved($manager->user_id, [
                    'source' => 'current_career.manager_id',
                    'manager_employee_id' => $manager->id,
                ]);
            }
        }

        // Fallback: Department manager
        if ($currentCareer?->department?->manager_id) {
            Log::info('DirectSupervisorResolver: Fallback to department manager', [
                'requester' => $requester->full_name,
                'department' => $currentCareer->department->name,
            ]);
            return ApproverResolution::resolved($currentCareer->department->manager_id, [
                'source' => 'department.manager_id',
            ]);
        }

        // No supervisor found
        Log::warning('DirectSupervisorResolver: No supervisor found', [
            'requester_id' => $requester->id,
            'requester' => $requester->full_name,
        ]);

        return ApproverResolution::failed(
            'Tidak dapat menemukan atasan langsung',
            ['requester_id' => $requester->id]
        );
    }
}
