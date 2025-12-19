<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EmployeePayrollComponent extends Model
{
    protected $fillable = [
        'employee_id',
        'component_id',
        'amount',
        'unit',
        'effective_from',
        'effective_to',
        'is_active',
        'is_recurring',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-deactivate when effective_to is reached
        static::creating(function ($employeeComponent) {
            if ($employeeComponent->is_active) {
                // Deactivate other active components of same type for this employee
                self::where('employee_id', $employeeComponent->employee_id)
                    ->where('component_id', $employeeComponent->component_id)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'effective_to' => $employeeComponent->effective_from->copy()->subDay(),
                    ]);
            }
        });
    }

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class, 'component_id');
    }

    /**
     * Accessors
     */
    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->amount, 0, ',', '.')
        );
    }

    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->effective_to && $this->effective_to->isPast()
        );
    }

    protected function durationLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->effective_to) {
                    return 'Unlimited';
                }

                $months = $this->effective_from->diffInMonths($this->effective_to);
                return $months . ' bulan';
            }
        );
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            });
    }

    /**
     * Methods
     */
    public function isEffectiveOn($date): bool
    {
        return $this->effective_from <= $date
            && (!$this->effective_to || $this->effective_to >= $date);
    }

    public function deactivate($endDate = null): void
    {
        $this->is_active = false;
        $this->effective_to = $endDate ?? now()->toDateString();
        $this->save();
    }
}