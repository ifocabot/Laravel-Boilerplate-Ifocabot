<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    public function activeSkills(): HasMany
    {
        return $this->hasMany(Skill::class)->where('is_active', true);
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

    public function getSkillCountAttribute(): int
    {
        return $this->skills()->count();
    }
}
