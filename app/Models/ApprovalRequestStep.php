<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestStep extends Model
{
    protected $fillable = [
        'approval_request_id',
        'workflow_step_id',
        'step_order',
        'approver_type',
        'approver_value',
        'conditions_snapshot',
        'approver_id',
        'resolver_type',
        'skip_reason',
        'resolved_at',
        'status',
        'notes',
        'actioned_at',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'conditions_snapshot' => 'array',
        'resolved_at' => 'datetime',
        'actioned_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_SKIPPED = 'skipped';

    /**
     * Skip reasons
     */
    public const SKIP_CONDITION_NOT_MET = 'condition_not_met';
    public const SKIP_SAME_APPROVER = 'same_approver';
    public const SKIP_APPROVER_NOT_FOUND = 'approver_not_found';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflowStep::class, 'workflow_step_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
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

    public function scopeSkipped($query)
    {
        return $query->where('status', self::STATUS_SKIPPED);
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Approve this step
     */
    public function approve(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'notes' => $notes,
            'actioned_at' => now(),
        ]);
    }

    /**
     * Reject this step
     */
    public function reject(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'notes' => $notes,
            'actioned_at' => now(),
        ]);
    }

    /**
     * Skip this step
     */
    public function skip(string $reason, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'skip_reason' => $reason,
            'notes' => $notes ?? $this->getSkipReasonLabel($reason),
            'actioned_at' => now(),
        ]);
    }

    /**
     * Mark as resolved with approver
     */
    public function markResolved(int $approverId, string $resolverType): void
    {
        $this->update([
            'approver_id' => $approverId,
            'resolver_type' => $resolverType,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Check if this step is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if this step is skipped
     */
    public function isSkipped(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }

    /**
     * Get skip reason label
     */
    public function getSkipReasonLabel(?string $reason = null): string
    {
        $reason = $reason ?? $this->skip_reason;

        return match ($reason) {
            self::SKIP_CONDITION_NOT_MET => 'Kondisi step tidak terpenuhi',
            self::SKIP_SAME_APPROVER => 'Approver sama dengan step sebelumnya',
            self::SKIP_APPROVER_NOT_FOUND => 'Tidak dapat menemukan approver',
            default => $reason ?? 'Dilewati',
        };
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
            self::STATUS_SKIPPED => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_SKIPPED => 'Dilewati',
            default => $this->status,
        };
    }
}
