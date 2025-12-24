<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestStep extends Model
{
    protected $fillable = [
        'approval_request_id',
        'step_order',
        'approver_id',
        'status',
        'notes',
        'actioned_at',
    ];

    protected $casts = [
        'step_order' => 'integer',
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
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
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
    public function skip(?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'notes' => $notes ?? 'Skipped automatically',
            'actioned_at' => now(),
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
