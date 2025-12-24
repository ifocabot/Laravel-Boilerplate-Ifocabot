<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certification extends Model
{
    protected $fillable = [
        'name',
        'code',
        'issuing_organization',
        'description',
        'validity_months',
        'level',
        'skill_id',
        'is_active',
    ];

    protected $casts = [
        'validity_months' => 'integer',
        'is_active' => 'boolean',
    ];

    public const LEVEL_BEGINNER = 'beginner';
    public const LEVEL_INTERMEDIATE = 'intermediate';
    public const LEVEL_ADVANCED = 'advanced';
    public const LEVEL_EXPERT = 'expert';

    public const LEVEL_LABELS = [
        'beginner' => 'Pemula',
        'intermediate' => 'Menengah',
        'advanced' => 'Lanjutan',
        'expert' => 'Ahli',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function employeeCertifications(): HasMany
    {
        return $this->hasMany(EmployeeCertification::class);
    }

    public function activeEmployeeCertifications(): HasMany
    {
        return $this->hasMany(EmployeeCertification::class)
            ->where('status', 'active');
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

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getLevelLabelAttribute(): ?string
    {
        return $this->level ? (self::LEVEL_LABELS[$this->level] ?? $this->level) : null;
    }

    public function getLevelBadgeClassAttribute(): string
    {
        return match ($this->level) {
            'beginner' => 'bg-green-100 text-green-700',
            'intermediate' => 'bg-blue-100 text-blue-700',
            'advanced' => 'bg-purple-100 text-purple-700',
            'expert' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active
            ? 'bg-green-100 text-green-700'
            : 'bg-gray-100 text-gray-600';
    }

    public function getValidityLabelAttribute(): string
    {
        if (!$this->validity_months) {
            return 'Seumur Hidup';
        }

        if ($this->validity_months >= 12) {
            $years = floor($this->validity_months / 12);
            $months = $this->validity_months % 12;

            if ($months > 0) {
                return "{$years} tahun {$months} bulan";
            }
            return "{$years} tahun";
        }

        return "{$this->validity_months} bulan";
    }

    public function getHolderCountAttribute(): int
    {
        return $this->activeEmployeeCertifications()->count();
    }
}

