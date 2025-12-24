<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingCourse extends Model
{
    protected $fillable = [
        'training_program_id',
        'name',
        'description',
        'duration_hours',
        'sequence',
        'materials_path',
        'passing_score',
        'is_mandatory',
        'learning_outcomes',
    ];

    protected $casts = [
        'duration_hours' => 'decimal:2',
        'sequence' => 'integer',
        'passing_score' => 'integer',
        'is_mandatory' => 'boolean',
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

    public function completions(): HasMany
    {
        return $this->hasMany(TrainingCompletion::class);
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence');
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getMandatoryLabelAttribute(): string
    {
        return $this->is_mandatory ? 'Wajib' : 'Opsional';
    }

    public function getMandatoryBadgeClassAttribute(): string
    {
        return $this->is_mandatory
            ? 'bg-red-100 text-red-700'
            : 'bg-gray-100 text-gray-600';
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration_hours);
        $minutes = ($this->duration_hours - $hours) * 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        } else {
            return "{$minutes} menit";
        }
    }

    public function getCompletionCountAttribute(): int
    {
        return $this->completions()
            ->where('status', 'completed')
            ->count();
    }

    public function getAverageScoreAttribute(): ?float
    {
        $avg = $this->completions()
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->avg('score');

        return $avg ? round($avg, 2) : null;
    }
}
