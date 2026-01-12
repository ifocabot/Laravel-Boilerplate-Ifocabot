<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Attendance Adjustment Ledger Entry
 * 
 * Tracks all modifications to attendance data:
 * - Leave approvals
 * - Overtime approvals/cancellations
 * - Late waivers
 * - Manual HR corrections
 * 
 * This makes AttendanceSummary regeneration safe - adjustments persist.
 */
class AttendanceAdjustment extends Model
{
    // Adjustment types
    public const TYPE_LEAVE = 'leave';
    public const TYPE_SICK = 'sick';
    public const TYPE_PERMISSION = 'permission';
    public const TYPE_OVERTIME_ADD = 'overtime_add';
    public const TYPE_OVERTIME_CANCEL = 'overtime_cancel';
    public const TYPE_LATE_WAIVE = 'late_waive';
    public const TYPE_MANUAL_OVERRIDE = 'manual_override';

    protected $fillable = [
        'employee_id',
        'date',
        'type',
        'adjustment_minutes',
        'status_override',
        'source_type',
        'source_id',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'adjustment_minutes' => 'integer',
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

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLeaveTypes($query)
    {
        return $query->whereIn('type', [self::TYPE_LEAVE, self::TYPE_SICK, self::TYPE_PERMISSION]);
    }

    /**
     * ========================================
     * STATIC METHODS
     * ========================================
     */

    /**
     * Create adjustment for approved leave
     */
    public static function createForLeave(
        int $employeeId,
        $date,
        string $statusOverride,
        int $leaveRequestId,
        ?int $userId = null
    ): self {
        return self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date,
                'source_type' => LeaveRequest::class,
                'source_id' => $leaveRequestId,
            ],
            [
                'type' => self::TYPE_LEAVE,
                'status_override' => $statusOverride,
                'created_by' => $userId ?? auth()->id(),
            ]
        );
    }

    /**
     * Create adjustment for approved overtime
     */
    public static function createForOvertime(
        int $employeeId,
        $date,
        int $minutes,
        int $overtimeRequestId,
        ?int $userId = null
    ): self {
        return self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date,
                'source_type' => OvertimeRequest::class,
                'source_id' => $overtimeRequestId,
            ],
            [
                'type' => self::TYPE_OVERTIME_ADD,
                'adjustment_minutes' => $minutes,
                'created_by' => $userId ?? auth()->id(),
            ]
        );
    }

    /**
     * Create manual HR override
     */
    public static function createManualOverride(
        int $employeeId,
        $date,
        ?string $statusOverride,
        ?int $minutes,
        string $reason,
        int $userId
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'type' => self::TYPE_MANUAL_OVERRIDE,
            'status_override' => $statusOverride,
            'adjustment_minutes' => $minutes ?? 0,
            'reason' => $reason,
            'created_by' => $userId,
        ]);
    }

    /**
     * Remove adjustments for a source (when cancelled)
     */
    public static function removeForSource(string $sourceType, int $sourceId): int
    {
        return self::where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }

    /**
     * Get active adjustment for employee/date (latest by type priority)
     */
    public static function getActiveForDate(int $employeeId, $date): ?self
    {
        return self::where('employee_id', $employeeId)
            ->where('date', $date)
            ->whereNotNull('status_override')
            ->latest()
            ->first();
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_LEAVE => 'Cuti',
            self::TYPE_SICK => 'Sakit',
            self::TYPE_PERMISSION => 'Izin',
            self::TYPE_OVERTIME_ADD => 'Lembur Ditambah',
            self::TYPE_OVERTIME_CANCEL => 'Lembur Dibatalkan',
            self::TYPE_LATE_WAIVE => 'Telat Dihapuskan',
            self::TYPE_MANUAL_OVERRIDE => 'Koreksi Manual',
            default => $this->type,
        };
    }
}
