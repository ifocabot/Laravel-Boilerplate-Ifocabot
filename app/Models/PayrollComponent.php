<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PayrollComponent extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'category',
        'calculation_type',
        'calculation_formula',
        'is_taxable',
        'is_bpjs_base',
        'display_order',
        'show_on_slip',
        'is_active',
        'rate_per_day',
        'rate_per_hour',
        'percentage_value',
        'default_amount', // ERP: Company policy default
        'calculation_notes',
        // Behavior flags
        'proration_type',
        'forfeit_on_alpha',
        'forfeit_on_late',
        'min_attendance_percent',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'is_bpjs_base' => 'boolean',
        'show_on_slip' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'default_amount' => 'decimal:2',
        'rate_per_day' => 'decimal:2',
        'rate_per_hour' => 'decimal:2',
        'percentage_value' => 'decimal:2',
        // Behavior flags
        'forfeit_on_alpha' => 'boolean',
        'forfeit_on_late' => 'boolean',
        'min_attendance_percent' => 'integer',
    ];

    /**
     * Relationships
     */
    public function employeeComponents(): HasMany
    {
        return $this->hasMany(EmployeePayrollComponent::class, 'component_id');
    }

    /**
     * Accessors
     */
    protected function typeBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type === 'earning'
            ? 'bg-green-100 text-green-700'
            : 'bg-red-100 text-red-700'
        );
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type === 'earning' ? 'Pendapatan' : 'Potongan'
        );
    }

    protected function categoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->category) {
                'basic_salary' => 'Gaji Pokok',
                'fixed_allowance' => 'Tunjangan Tetap',
                'variable_allowance' => 'Tunjangan Variabel',
                'statutory' => 'Potongan Wajib',
                'other_deduction' => 'Potongan Lainnya',
                default => '-',
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

    public function scopeEarnings($query)
    {
        return $query->where('type', 'earning');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Methods
     */
    public function calculateAmount(float $baseAmount, array $context = []): float
    {
        return match ($this->calculation_type) {
            'fixed' => $baseAmount,
            'percentage' => $baseAmount * (floatval($this->calculation_formula) / 100),
            'formula' => $this->evaluateFormula($baseAmount, $context),
            default => 0,
        };
    }

    private function evaluateFormula(float $baseAmount, array $context): float
    {
        // Custom formula evaluation
        // You can use a library like symfony/expression-language
        // For now, simple implementation
        return 0;
    }
}