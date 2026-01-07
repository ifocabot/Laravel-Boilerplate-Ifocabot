<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable audit log for approval workflow events
 */
class ApprovalEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'approval_request_id',
        'approval_request_step_id',
        'workflow_step_id',
        'event_type',
        'actor_id',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Event types
     */
    public const TYPE_CREATED = 'created';
    public const TYPE_STEP_CREATED = 'step_created';
    public const TYPE_STEP_SKIPPED = 'step_skipped';
    public const TYPE_STEP_RESOLVED = 'step_resolved';
    public const TYPE_APPROVED = 'approved';
    public const TYPE_REJECTED = 'rejected';
    public const TYPE_FAILED_TO_RESOLVE = 'failed_to_resolve';
    public const TYPE_ESCALATED = 'escalated';
    public const TYPE_CONCURRENCY_CONFLICT = 'concurrency_conflict';
    public const TYPE_CANCELLED = 'cancelled';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function approvalRequestStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequestStep::class);
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflowStep::class, 'workflow_step_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeForRequest($query, int $requestId)
    {
        return $query->where('approval_request_id', $requestId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * ========================================
     * FACTORY METHODS
     * ========================================
     */

    /**
     * Create an event with standard payload structure
     */
    public static function log(
        int $requestId,
        string $eventType,
        ?int $actorId = null,
        array $payload = [],
        ?int $stepId = null,
        ?int $workflowStepId = null,
    ): self {
        return self::create([
            'approval_request_id' => $requestId,
            'approval_request_step_id' => $stepId,
            'workflow_step_id' => $workflowStepId,
            'event_type' => $eventType,
            'actor_id' => $actorId ?? auth()->id(),
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getEventTypeLabelAttribute(): string
    {
        return match ($this->event_type) {
            self::TYPE_CREATED => 'Dibuat',
            self::TYPE_STEP_CREATED => 'Step Dibuat',
            self::TYPE_STEP_SKIPPED => 'Step Dilewati',
            self::TYPE_STEP_RESOLVED => 'Approver Ditentukan',
            self::TYPE_APPROVED => 'Disetujui',
            self::TYPE_REJECTED => 'Ditolak',
            self::TYPE_FAILED_TO_RESOLVE => 'Gagal Menentukan Approver',
            self::TYPE_ESCALATED => 'Dieskalasi',
            self::TYPE_CONCURRENCY_CONFLICT => 'Konflik Konkurensi',
            self::TYPE_CANCELLED => 'Dibatalkan',
            default => $this->event_type,
        };
    }
}
