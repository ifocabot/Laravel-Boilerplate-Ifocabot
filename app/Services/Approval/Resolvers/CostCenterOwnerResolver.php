<?php

namespace App\Services\Approval\Resolvers;

use App\Contracts\ApproverResolverInterface;
use App\Models\Employee;
use App\Services\Approval\ApproverResolution;
use Illuminate\Support\Facades\Log;

/**
 * Resolves the cost center owner as approver
 * 
 * Uses context['cost_center_id'] to find the owner.
 * Useful for procurement workflows.
 */
class CostCenterOwnerResolver implements ApproverResolverInterface
{
    public function getType(): string
    {
        return 'cost_center_owner';
    }

    public function resolve(Employee $requester, array $context, ?string $value): ApproverResolution
    {
        // Get cost_center_id from context or value
        $costCenterId = $context['cost_center_id'] ?? $value;

        if (!$costCenterId) {
            return ApproverResolution::failed(
                'Cost center tidak ditentukan',
                ['context_keys' => array_keys($context)]
            );
        }

        // Try to find CostCenter model if it exists
        // Note: Adjust this based on your actual CostCenter model
        $costCenterClass = 'App\\Models\\CostCenter';

        if (!class_exists($costCenterClass)) {
            // Fallback: Check if there's an account or location that acts as cost center
            Log::warning('CostCenterOwnerResolver: CostCenter model not found, trying Location');

            // Try Location as cost center
            $location = \App\Models\Location::find($costCenterId);
            if ($location) {
                // Check if location has a manager or owner
                // This is a placeholder - adjust based on your schema
                return ApproverResolution::failed(
                    'Cost center owner not configured for location',
                    ['cost_center_id' => $costCenterId]
                );
            }
        } else {
            $costCenter = $costCenterClass::find($costCenterId);

            if (!$costCenter) {
                return ApproverResolution::failed(
                    'Cost center tidak ditemukan: ' . $costCenterId,
                    ['cost_center_id' => $costCenterId]
                );
            }

            // Assume cost center has owner_id or manager_id
            $ownerId = $costCenter->owner_id ?? $costCenter->manager_id ?? null;

            if ($ownerId) {
                // Verify it's not the requester
                if ($ownerId === $requester->user_id) {
                    return ApproverResolution::skipped(
                        'Requester adalah owner cost center',
                        ['cost_center' => $costCenter->name ?? $costCenterId]
                    );
                }

                Log::info('CostCenterOwnerResolver: Resolved cost center owner', [
                    'cost_center_id' => $costCenterId,
                    'owner_id' => $ownerId,
                ]);

                return ApproverResolution::resolved($ownerId, [
                    'source' => 'cost_center.owner_id',
                    'cost_center_id' => $costCenterId,
                ]);
            }
        }

        return ApproverResolution::failed(
            'Cost center tidak memiliki owner',
            ['cost_center_id' => $costCenterId]
        );
    }
}
