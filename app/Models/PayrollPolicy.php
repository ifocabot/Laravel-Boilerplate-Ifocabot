<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPolicy extends Model
{
    protected $fillable = [
        'key',
        'value',
        'scope_type',
        'scope_id',
        'effective_from',
        'effective_to',
        'description',
        'created_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    // Scope type priority (higher = more specific)
    public const SCOPE_PRIORITY = [
        'company' => 1,
        'branch' => 2,
        'department' => 3,
        'level' => 4,
        'employee' => 5,
    ];

    // Common policy keys
    public const KEY_LATE_PENALTY_PER_MINUTE = 'late.penalty_per_minute';
    public const KEY_OVERTIME_MULTIPLIER = 'overtime.multiplier';
    public const KEY_OVERTIME_HOURLY_RATE = 'overtime.hourly_rate';
    public const KEY_STANDARD_MONTHLY_HOURS = 'work.standard_monthly_hours';
    public const KEY_BPJS_JKK_RISK_CLASS = 'bpjs.jkk_risk_class';

    /**
     * Get the value, decoded if JSON
     */
    public function getDecodedValueAttribute(): mixed
    {
        $decoded = json_decode($this->value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->value;
    }

    /**
     * Set value, encoding complex types to JSON
     */
    public function setValueAttribute(mixed $value): void
    {
        $this->attributes['value'] = is_array($value) || is_object($value)
            ? json_encode($value)
            : (string) $value;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: active policies as of date
     */
    public function scopeActiveAsOf($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }

    /**
     * Scope: for specific key
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Get policy for employee with scope resolution
     */
    public static function getForEmployee(string $key, Employee $employee, $asOfDate = null): ?self
    {
        $date = $asOfDate ?? now();

        // Build scope conditions in priority order (most specific first)
        $scopes = [
            ['scope_type' => 'employee', 'scope_id' => $employee->id],
            ['scope_type' => 'level', 'scope_id' => $employee->currentCareer?->level_id],
            ['scope_type' => 'department', 'scope_id' => $employee->currentCareer?->department_id],
            ['scope_type' => 'branch', 'scope_id' => $employee->currentCareer?->department?->branch_id ?? null],
            ['scope_type' => 'company', 'scope_id' => null],
        ];

        // Query each scope in order, return first match
        foreach ($scopes as $scope) {
            if ($scope['scope_id'] !== null || $scope['scope_type'] === 'company') {
                $policy = self::forKey($key)
                    ->activeAsOf($date)
                    ->where('scope_type', $scope['scope_type'])
                    ->where(function ($q) use ($scope) {
                        if ($scope['scope_id'] === null) {
                            $q->whereNull('scope_id');
                        } else {
                            $q->where('scope_id', $scope['scope_id']);
                        }
                    })
                    ->orderBy('effective_from', 'desc')
                    ->first();

                if ($policy) {
                    return $policy;
                }
            }
        }

        return null;
    }

    /**
     * Get policy value with fallback
     */
    public static function getValue(string $key, Employee $employee, $default = null, $asOfDate = null): mixed
    {
        $policy = self::getForEmployee($key, $employee, $asOfDate);
        return $policy ? $policy->decoded_value : $default;
    }
}
