<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillAssessment extends Model
{
    protected $fillable = [
        'employee_id',
        'skill_id',
        'assessor_id',
        'assessment_date',
        'proficiency_level',
        'proficiency_score',
        'assessment_type',
        'evidence',
        'strengths',
        'areas_for_improvement',
        'notes',
        'next_assessment_date',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'next_assessment_date' => 'date',
        'proficiency_level' => 'integer',
        'proficiency_score' => 'decimal:2',
    ];

    public const TYPE_SELF = 'self';
    public const TYPE_MANAGER = 'manager';
    public const TYPE_PEER = 'peer';
    public const TYPE_360 = '360';

    public const TYPE_LABELS = [
        'self' => 'Penilaian Diri',
        'manager' => 'Penilaian Atasan',
        'peer' => 'Penilaian Rekan',
        '360' => 'Penilaian 360°',
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

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
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

    public function scopeBySkill($query, $skillId)
    {
        return $query->where('skill_id', $skillId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('assessment_type', $type);
    }

    public function scopeRecent($query, int $months = 12)
    {
        return $query->where('assessment_date', '>=', now()->subMonths($months));
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('assessment_date', 'desc');
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->assessment_type] ?? $this->assessment_type;
    }

    public function getProficiencyLabelAttribute(): string
    {
        return Skill::getProficiencyLabel($this->proficiency_level);
    }

    public function getProficiencyDescriptionAttribute(): string
    {
        return Skill::getProficiencyDescription($this->proficiency_level);
    }

    public function getLevelBadgeClassAttribute(): string
    {
        return match ($this->proficiency_level) {
            1 => 'bg-red-100 text-red-700',
            2 => 'bg-orange-100 text-orange-700',
            3 => 'bg-yellow-100 text-yellow-700',
            4 => 'bg-blue-100 text-blue-700',
            5 => 'bg-green-100 text-green-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->assessment_type) {
            'self' => 'bg-purple-100 text-purple-700',
            'manager' => 'bg-blue-100 text-blue-700',
            'peer' => 'bg-indigo-100 text-indigo-700',
            '360' => 'bg-green-100 text-green-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getNeedsReassessmentAttribute(): bool
    {
        if (!$this->next_assessment_date) {
            return false;
        }
        return $this->next_assessment_date->isPast();
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Update employee skill level after assessment
     */
    public function updateEmployeeSkill(): void
    {
        EmployeeSkill::updateOrCreate(
            [
                'employee_id' => $this->employee_id,
                'skill_id' => $this->skill_id,
            ],
            [
                'current_level' => $this->proficiency_level,
                'last_assessed_at' => $this->assessment_date,
                'acquired_from' => 'assessment',
            ]
        );
    }
}
