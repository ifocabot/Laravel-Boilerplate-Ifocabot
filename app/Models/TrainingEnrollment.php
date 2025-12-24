<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingEnrollment extends Model
{
    protected $fillable = [
        'training_program_id',
        'employee_id',
        'enrollment_date',
        'status',
        'approved_by',
        'approved_at',
        'started_at',
        'completion_date',
        'final_score',
        'certificate_issued',
        'certificate_issued_at',
        'certificate_number',
        'notes',
        'feedback',
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completion_date' => 'datetime',
        'certificate_issued_at' => 'date',
        'final_score' => 'decimal:2',
        'certificate_issued' => 'boolean',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_ENROLLED = 'enrolled';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_LABELS = [
        'pending' => 'Menunggu Approval',
        'approved' => 'Disetujui',
        'enrolled' => 'Terdaftar',
        'in_progress' => 'Sedang Berjalan',
        'completed' => 'Selesai',
        'failed' => 'Tidak Lulus',
        'cancelled' => 'Dibatalkan',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function program(): BelongsTo
    {
        return $this->belongsTo(TrainingProgram::class, 'training_program_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TrainingCompletion::class);
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

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_ENROLLED,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
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
            'pending' => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-blue-100 text-blue-700',
            'enrolled' => 'bg-indigo-100 text-indigo-700',
            'in_progress' => 'bg-purple-100 text-purple-700',
            'completed' => 'bg-green-100 text-green-700',
            'failed' => 'bg-red-100 text-red-700',
            'cancelled' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getProgressPercentageAttribute(): int
    {
        $totalCourses = $this->program->courses()->count();
        if ($totalCourses === 0) {
            return 0;
        }

        $completedCourses = $this->completions()
            ->where('status', 'completed')
            ->count();

        return round(($completedCourses / $totalCourses) * 100);
    }

    public function getCompletedCoursesCountAttribute(): int
    {
        return $this->completions()
            ->where('status', 'completed')
            ->count();
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    public function approve(int $approverId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
    }

    public function startProgram(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        // Create completion records for all courses
        foreach ($this->program->courses as $course) {
            TrainingCompletion::firstOrCreate([
                'training_enrollment_id' => $this->id,
                'training_course_id' => $course->id,
                'employee_id' => $this->employee_id,
            ], [
                'status' => 'not_started',
            ]);
        }
    }

    public function complete(float $finalScore): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completion_date' => now(),
            'final_score' => $finalScore,
        ]);
    }

    public function fail(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completion_date' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function issueCertificate(string $certificateNumber): void
    {
        $this->update([
            'certificate_issued' => true,
            'certificate_issued_at' => now(),
            'certificate_number' => $certificateNumber,
        ]);
    }

    /**
     * Calculate and update final score based on course completions
     */
    public function calculateFinalScore(): float
    {
        $completions = $this->completions()
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->get();

        if ($completions->isEmpty()) {
            return 0;
        }

        $totalScore = $completions->sum('score');
        $avgScore = $totalScore / $completions->count();

        $this->update(['final_score' => $avgScore]);

        return $avgScore;
    }
}
