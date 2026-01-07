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
        'context',
        'failure_code',
        'failure_reason',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'context' => 'array',
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
    public const STATUS_NEEDS_CONFIGURATION = 'needs_configuration';
    public const STATUS_FAILED = 'failed';

    /**
     * Failure codes
     */
    public const FAILURE_NO_APPROVER = 'NO_APPROVER_RESOLVED';
    public const FAILURE_INVALID_CONDITION = 'INVALID_CONDITION';
    public const FAILURE_CONCURRENCY = 'CONCURRENCY_CONFLICT';
    public const FAILURE_WORKFLOW_NOT_FOUND = 'WORKFLOW_NOT_FOUND';
    public const FAILURE_CONTEXT_MISSING = 'CONTEXT_MISSING';
    public const FAILURE_NO_PENDING_STEPS = 'NO_PENDING_STEPS';

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

    public function events(): HasMany
    {
        return $this->hasMany(ApprovalEvent::class)->orderBy('created_at');
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

    public function scopeNeedsConfiguration($query)
    {
        return $query->where('status', self::STATUS_NEEDS_CONFIGURATION);
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
     * Mark request as needing configuration
     */
    public function markNeedsConfiguration(string $code, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_NEEDS_CONFIGURATION,
            'failure_code' => $code,
            'failure_reason' => $reason,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark request as failed
     */
    public function markFailed(string $code, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_code' => $code,
            'failure_reason' => $reason,
            'completed_at' => now(),
        ]);
    }

    /**
     * Approve current step (called by service with proper locking)
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
     * Reject current step (called by service with proper locking)
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

        // Trigger rejection callback on the requestable model
        $this->triggerRejectionCallback($notes);

        return true;
    }

    /**
     * Trigger rejection callback on the requestable model
     */
    protected function triggerRejectionCallback(?string $notes = null): void
    {
        $requestable = $this->requestable;

        if ($requestable && method_exists($requestable, 'onWorkflowRejected')) {
            $requestable->onWorkflowRejected($this, $notes);

            \Log::info('Workflow rejection callback triggered', [
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
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

            // Trigger callback on the requestable model
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
            $requestable->onWorkflowApproved($this);

            \Log::info('Workflow approval callback triggered', [
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
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
            self::STATUS_NEEDS_CONFIGURATION => 'bg-orange-100 text-orange-700',
            self::STATUS_FAILED => 'bg-red-100 text-red-700',
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
            self::STATUS_NEEDS_CONFIGURATION => 'Butuh Konfigurasi',
            self::STATUS_FAILED => 'Gagal',
            default => $this->status,
        };
    }
}
