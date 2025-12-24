<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'workflow_id',
        'requestable_type',
        'requestable_id',
        'requester_id',
        'current_step',
        'status',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRequestStep::class)->orderBy('step_order');
    }

    public function currentStepRecord()
    {
        return $this->hasOne(ApprovalRequestStep::class)
            ->where('step_order', $this->current_step);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForRequester($query, int $requesterId)
    {
        return $query->where('requester_id', $requesterId);
    }

    public function scopeForApprover($query, int $userId)
    {
        return $query->whereHas('steps', function ($q) use ($userId) {
            $q->where('approver_id', $userId)
                ->where('status', ApprovalRequestStep::STATUS_PENDING);
        });
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Check if current user can approve/reject
     */
    public function canBeActionedBy(int $userId): bool
    {
        $currentStep = $this->steps()->where('step_order', $this->current_step)->first();

        return $currentStep &&
            $currentStep->status === ApprovalRequestStep::STATUS_PENDING &&
            $currentStep->approver_id === $userId;
    }

    /**
     * Approve current step
     */
    public function approveStep(int $userId, ?string $notes = null): bool
    {
        if (!$this->canBeActionedBy($userId)) {
            return false;
        }

        $currentStep = $this->steps()->where('step_order', $this->current_step)->first();
        $currentStep->approve($notes);

        // Move to next step or complete
        $this->moveToNextStep();

        return true;
    }

    /**
     * Reject current step
     */
    public function rejectStep(int $userId, ?string $notes = null): bool
    {
        if (!$this->canBeActionedBy($userId)) {
            return false;
        }

        $currentStep = $this->steps()->where('step_order', $this->current_step)->first();
        $currentStep->reject($notes);

        // Mark entire request as rejected
        $this->update([
            'status' => self::STATUS_REJECTED,
            'completed_at' => now(),
        ]);

        // ⭐ Trigger rejection callback on the requestable model
        $this->triggerRejectionCallback($userId, $notes);

        return true;
    }

    /**
     * Trigger rejection callback on the requestable model
     */
    protected function triggerRejectionCallback(int $userId, ?string $notes = null): void
    {
        $requestable = $this->requestable;

        if ($requestable && method_exists($requestable, 'onWorkflowRejected')) {
            $requestable->onWorkflowRejected($userId, $notes);

            \Log::info('Workflow rejection callback triggered', [
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
                'rejected_by' => $userId,
            ]);
        }
    }

    /**
     * Move to next step or complete approval
     */
    public function moveToNextStep(): void
    {
        $nextStep = $this->steps()
            ->where('step_order', '>', $this->current_step)
            ->where('status', ApprovalRequestStep::STATUS_PENDING)
            ->first();

        if ($nextStep) {
            $this->update(['current_step' => $nextStep->step_order]);
        } else {
            // All steps completed - mark as approved
            $this->update([
                'status' => self::STATUS_APPROVED,
                'completed_at' => now(),
            ]);

            // ⭐ Trigger callback on the requestable model
            $this->triggerApprovalCallback();
        }
    }

    /**
     * Trigger approval callback on the requestable model
     */
    protected function triggerApprovalCallback(): void
    {
        $requestable = $this->requestable;

        if ($requestable && method_exists($requestable, 'onWorkflowApproved')) {
            // Get the last approver from the last step
            $lastStep = $this->steps()->whereNotNull('actioned_at')->orderBy('step_order', 'desc')->first();
            $approverId = $lastStep?->approver_id ?? auth()->id();

            $requestable->onWorkflowApproved($approverId);

            \Log::info('Workflow approval callback triggered', [
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
                'approver_id' => $approverId,
            ]);
        }
    }

    /**
     * Cancel the approval request
     */
    public function cancel(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);

        // Mark all pending steps as skipped
        $this->steps()
            ->where('status', ApprovalRequestStep::STATUS_PENDING)
            ->update(['status' => ApprovalRequestStep::STATUS_SKIPPED]);

        return true;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-700',
            self::STATUS_APPROVED => 'bg-green-100 text-green-700',
            self::STATUS_REJECTED => 'bg-red-100 text-red-700',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => $this->status,
        };
    }
}
