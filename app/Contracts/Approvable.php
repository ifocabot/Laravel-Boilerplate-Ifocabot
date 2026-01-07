<?php

namespace App\Contracts;

use App\Models\ApprovalRequest;

/**
 * Interface for models that can go through approval workflow
 * 
 * Implement this in models like LeaveRequest, OvertimeRequest, PurchaseOrder, etc.
 */
interface Approvable
{
    /**
     * Get the workflow type identifier for this model
     * 
     * @return string e.g., 'leave', 'overtime', 'purchase_order'
     */
    public function getWorkflowType(): string;

    /**
     * Get the approval context for condition evaluation
     * 
     * This context is used to:
     * - Evaluate step conditions (e.g., amount > 1000000)
     * - Pass to resolvers for dynamic approver resolution
     * - Store as snapshot for audit trail
     * 
     * @return array<string, mixed> Context data
     */
    public function getApprovalContext(): array;

    /**
     * Callback when workflow is fully approved
     * 
     * @param ApprovalRequest $request The approval request with all step details
     */
    public function onWorkflowApproved(ApprovalRequest $request): void;

    /**
     * Callback when workflow is rejected
     * 
     * @param ApprovalRequest $request The approval request with rejection details
     * @param string|null $reason Optional rejection reason
     */
    public function onWorkflowRejected(ApprovalRequest $request, ?string $reason = null): void;
}
