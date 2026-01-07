<?php

namespace App\Services\Approval\Resolvers;

use App\Contracts\ApproverResolverInterface;
use App\Models\Employee;
use App\Models\User;
use App\Services\Approval\ApproverResolution;
use Illuminate\Support\Facades\Log;

/**
 * Resolves approver(s) by role
 * 
 * approver_value: role name (e.g., "finance_manager", "hr_admin")
 * 
 * Can return multiple approvers for quorum/parallel approval.
 */
class RoleResolver implements ApproverResolverInterface
{
    public function getType(): string
    {
        return 'role';
    }

    public function resolve(Employee $requester, array $context, ?string $value): ApproverResolution
    {
        if (!$value) {
            return ApproverResolution::failed('Role tidak ditentukan');
        }

        // Support multiple roles separated by |
        $roles = array_map('trim', explode('|', $value));

        $users = User::whereHas('roles', function ($q) use ($roles) {
            $q->whereIn('name', $roles);
        })
            ->where('id', '!=', $requester->user_id) // Exclude requester
            ->get();

        if ($users->isEmpty()) {
            Log::warning('RoleResolver: No users found with role', [
                'roles' => $roles,
            ]);
            return ApproverResolution::failed(
                'Tidak ada user dengan role: ' . implode(', ', $roles),
                ['roles' => $roles]
            );
        }

        // Return all users with the role (for multi-approver support)
        $approverIds = $users->pluck('id')->toArray();

        Log::info('RoleResolver: Found users with role', [
            'roles' => $roles,
            'count' => count($approverIds),
        ]);

        return ApproverResolution::resolvedMultiple($approverIds, [
            'roles' => $roles,
            'source' => 'role_based',
        ]);
    }
}
