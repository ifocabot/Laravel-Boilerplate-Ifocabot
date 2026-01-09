<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PayrollSlip extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'slip_number',
        'slip_date',

        // Employee Snapshot
        'employee_nik',
        'employee_name',
        'department',
        'position',
        'level',

        // Working Days
        'working_days',
        'actual_days',
        'absent_days',
        'leave_days',

        // Components
        'earnings',
        'deductions',

        // Totals
        'gross_salary',
        'total_deductions',
        'net_salary',

        // Tax
        'tax_status',
        'taxable_income',
        'tax_amount',

        // BPJS
        'bpjs_tk_company',
        'bpjs_tk_employee',
        'bpjs_kes_company',
        'bpjs_kes_employee',

        // Payment
        'payment_status',
        'payment_date',
        'payment_method',
        'payment_reference',

        // Bank Snapshot
        'bank_name',
        'bank_account_number',
        'bank_account_holder',

        'notes',

        // Calculation Snapshot (Phase 1: Freeze & Audit)
        'calculation_snapshot',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'slip_date' => 'date',
        'payment_date' => 'date',
        'earnings' => 'array',
        'deductions' => 'array',
        'gross_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'taxable_income' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'bpjs_tk_company' => 'decimal:2',
        'bpjs_tk_employee' => 'decimal:2',
        'bpjs_kes_company' => 'decimal:2',
        'bpjs_kes_employee' => 'decimal:2',
        'working_days' => 'integer',
        'actual_days' => 'integer',
        'absent_days' => 'integer',
        'leave_days' => 'integer',
        'calculation_snapshot' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'generated_by');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PayrollSlipItem::class);
    }

    /**
     * Accessors
     */
    protected function formattedGrossSalary(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->gross_salary, 0, ',', '.')
        );
    }

    protected function formattedNetSalary(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->net_salary, 0, ',', '.')
        );
    }

    protected function formattedTotalDeductions(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->total_deductions, 0, ',', '.')
        );
    }

    protected function paymentStatusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->payment_status) {
                'pending' => 'bg-yellow-100 text-yellow-700',
                'paid' => 'bg-green-100 text-green-700',
                'failed' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700',
            }
        );
    }

    protected function paymentStatusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->payment_status) {
                'pending' => 'Menunggu',
                'paid' => 'Dibayar',
                'failed' => 'Gagal',
                default => '-',
            }
        );
    }

    /**
     * Scopes
     */
    public function scopeForPeriod($query, $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Methods
     */
    public function calculateTotals(): void
    {
        // Calculate gross salary from earnings
        $this->gross_salary = collect($this->earnings)->sum('amount');

        // Calculate total deductions
        $this->total_deductions = collect($this->deductions)->sum('amount');

        // Calculate net salary
        $this->net_salary = $this->gross_salary - $this->total_deductions;

        $this->save();
    }

    public function markAsPaid(string $paymentMethod = 'transfer', string $reference = null): void
    {
        $this->payment_status = 'paid';
        $this->payment_date = now();
        $this->payment_method = $paymentMethod;
        $this->payment_reference = $reference;
        $this->save();
    }

    public function markAsFailed(string $notes = null): void
    {
        $this->payment_status = 'failed';
        $this->notes = $notes ?? $this->notes;
        $this->save();
    }

    /**
     * Generate slip number
     */
    public static function generateSlipNumber($periodCode, $employeeNik): string
    {
        return 'PS-' . $periodCode . '-' . $employeeNik;
    }

    /**
     * Get earnings by category
     */
    public function getEarningsByCategory(string $category): array
    {
        return collect($this->earnings)
            ->where('category', $category)
            ->all();
    }

    /**
     * Get deductions by category
     */
    public function getDeductionsByCategory(string $category): array
    {
        return collect($this->deductions)
            ->where('category', $category)
            ->all();
    }

    /**
     * Get total earnings by type
     */
    public function getTotalEarningsByType(string $type): float
    {
        return collect($this->earnings)
            ->where('type', $type)
            ->sum('amount');
    }

    /**
     * Get basic salary from earnings
     */
    public function getBasicSalary(): float
    {
        $basicSalary = collect($this->earnings)
            ->firstWhere('code', 'BASIC_SALARY');

        return $basicSalary ? $basicSalary['amount'] : 0;
    }

    /**
     * Export to PDF (placeholder)
     */
    public function exportToPdf()
    {
        // TODO: Implement PDF export using dompdf or similar
        // Return PDF download response
    }
}