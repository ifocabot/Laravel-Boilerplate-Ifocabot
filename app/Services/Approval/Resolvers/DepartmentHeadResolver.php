<?php

namespace App\Services\Approval\Resolvers;

use App\Contracts\ApproverResolverInterface;
use App\Models\Employee;
use App\Services\Approval\ApproverResolution;
use Illuminate\Support\Facades\Log;

/**
 * Resolves the department head of the requester
 */
class DepartmentHeadResolver implements ApproverResolverInterface
{
    public function getType(): string
    {
        return 'department_head';
    }

    public function resolve(Employee $requester, array $context, ?string $value): ApproverResolution
    {
        $currentCareer = $requester->current_career;

        if (!$currentCareer || !$currentCareer->department) {
            return ApproverResolution::failed(
                'Requester tidak memiliki department',
                ['requester_id' => $requester->id]
            );
        }

        $department = $currentCareer->department;

        // Check department manager_id
        if ($department->manager_id) {
            // Verify it's not the requester
            if ($department->manager_id === $requester->user_id) {
                return ApproverResolution::skipped(
                    'Requester adalah kepala departemen',
                    ['department' => $department->name]
                );
            }

            Log::info('DepartmentHeadResolver: Resolved department head', [
                'department' => $department->name,
                'manager_id' => $department->manager_id,
            ]);

            return ApproverResolution::resolved($department->manager_id, [
                'source' => 'department.manager_id',
                'department' => $department->name,
            ]);
        }

        return ApproverResolution::failed(
            'Department tidak memiliki manager: ' . $department->name,
            ['department_id' => $department->id]
        );
    }
}
