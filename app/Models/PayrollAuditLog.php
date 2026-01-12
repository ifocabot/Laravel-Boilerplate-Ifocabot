<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PayrollAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'actor_id',
        'action',
        'old_values',
        'new_values',
        'context',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'context' => 'array',
        'created_at' => 'datetime',
    ];

    // Common actions
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_SLIP_GENERATED = 'slip_generated';
    public const ACTION_SLIP_RECALCULATED = 'slip_recalculated';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_PAID = 'paid';
    public const ACTION_CLOSED = 'closed';
    public const ACTION_ADJUSTMENT_APPLIED = 'adjustment_applied';
    public const ACTION_ATTENDANCE_LOCKED = 'attendance_locked';

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Log an action
     */
    public static function log(
        Model $auditable,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $context = null
    ): self {
        return self::create([
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'actor_id' => auth()->id(),
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'context' => $context,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Log status change
     */
    public static function logStatusChange(Model $auditable, string $oldStatus, string $newStatus, ?string $reason = null): self
    {
        return self::log(
            $auditable,
            self::ACTION_STATUS_CHANGED,
            ['status' => $oldStatus],
            ['status' => $newStatus],
            ['reason' => $reason]
        );
    }

    /**
     * Scope: for specific model
     */
    public function scopeForModel($query, Model $model)
    {
        return $query->where('auditable_type', get_class($model))
            ->where('auditable_id', $model->getKey());
    }

    /**
     * Scope: by action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get formatted description
     */
    public function getDescriptionAttribute(): string
    {
        $actor = $this->actor?->name ?? 'System';
        $model = class_basename($this->auditable_type);

        return match ($this->action) {
            self::ACTION_STATUS_CHANGED => "{$actor} changed {$model} status from {$this->old_values['status']} to {$this->new_values['status']}",
            self::ACTION_APPROVED => "{$actor} approved {$model}",
            self::ACTION_PAID => "{$actor} marked {$model} as paid",
            self::ACTION_CLOSED => "{$actor} closed {$model}",
            self::ACTION_SLIP_GENERATED => "{$actor} generated payroll slip",
            default => "{$actor} {$this->action} {$model}",
        };
    }
}
