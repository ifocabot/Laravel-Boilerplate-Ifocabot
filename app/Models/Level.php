<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Level extends Model implements AuditableContract
{
    use Auditable;

    protected $table = "levels";

    protected $fillable = [
        'name',
        'grade_code',
        'min_salary',
        'max_salary',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
    ];

    /**
     * Attributes to include in audit
     */
    protected $auditInclude = [
        'name',
        'grade_code',
        'min_salary',
        'max_salary',
    ];

    /**
     * Generate tags for the audit
     */
    public function generateTags(): array
    {
        return ['level', 'master-data'];
    }

    /**
     * Relationships
     */
    // public function employees(): HasMany
    // {
    //     return $this->hasMany(Employee::class);
    // }

    /**
     * Audit Transformations
     */
    public function transformAudit(array $data): array
    {
        try {
            // Format salary values
            if (isset($data['old_values']['min_salary'])) {
                $data['old_values']['min_salary_formatted'] = 'Rp ' . number_format($data['old_values']['min_salary'], 0, ',', '.');
            }

            if (isset($data['new_values']['min_salary'])) {
                $data['new_values']['min_salary_formatted'] = 'Rp ' . number_format($data['new_values']['min_salary'], 0, ',', '.');
            }

            if (isset($data['old_values']['max_salary'])) {
                $data['old_values']['max_salary_formatted'] = 'Rp ' . number_format($data['old_values']['max_salary'], 0, ',', '.');
            }

            if (isset($data['new_values']['max_salary'])) {
                $data['new_values']['max_salary_formatted'] = 'Rp ' . number_format($data['new_values']['max_salary'], 0, ',', '.');
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to transform level audit data', [
                'error' => $e->getMessage()
            ]);
        }

        return $data;
    }

    /**
     * Accessors
     */
    public function getFormattedMinSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->min_salary, 0, ',', '.');
    }

    public function getFormattedMaxSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->max_salary, 0, ',', '.');
    }

    public function getSalaryRangeAttribute(): float
    {
        return $this->max_salary - $this->min_salary;
    }

    public function getFormattedSalaryRangeAttribute(): string
    {
        return 'Rp ' . number_format($this->salary_range, 0, ',', '.');
    }

    /**
     * Scopes
     */
    public function scopeByGrade($query, $gradeCode)
    {
        return $query->where('grade_code', $gradeCode);
    }

    public function scopeWithinSalaryRange($query, $salary)
    {
        return $query->where('min_salary', '<=', $salary)
            ->where('max_salary', '>=', $salary);
    }

    /**
     * Check if a salary is within this level's range
     */
    public function isWithinRange($salary): bool
    {
        return $salary >= $this->min_salary && $salary <= $this->max_salary;
    }

    /**
     * Get the midpoint salary for this level
     */
    public function getMidpointSalary(): float
    {
        return ($this->min_salary + $this->max_salary) / 2;
    }

    /**
     * Calculate position in range (0-100%)
     */
    public function getPositionInRange($salary): float
    {
        if ($salary < $this->min_salary)
            return 0;
        if ($salary > $this->max_salary)
            return 100;

        $range = $this->max_salary - $this->min_salary;
        $position = $salary - $this->min_salary;

        return ($position / $range) * 100;
    }
}