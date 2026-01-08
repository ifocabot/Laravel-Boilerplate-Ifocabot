<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PayrollAdjustment extends Model
{
    protected $table = 'payroll_adjustments';

    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'source_period_id',
        'source_date',
        'type',
        'amount_minutes',
        'amount_days',
        'amount_money',
        'reason',
        'notes',
        'reference_type',
        'reference_id',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'source_date' => 'date',
        'amount_minutes' => 'integer',
        'amount_days' => 'decimal:2',
        'amount_money' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Adjustment types
     */
    public const TYPE_OVERTIME = 'overtime';
    public const TYPE_LEAVE_CORRECTION = 'leave_correction';
    public const TYPE_ATTENDANCE_CORRECTION = 'attendance_correction';
    public const TYPE_LATE_CORRECTION = 'late_correction';
    public const TYPE_SCHEDULE_CHANGE = 'schedule_change';
    public const TYPE_MANUAL = 'manual';
    public const TYPE_OTHER = 'other';

    /**
     * Statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Relationships
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function sourcePeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'source_period_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the referenced model (polymorphic)
     */
    public function reference()
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }
        return $this->reference_type::find($this->reference_id);
    }

    /**
     * Accessors
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->type) {
                self::TYPE_OVERTIME => 'Overtime',
                self::TYPE_LEAVE_CORRECTION => 'Koreksi Cuti',
                self::TYPE_ATTENDANCE_CORRECTION => 'Koreksi Kehadiran',
                self::TYPE_LATE_CORRECTION => 'Koreksi Keterlambatan',
                self::TYPE_SCHEDULE_CHANGE => 'Perubahan Jadwal',
                self::TYPE_MANUAL => 'Manual',
                self::TYPE_OTHER => 'Lainnya',
                default => $this->type,
            }
        );
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                self::STATUS_PENDING => 'Menunggu',
                self::STATUS_APPROVED => 'Disetujui',
                self::STATUS_REJECTED => 'Ditolak',
                default => $this->status,
            }
        );
    }

    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->status) {
                self::STATUS_PENDING => 'bg-yellow-100 text-yellow-700',
                self::STATUS_APPROVED => 'bg-green-100 text-green-700',
                self::STATUS_REJECTED => 'bg-red-100 text-red-700',
                default => 'bg-gray-100 text-gray-700',
            }
        );
    }

    protected function formattedAmountMinutes(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->amount_minutes)
                    return '-';
                $hours = floor(abs($this->amount_minutes) / 60);
                $mins = abs($this->amount_minutes) % 60;
                $sign = $this->amount_minutes >= 0 ? '+' : '-';
                return $mins > 0 ? "{$sign}{$hours}j {$mins}m" : "{$sign}{$hours}j";
            }
        );
    }

    protected function formattedAmountMoney(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->amount_money
            ? 'Rp ' . number_format($this->amount_money, 0, ',', '.')
            : '-'
        );
    }

    /**
     * Approve adjustment
     */
    public function approve(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        \Log::info('Payroll adjustment approved', [
            'adjustment_id' => $this->id,
            'employee_id' => $this->employee_id,
            'type' => $this->type,
            'approved_by' => $userId,
        ]);
    }

    /**
     * Reject adjustment
     */
    public function reject(int $userId, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        \Log::info('Payroll adjustment rejected', [
            'adjustment_id' => $this->id,
            'employee_id' => $this->employee_id,
            'type' => $this->type,
            'rejected_by' => $userId,
            'reason' => $reason,
        ]);
    }

    /**
     * Scopes
     */
    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForPeriod($query, int $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Create adjustment from overtime request approved after lock
     */
    public static function createFromLateOvertimeApproval(
        OvertimeRequest $overtimeRequest,
        PayrollPeriod $targetPeriod,
        int $userId
    ): self {
        return self::create([
            'employee_id' => $overtimeRequest->employee_id,
            'payroll_period_id' => $targetPeriod->id,
            'source_date' => $overtimeRequest->date,
            'type' => self::TYPE_OVERTIME,
            'amount_minutes' => $overtimeRequest->approved_duration_minutes,
            'reason' => "Overtime tanggal {$overtimeRequest->date->format('d/m/Y')} disetujui setelah periode terkunci",
            'reference_type' => OvertimeRequest::class,
            'reference_id' => $overtimeRequest->id,
            'status' => self::STATUS_PENDING,
            'created_by' => $userId,
        ]);
    }
}
