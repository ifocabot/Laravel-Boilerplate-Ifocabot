<?php

namespace App\Traits;

use App\Contracts\Approvable;
use App\Models\ApprovalRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait for models that require approval workflow
 * Use this trait in models like LeaveRequest, OvertimeRequest, etc.
 * 
 * Models should also implement App\Contracts\Approvable for full ERP support.
 */
trait HasApprovalWorkflow
{
    /**
     * Get the workflow type for this model
     * Override this in your model
     */
    abstract public function getWorkflowType(): string;

    /**
     * Get the approval context for condition evaluation
     * 
     * Override this in your model to provide context for:
     * - Step condition evaluation
     * - Resolver context (department, level, amount, etc.)
     * - Audit trail snapshot
     * 
     * @deprecated in complex workflows - implement Approvable interface instead
     * @return array<string, mixed>
     */
    public function getApprovalContext(): array
    {
        // Default implementation returns basic context
        // Override in model for richer context
        return [
            'requester_id' => $this->getRequesterId(),
        ];
    }

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
     * 
     * @throws \Exception if already has pending request
     */
    public function submitForApproval(): ApprovalRequest
    {
        // ⭐ Early guard: Prevent double submit at trait level
        if ($this->hasPendingApprovalRequest()) {
            throw new \Exception(
                "Cannot submit: This item already has a pending approval request. " .
                "Please wait for the current request to be processed or cancel it first."
            );
        }

        $service = app(ApprovalWorkflowService::class);

        return $service->submitForApproval(
            $this,
            $this->getWorkflowType(),
            $this->getRequesterId()
        );
    }

    /**
     * ⭐ Check if there's an active (pending/needs_configuration) approval request
     */
    public function hasActiveApprovalRequest(): bool
    {
        $status = $this->approvalRequest?->status;
        return in_array($status, [
            ApprovalRequest::STATUS_PENDING,
            ApprovalRequest::STATUS_NEEDS_CONFIGURATION,
        ]);
    }

    /**
     * Alias for hasActiveApprovalRequest
     */
    public function hasPendingApprovalRequest(): bool
    {
        return $this->hasActiveApprovalRequest();
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
     * Check if this request needs configuration
     */
    public function needsConfiguration(): bool
    {
        return $this->approvalRequest?->status === ApprovalRequest::STATUS_NEEDS_CONFIGURATION;
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
     * @param ApprovalRequest $request The approval request with all details
     */
    public function onWorkflowApproved(ApprovalRequest $request): void
    {
        // Default implementation does nothing
        // Override in model for custom behavior
        // 
        // Example:
        // $this->update(['status' => 'approved']);
    }

    /**
     * Callback when workflow is rejected
     * Override this in your model to add custom logic
     * 
     * @param ApprovalRequest $request The approval request with rejection details
     * @param string|null $reason Rejection reason
     */
    public function onWorkflowRejected(ApprovalRequest $request, ?string $reason = null): void
    {
        // Default implementation does nothing
        // Override in model for custom behavior
        //
        // Example:
        // $this->update(['status' => 'rejected', 'rejection_reason' => $reason]);
    }

    /**
     * Get the approval audit log
     */
    public function getApprovalAuditLog()
    {
        $request = $this->approvalRequest;

        if (!$request) {
            return collect();
        }

        return $request->events()
            ->with(['actor'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get failure reason if request needs configuration
     */
    public function getApprovalFailureReason(): ?string
    {
        return $this->approvalRequest?->failure_reason;
    }
}
