<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeSkill extends Model
{
    protected $fillable = [
        'employee_id',
        'skill_id',
        'current_level',
        'target_level',
        'last_assessed_at',
        'acquired_from',
        'is_primary',
        'years_experience',
    ];

    protected $casts = [
        'current_level' => 'integer',
        'target_level' => 'integer',
        'last_assessed_at' => 'date',
        'is_primary' => 'boolean',
        'years_experience' => 'integer',
    ];

    public const ACQUIRED_TRAINING = 'training';
    public const ACQUIRED_CERTIFICATION = 'certification';
    public const ACQUIRED_EXPERIENCE = 'experience';
    public const ACQUIRED_ASSESSMENT = 'assessment';
    public const ACQUIRED_SELF = 'self_declared';

    public const ACQUIRED_LABELS = [
        'training' => 'Training',
        'certification' => 'Sertifikasi',
        'experience' => 'Pengalaman',
        'assessment' => 'Assessment',
        'self_declared' => 'Self-declared',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(SkillAssessment::class, 'skill_id', 'skill_id')
            ->where('employee_id', $this->employee_id);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByLevel($query, int $minLevel)
    {
        return $query->where('current_level', '>=', $minLevel);
    }

    public function scopeNeedsImprovement($query)
    {
        return $query->whereNotNull('target_level')
            ->whereColumn('current_level', '<', 'target_level');
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getCurrentLevelLabelAttribute(): string
    {
        return Skill::getProficiencyLabel($this->current_level);
    }

    public function getTargetLevelLabelAttribute(): ?string
    {
        return $this->target_level
            ? Skill::getProficiencyLabel($this->target_level)
            : null;
    }

    public function getAcquiredFromLabelAttribute(): string
    {
        return self::ACQUIRED_LABELS[$this->acquired_from] ?? $this->acquired_from;
    }

    public function getLevelBadgeClassAttribute(): string
    {
        return match ($this->current_level) {
            1 => 'bg-red-100 text-red-700',
            2 => 'bg-orange-100 text-orange-700',
            3 => 'bg-yellow-100 text-yellow-700',
            4 => 'bg-blue-100 text-blue-700',
            5 => 'bg-green-100 text-green-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getGapToTargetAttribute(): ?int
    {
        if (!$this->target_level) {
            return null;
        }
        return max(0, $this->target_level - $this->current_level);
    }

    public function getProgressPercentageAttribute(): int
    {
        if (!$this->target_level || $this->target_level <= 1) {
            return 100;
        }
        return min(100, round(($this->current_level / $this->target_level) * 100));
    }

    public function getNeedsAssessmentAttribute(): bool
    {
        if (!$this->last_assessed_at) {
            return true;
        }
        // Needs assessment if last assessment was more than 12 months ago
        return $this->last_assessed_at->diffInMonths(now()) >= 12;
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Get assessment history for this employee skill
     */
    public function getAssessmentHistory()
    {
        return SkillAssessment::where('employee_id', $this->employee_id)
            ->where('skill_id', $this->skill_id)
            ->orderBy('assessment_date', 'desc')
            ->get();
    }

    /**
     * Update level from latest assessment
     */
    public function updateFromAssessment(SkillAssessment $assessment): void
    {
        $this->update([
            'current_level' => $assessment->proficiency_level,
            'last_assessed_at' => $assessment->assessment_date,
            'acquired_from' => 'assessment',
        ]);
    }
}
