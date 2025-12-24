<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingCompletion extends Model
{
    protected $fillable = [
        'training_enrollment_id',
        'training_course_id',
        'employee_id',
        'started_at',
        'completed_at',
        'score',
        'status',
        'attempts',
        'time_spent_minutes',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
        'attempts' => 'integer',
        'time_spent_minutes' => 'integer',
    ];

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const STATUS_LABELS = [
        'not_started' => 'Belum Dimulai',
        'in_progress' => 'Sedang Berjalan',
        'completed' => 'Selesai',
        'failed' => 'Tidak Lulus',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'training_enrollment_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(TrainingCourse::class, 'training_course_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeNotStarted($query)
    {
        return $query->where('status', self::STATUS_NOT_STARTED);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'not_started' => 'bg-gray-100 text-gray-700',
            'in_progress' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'failed' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getFormattedTimeSpentAttribute(): string
    {
        $hours = floor($this->time_spent_minutes / 60);
        $minutes = $this->time_spent_minutes % 60;

        if ($hours > 0) {
            return "{$hours} jam {$minutes} menit";
        }
        return "{$minutes} menit";
    }

    public function getIsPassedAttribute(): bool
    {
        if (!$this->score || !$this->course) {
            return false;
        }
        return $this->score >= $this->course->passing_score;
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    public function start(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function complete(float $score): void
    {
        $passingScore = $this->course->passing_score ?? 70;
        $passed = $score >= $passingScore;

        $this->update([
            'status' => $passed ? self::STATUS_COMPLETED : self::STATUS_FAILED,
            'completed_at' => now(),
            'score' => $score,
        ]);
    }

    public function addTimeSpent(int $minutes): void
    {
        $this->increment('time_spent_minutes', $minutes);
    }
}
