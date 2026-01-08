<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'period_code',
        'period_name',
        'year',
        'month',
        'cutoff_start_day',
        'cutoff_end_day',
        'start_date',
        'end_date',
        'payment_date',
        'status',
        'attendance_locked',
        'attendance_locked_at',
        'attendance_locked_by',
        'period_summary_generated_at',
        'approved_at',
        'approved_by',
        'total_gross_salary',
        'total_deductions',
        'total_net_salary',
        'total_employees',
        'notes',
    ];

    protected $casts = [
        'cutoff_start_day' => 'integer',
        'cutoff_end_day' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'attendance_locked' => 'boolean',
        'attendance_locked_at' => 'datetime',
        'period_summary_generated_at' => 'datetime',
        'approved_at' => 'date',
        'total_gross_salary' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_net_salary' => 'decimal:2',
        'total_employees' => 'integer',
    ];

    /**
     * Relationships
     */
    public function slips(): HasMany
    {
        return $this->hasMany(PayrollSlip::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attendanceLockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendance_locked_by');
    }

    public function periodSummaries(): HasMany
    {
        return $this->hasMany(AttendancePeriodSummary::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    /**
     * Accessors
     */
    protected function formattedGrossSalary(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->total_gross_salary, 0, ',', '.')
        );
    }

    protected function formattedNetSalary(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->total_net_salary, 0, ',', '.')
        );
    }

    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                'draft' => 'bg-gray-100 text-gray-700',
                'processing' => 'bg-blue-100 text-blue-700',
                'approved' => 'bg-green-100 text-green-700',
                'paid' => 'bg-purple-100 text-purple-700',
                'closed' => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700',
            }
        );
    }

    /**
     * Scopes
     */
    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Methods
     */
    public function calculateTotals(): void
    {
        $this->total_gross_salary = $this->slips()->sum('gross_salary');
        $this->total_deductions = $this->slips()->sum('total_deductions');
        $this->total_net_salary = $this->slips()->sum('net_salary');
        $this->total_employees = $this->slips()->count();
        $this->save();
    }

    public function approve(User $user): void
    {
        $this->status = 'approved';
        $this->approved_at = now();
        $this->approved_by = $user->id;
        $this->save();
    }

    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->save();

        // Update all slips
        $this->slips()->update([
            'payment_status' => 'paid',
            'payment_date' => now(),
        ]);
    }
}