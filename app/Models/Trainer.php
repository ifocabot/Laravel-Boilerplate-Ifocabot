<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trainer extends Model
{
    protected $fillable = [
        'type',
        'employee_id',
        'name',
        'email',
        'phone',
        'organization',
        'expertise',
        'bio',
        'hourly_rate',
        'is_active',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function trainingPrograms(): HasMany
    {
        return $this->hasMany(TrainingProgram::class);
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

    public function scopeInternal($query)
    {
        return $query->where('type', self::TYPE_INTERNAL);
    }

    public function scopeExternal($query)
    {
        return $query->where('type', self::TYPE_EXTERNAL);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    /**
     * Get display name - for internal, use employee name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === self::TYPE_INTERNAL && $this->employee) {
            return $this->employee->full_name;
        }
        return $this->name ?? '-';
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_INTERNAL ? 'Internal' : 'Eksternal';
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === self::TYPE_INTERNAL
            ? 'bg-blue-100 text-blue-700'
            : 'bg-purple-100 text-purple-700';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active
            ? 'bg-green-100 text-green-700'
            : 'bg-gray-100 text-gray-600';
    }

    public function getFormattedHourlyRateAttribute(): string
    {
        return 'Rp ' . number_format($this->hourly_rate ?? 0, 0, ',', '.');
    }
}
