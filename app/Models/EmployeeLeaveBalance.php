<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeLeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'quota',
        'used',
        'carry_forward',
    ];

    protected $casts = [
        'year' => 'integer',
        'quota' => 'decimal:1',
        'used' => 'decimal:1',
        'carry_forward' => 'decimal:1',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Ledger transactions for audit trail
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LeaveBalanceTransaction::class, 'employee_leave_balance_id');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForType($query, int $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    /**
     * Get total available quota (quota + carry forward)
     */
    public function getTotalQuotaAttribute(): float
    {
        return $this->quota + $this->carry_forward;
    }

    /**
     * Get remaining leave balance
     */
    public function getRemainingAttribute(): float
    {
        return $this->total_quota - $this->used;
    }

    /**
     * Alias for remaining - for backward compatibility
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->remaining;
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Use leave days from balance (legacy - no ledger)
     */
    public function deduct(float $days): bool
    {
        if ($days > $this->remaining) {
            return false;
        }

        $this->increment('used', $days);
        return true;
    }

    /**
     * Restore leave days to balance (legacy - no ledger)
     */
    public function restore(float $days): void
    {
        $this->decrement('used', min($days, $this->used));
    }

    /**
     * ⭐ Deduct with ledger transaction for audit trail
     */
    public function deductWithLedger(float $days, int $leaveRequestId, ?int $userId = null): bool
    {
        if ($days > $this->remaining) {
            return false;
        }

        $this->increment('used', $days);

        LeaveBalanceTransaction::create([
            'employee_leave_balance_id' => $this->id,
            'leave_request_id' => $leaveRequestId,
            'type' => LeaveBalanceTransaction::TYPE_DEDUCTION,
            'amount' => -$days,
            'balance_after' => $this->fresh()->remaining,
            'description' => 'Penggunaan cuti untuk request #' . $leaveRequestId,
            'created_by' => $userId,
        ]);

        return true;
    }

    /**
     * ⭐ Restore with ledger transaction for audit trail (for cancelled leaves)
     */
    public function restoreWithLedger(float $days, int $leaveRequestId, ?int $userId = null): void
    {
        $restoreAmount = min($days, $this->used);
        $this->decrement('used', $restoreAmount);

        LeaveBalanceTransaction::create([
            'employee_leave_balance_id' => $this->id,
            'leave_request_id' => $leaveRequestId,
            'type' => LeaveBalanceTransaction::TYPE_REVERSAL,
            'amount' => $restoreAmount,
            'balance_after' => $this->fresh()->remaining,
            'description' => 'Pengembalian cuti untuk request #' . $leaveRequestId . ' (dibatalkan)',
            'created_by' => $userId,
        ]);
    }

    /**
     * Check if employee has sufficient balance
     */
    public function hasSufficientBalance(float $days): bool
    {
        return $this->remaining >= $days;
    }

    /**
     * Get or create balance for employee and leave type
     */
    public static function getOrCreate(int $employeeId, int $leaveTypeId, int $year): self
    {
        $balance = self::forEmployee($employeeId)
            ->forType($leaveTypeId)
            ->forYear($year)
            ->first();

        if (!$balance) {
            $leaveType = LeaveType::find($leaveTypeId);
            $balance = self::create([
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
                'quota' => $leaveType?->default_quota ?? 0,
                'used' => 0,
                'carry_forward' => 0,
            ]);
        }

        return $balance;
    }
}
