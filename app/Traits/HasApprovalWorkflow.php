<?php

namespace App\Traits;

use App\Models\ApprovalRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait for models that require approval workflow
 * Use this trait in models like LeaveRequest, OvertimeRequest, etc.
 */
trait HasApprovalWorkflow
{
    /**
     * Get the workflow type for this model
     * Override this in your model
     */
    abstract public function getWorkflowType(): string;

    /**
     * Get the requester employee ID
     * Override this if your model has different field name
     */
    public function getRequesterId(): int
    {
        return $this->employee_id;
    }

    /**
     * Relationship to approval request
     */
    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(ApprovalRequest::class, 'requestable');
    }

    /**
     * Submit this model for approval
     */
    public function submitForApproval(): ApprovalRequest
    {
        $service = app(ApprovalWorkflowService::class);

        return $service->submitForApproval(
            $this,
            $this->getWorkflowType(),
            $this->getRequesterId()
        );
    }

    /**
     * Get current approval status
     */
    public function getApprovalStatus(): ?string
    {
        return $this->approvalRequest?->status;
    }

    /**
     * Check if this request is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->approvalRequest?->status === ApprovalRequest::STATUS_PENDING;
    }

    /**
     * Check if this request is approved
     */
    public function isApproved(): bool
    {
        return $this->approvalRequest?->status === ApprovalRequest::STATUS_APPROVED;
    }

    /**
     * Check if this request is rejected
     */
    public function isRejected(): bool
    {
        return $this->approvalRequest?->status === ApprovalRequest::STATUS_REJECTED;
    }

    /**
     * Get the current approval step
     */
    public function getCurrentApprovalStep(): ?int
    {
        return $this->approvalRequest?->current_step;
    }

    /**
     * Cancel the approval request
     */
    public function cancelApproval(): bool
    {
        return $this->approvalRequest?->cancel() ?? false;
    }

    /**
     * Get approval status label
     */
    public function getApprovalStatusLabel(): string
    {
        return $this->approvalRequest?->status_label ?? 'Belum Diajukan';
    }

    /**
     * Get approval status badge class
     */
    public function getApprovalStatusBadgeClass(): string
    {
        return $this->approvalRequest?->status_badge_class ?? 'bg-gray-100 text-gray-600';
    }

    /**
     * Callback when workflow is fully approved
     * Override this in your model to add custom logic
     * 
     * @param int $approverId The user ID who gave final approval
     */
    public function onWorkflowApproved(int $approverId): void
    {
        // Default implementation does nothing
        // Override in model for custom behavior
    }

    /**
     * Callback when workflow is rejected
     * Override this in your model to add custom logic
     * 
     * @param int $approverId The user ID who rejected
     * @param string|null $reason Rejection reason
     */
    public function onWorkflowRejected(int $approverId, ?string $reason = null): void
    {
        // Default implementation does nothing
        // Override in model for custom behavior
    }
}
