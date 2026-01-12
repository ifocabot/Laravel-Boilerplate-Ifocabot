<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalanceTransaction extends Model
{
    protected $fillable = [
        'employee_leave_balance_id',
        'leave_request_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:1',
        'balance_after' => 'decimal:1',
    ];

    /**
     * Transaction type constants
     */
    public const TYPE_ALLOCATION = 'allocation';
    public const TYPE_DEDUCTION = 'deduction';
    public const TYPE_REVERSAL = 'reversal';
    public const TYPE_CARRY_FORWARD = 'carry_forward';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function balance(): BelongsTo
    {
        return $this->belongsTo(EmployeeLeaveBalance::class, 'employee_leave_balance_id');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForBalance($query, int $balanceId)
    {
        return $query->where('employee_leave_balance_id', $balanceId);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ALLOCATION => 'Alokasi',
            self::TYPE_DEDUCTION => 'Penggunaan',
            self::TYPE_REVERSAL => 'Pengembalian',
            self::TYPE_CARRY_FORWARD => 'Sisa Tahun Lalu',
            self::TYPE_ADJUSTMENT => 'Penyesuaian',
            default => $this->type,
        };
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->amount < 0;
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->amount > 0;
    }
}
