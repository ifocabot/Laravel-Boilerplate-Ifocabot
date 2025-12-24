<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'quota' => 'integer',
        'used' => 'integer',
        'carry_forward' => 'integer',
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
    public function getTotalQuotaAttribute(): int
    {
        return $this->quota + $this->carry_forward;
    }

    /**
     * Get remaining leave balance
     */
    public function getRemainingAttribute(): int
    {
        return $this->total_quota - $this->used;
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Use leave days from balance
     */
    public function deduct(int $days): bool
    {
        if ($days > $this->remaining) {
            return false;
        }

        $this->increment('used', $days);
        return true;
    }

    /**
     * Restore leave days to balance (for cancelled leaves)
     */
    public function restore(int $days): void
    {
        $this->decrement('used', min($days, $this->used));
    }

    /**
     * Check if employee has sufficient balance
     */
    public function hasSufficientBalance(int $days): bool
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
