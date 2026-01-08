<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class AttendancePeriodSummary extends Model
{
    protected $table = 'attendance_period_summaries';

    protected $fillable = [
        'employee_id',
        'payroll_period_id',
        'present_days',
        'alpha_days',
        'leave_days',
        'sick_days',
        'permission_days',
        'late_days',
        'offday_days',
        'holiday_days',
        'wfh_days',
        'business_trip_days',
        'scheduled_work_days',
        'total_worked_minutes',
        'total_late_minutes',
        'total_early_leave_minutes',
        'total_detected_overtime_minutes',
        'total_approved_overtime_minutes',
        'is_locked',
        'locked_at',
        'locked_by',
        'generated_at',
        'generated_by',
    ];

    protected $casts = [
        'present_days' => 'integer',
        'alpha_days' => 'integer',
        'leave_days' => 'integer',
        'sick_days' => 'integer',
        'permission_days' => 'integer',
        'late_days' => 'integer',
        'offday_days' => 'integer',
        'holiday_days' => 'integer',
        'wfh_days' => 'integer',
        'business_trip_days' => 'integer',
        'scheduled_work_days' => 'integer',
        'total_worked_minutes' => 'integer',
        'total_late_minutes' => 'integer',
        'total_early_leave_minutes' => 'integer',
        'total_detected_overtime_minutes' => 'integer',
        'total_approved_overtime_minutes' => 'integer',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'generated_at' => 'datetime',
    ];

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

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Accessors
     */
    protected function totalWorkedHours(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->total_worked_minutes / 60, 2)
        );
    }

    protected function totalApprovedOvertimeHours(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->total_approved_overtime_minutes / 60, 2)
        );
    }

    protected function attendanceRate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->scheduled_work_days <= 0)
                    return 0;
                return round(($this->present_days / $this->scheduled_work_days) * 100, 1);
            }
        );
    }

    /**
     * Check if locked
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Lock this period summary
     */
    public function lock(int $userId): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId,
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

    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Generate or update from daily summaries
     */
    public static function generateFromDailySummaries(
        int $employeeId,
        int $payrollPeriodId,
        $startDate,
        $endDate,
        ?int $userId = null
    ): self {
        $dailySummaries = AttendanceSummary::forEmployee($employeeId)
            ->forDateRange($startDate, $endDate)
            ->get();

        $data = [
            'present_days' => $dailySummaries->whereIn('status', ['present', 'late'])->count(),
            'alpha_days' => $dailySummaries->whereIn('status', ['alpha', 'absent'])->count(),
            'leave_days' => $dailySummaries->where('status', 'leave')->count(),
            'sick_days' => $dailySummaries->where('status', 'sick')->count(),
            'permission_days' => $dailySummaries->where('status', 'permission')->count(),
            'late_days' => $dailySummaries->where('status', 'late')->count(),
            'offday_days' => $dailySummaries->where('status', 'offday')->count(),
            'holiday_days' => $dailySummaries->where('status', 'holiday')->count(),
            'wfh_days' => $dailySummaries->where('status', 'wfh')->count(),
            'business_trip_days' => $dailySummaries->where('status', 'business_trip')->count(),
            'scheduled_work_days' => $dailySummaries->whereNotIn('status', ['offday', 'holiday'])->count(),
            'total_worked_minutes' => $dailySummaries->sum('total_work_minutes'),
            'total_late_minutes' => $dailySummaries->sum('late_minutes'),
            'total_early_leave_minutes' => $dailySummaries->sum('early_leave_minutes'),
            'total_detected_overtime_minutes' => $dailySummaries->sum('detected_overtime_minutes'),
            'total_approved_overtime_minutes' => $dailySummaries->sum('approved_overtime_minutes'),
            'generated_at' => now(),
            'generated_by' => $userId,
        ];

        return self::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'payroll_period_id' => $payrollPeriodId,
            ],
            $data
        );
    }
}
