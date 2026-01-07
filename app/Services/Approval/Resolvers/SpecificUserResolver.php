<?php

namespace App\Services\Approval\Resolvers;

use App\Contracts\ApproverResolverInterface;
use App\Models\Employee;
use App\Models\User;
use App\Services\Approval\ApproverResolution;
use Illuminate\Support\Facades\Log;

/**
 * Resolves a specific user as approver
 * 
 * approver_value: user ID
 */
class SpecificUserResolver implements ApproverResolverInterface
{
    public function getType(): string
    {
        return 'specific_user';
    }

    public function resolve(Employee $requester, array $context, ?string $value): ApproverResolution
    {
        if (!$value) {
            return ApproverResolution::failed('User ID tidak ditentukan');
        }

        $userId = (int) $value;
        $user = User::find($userId);

        if (!$user) {
            Log::warning('SpecificUserResolver: User not found', [
                'user_id' => $userId,
            ]);
            return ApproverResolution::failed(
                'User tidak ditemukan: ' . $userId,
                ['user_id' => $userId]
            );
        }

        // Check if it's the same as requester
        if ($user->id === $requester->user_id) {
            return ApproverResolution::skipped(
                'Approver sama dengan requester',
                ['user_id' => $userId]
            );
        }

        Log::info('SpecificUserResolver: Resolved specific user', [
            'user_id' => $user->id,
            'user_name' => $user->name,
        ]);

        return ApproverResolution::resolved($user->id, [
            'source' => 'specific_user',
            'user_name' => $user->name,
        ]);
    }
}
