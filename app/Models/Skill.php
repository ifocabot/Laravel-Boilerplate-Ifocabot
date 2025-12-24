<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $fillable = [
        'skill_category_id',
        'name',
        'code',
        'description',
        'proficiency_levels',
        'is_active',
    ];

    protected $casts = [
        'proficiency_levels' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Proficiency Level Constants
     */
    public const LEVEL_NOVICE = 1;
    public const LEVEL_BEGINNER = 2;
    public const LEVEL_COMPETENT = 3;
    public const LEVEL_PROFICIENT = 4;
    public const LEVEL_EXPERT = 5;

    public const PROFICIENCY_LABELS = [
        1 => 'Novice',
        2 => 'Beginner',
        3 => 'Competent',
        4 => 'Proficient',
        5 => 'Expert',
    ];

    public const PROFICIENCY_DESCRIPTIONS = [
        1 => 'Pengetahuan dasar teori, membutuhkan pengawasan ketat',
        2 => 'Dapat melakukan tugas sederhana dengan bimbingan',
        3 => 'Bekerja mandiri pada tugas standar',
        4 => 'Menangani situasi kompleks, dapat membimbing orang lain',
        5 => 'Tingkat master, mendorong inovasi dan praktik terbaik',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    public function employeeSkills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(SkillAssessment::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('skill_category_id', $categoryId);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active
            ? 'bg-green-100 text-green-700'
            : 'bg-gray-100 text-gray-600';
    }

    /**
     * Get custom proficiency levels or default
     */
    public function getProficiencyLabelsAttribute(): array
    {
        return $this->proficiency_levels ?? self::PROFICIENCY_LABELS;
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Get proficiency level label
     */
    public static function getProficiencyLabel(int $level): string
    {
        return self::PROFICIENCY_LABELS[$level] ?? 'Unknown';
    }

    /**
     * Get proficiency level description
     */
    public static function getProficiencyDescription(int $level): string
    {
        return self::PROFICIENCY_DESCRIPTIONS[$level] ?? '';
    }
}
