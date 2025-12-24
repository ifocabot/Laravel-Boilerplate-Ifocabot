<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProgram extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'provider',
        'trainer_id',
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'cost_per_person',
        'total_budget',
        'duration_hours',
        'status',
        'created_by',
        'objectives',
        'prerequisites',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_participants' => 'integer',
        'cost_per_person' => 'decimal:2',
        'total_budget' => 'decimal:2',
        'duration_hours' => 'integer',
    ];

    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';
    public const TYPE_ONLINE = 'online';
    public const TYPE_HYBRID = 'hybrid';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_ONGOING = 'ongoing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_LABELS = [
        'internal' => 'Internal',
        'external' => 'Eksternal',
        'online' => 'Online',
        'hybrid' => 'Hybrid',
    ];

    public const STATUS_LABELS = [
        'draft' => 'Draft',
        'open' => 'Pendaftaran Dibuka',
        'ongoing' => 'Sedang Berjalan',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(TrainingCourse::class)->orderBy('sequence');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class)
            ->whereNotIn('status', ['cancelled', 'failed']);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeOngoing($query)
    {
        return $query->where('status', self::STATUS_ONGOING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_OPEN])
            ->where('start_date', '>', now());
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-gray-100 text-gray-700',
            'open' => 'bg-blue-100 text-blue-700',
            'ongoing' => 'bg-yellow-100 text-yellow-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getFormattedCostPerPersonAttribute(): string
    {
        return 'Rp ' . number_format($this->cost_per_person ?? 0, 0, ',', '.');
    }

    public function getFormattedTotalBudgetAttribute(): string
    {
        return 'Rp ' . number_format($this->total_budget ?? 0, 0, ',', '.');
    }

    public function getEnrollmentCountAttribute(): int
    {
        return $this->enrollments()->count();
    }

    public function getAvailableSlotsAttribute(): ?int
    {
        if (!$this->max_participants) {
            return null;
        }
        return max(0, $this->max_participants - $this->enrollment_count);
    }

    public function getIsFullAttribute(): bool
    {
        if (!$this->max_participants) {
            return false;
        }
        return $this->enrollment_count >= $this->max_participants;
    }

    public function getCourseCountAttribute(): int
    {
        return $this->courses()->count();
    }

    public function getTotalDurationAttribute(): float
    {
        return $this->courses()->sum('duration_hours');
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    public function canEnroll(): bool
    {
        return $this->status === self::STATUS_OPEN && !$this->is_full;
    }

    public function publish(): void
    {
        $this->update(['status' => self::STATUS_OPEN]);
    }

    public function start(): void
    {
        $this->update(['status' => self::STATUS_ONGOING]);
    }

    public function complete(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}
