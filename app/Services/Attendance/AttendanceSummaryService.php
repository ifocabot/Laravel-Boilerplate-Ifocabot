<?php

namespace App\Services\Attendance;

use App\Models\AttendanceAdjustment;
use App\Models\AttendanceLog;
use App\Models\AttendancePeriodSummary;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\NationalHoliday;
use App\Models\OvertimeRequest;
use App\Models\PayrollAdjustment;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceSummaryService
{
    /**
     * ========================================
     * DAILY ROW MANAGEMENT
     * ========================================
     */

    /**
     * Ensure daily summary rows exist for scheduled dates
     * Called when schedule is created or payroll period is prepared
     */
    public function ensureDailyRowsForSchedule(int $employeeId, $startDate, $endDate): int
    {
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->startOfDay();
        $period = CarbonPeriod::create($startDate, $endDate);

        $created = 0;

        foreach ($period as $date) {
            // Check if row already exists
            $exists = AttendanceSummary::where('employee_id', $employeeId)
                ->where('date', $date->toDateString())
                ->exists();

            if (!$exists) {
                $summary = $this->createPlannedSummaryRow($employeeId, $date);
                if ($summary)
                    $created++;
            }
        }

        Log::info('Ensured daily rows for schedule', [
            'employee_id' => $employeeId,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'created' => $created,
        ]);

        return $created;
    }

    /**
     * Create a planned summary row from schedule
     */
    protected function createPlannedSummaryRow(int $employeeId, Carbon $date): ?AttendanceSummary
    {
        // Get schedule for this date
        $schedule = EmployeeSchedule::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->with('shift')
            ->first();

        // Check if holiday
        $isHoliday = NationalHoliday::where('date', $date->toDateString())->exists();

        // Determine initial status
        $status = 'absent'; // default

        if ($isHoliday) {
            $status = 'holiday';
        } elseif (!$schedule || $schedule->is_day_off) {
            $status = 'offday';
        }

        // Create summary row
        return AttendanceSummary::create([
            'employee_id' => $employeeId,
            'date' => $date->toDateString(),
            'shift_id' => $schedule?->shift_id,
            'schedule_id' => $schedule?->id,
            'status' => $status,
            'planned_start_at' => $schedule?->shift ? $this->buildShiftDatetime($date, $schedule->shift->start_time) : null,
            'planned_end_at' => $schedule?->shift ? $this->buildShiftEndDatetime($date, $schedule->shift) : null,
            'source_flags' => ['schedule'],
        ]);
    }

    /**
     * Build datetime from date and time, handling cross-midnight
     */
    protected function buildShiftDatetime(Carbon $date, string $time): Carbon
    {
        return Carbon::parse($date->toDateString() . ' ' . $time);
    }

    /**
     * Build shift end datetime, handling cross-midnight shifts
     */
    protected function buildShiftEndDatetime(Carbon $date, $shift): Carbon
    {
        $startTime = Carbon::parse($shift->start_time);
        $endTime = Carbon::parse($shift->end_time);

        $endDatetime = Carbon::parse($date->toDateString() . ' ' . $shift->end_time);

        // If end time is before start time, it's cross-midnight
        if ($endTime->lt($startTime)) {
            $endDatetime->addDay();
        }

        return $endDatetime;
    }

    /**
     * ========================================
     * RECALCULATION
     * ========================================
     */

    /**
     * Recalculate daily summary for employee on specific date
     * This is the MAIN method called by all triggers
     */
    public function recalculate(int $employeeId, $date): AttendanceSummary
    {
        $date = Carbon::parse($date)->startOfDay();

        // Get or create summary
        $summary = AttendanceSummary::firstOrCreate(
            ['employee_id' => $employeeId, 'date' => $date->toDateString()],
            ['status' => 'absent']
        );

        // Check if locked - if so, create adjustment instead
        if ($summary->is_locked_for_payroll) {
            Log::warning('Cannot recalculate locked summary', [
                'summary_id' => $summary->id,
                'employee_id' => $employeeId,
                'date' => $date->toDateString(),
            ]);
            return $summary;
        }

        return $this->performRecalculation($summary);
    }

    /**
     * Perform the actual recalculation
     */
    protected function performRecalculation(AttendanceSummary $summary): AttendanceSummary
    {
        $date = Carbon::parse($summary->date);
        $employeeId = $summary->employee_id;

        // Gather all data sources
        $schedule = EmployeeSchedule::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->with('shift')
            ->first();

        $log = AttendanceLog::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->first();

        $leaveRequest = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        $overtimeRequest = OvertimeRequest::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->where('status', 'approved')
            ->first();

        $isHoliday = NationalHoliday::where('date', $date->toDateString())->exists();

        // Update schedule info
        $summary->schedule_id = $schedule?->id;
        $summary->shift_id = $schedule?->shift_id;

        if ($schedule?->shift) {
            $summary->planned_start_at = $this->buildShiftDatetime($date, $schedule->shift->start_time);
            $summary->planned_end_at = $this->buildShiftEndDatetime($date, $schedule->shift);
        }

        // Determine status and calculate times
        $sourceFlags = [];

        // Priority: Holiday > Leave > Log > Schedule
        if ($isHoliday) {
            $summary->status = 'holiday';
            $sourceFlags[] = 'holiday';
        } elseif ($leaveRequest) {
            $summary->status = $this->mapLeaveTypeToStatus($leaveRequest->leave_type);
            $summary->leave_request_id = $leaveRequest->id;
            $sourceFlags[] = 'leave';
        } elseif (!$schedule || $schedule->is_day_off) {
            $summary->status = 'offday';
            $sourceFlags[] = 'schedule';
        } elseif ($log) {
            $this->calculateFromLog($summary, $log, $schedule);
            $sourceFlags[] = 'clock';
        } else {
            // Scheduled to work but no log and no leave = alpha/absent
            // We leave as 'absent' until end of day, then batch mark as alpha
            $summary->status = 'absent';
            $sourceFlags[] = 'schedule';
        }

        // Handle overtime
        if ($overtimeRequest) {
            $summary->approved_overtime_minutes = $overtimeRequest->approved_duration_minutes;
            $summary->overtime_request_id = $overtimeRequest->id;
            $sourceFlags[] = 'overtime';
        }

        // ⭐ Check attendance adjustments ledger (makes regen-safe)
        $adjustment = AttendanceAdjustment::getActiveForDate($employeeId, $date);
        if ($adjustment) {
            if ($adjustment->status_override) {
                $summary->status = $adjustment->status_override;
            }
            if ($adjustment->adjustment_minutes !== 0) {
                // Add/subtract overtime minutes from adjustment
                $summary->approved_overtime_minutes =
                    ($summary->approved_overtime_minutes ?? 0) + $adjustment->adjustment_minutes;
            }
            $summary->notes = ($summary->notes ?? '') . ' [Adj: ' . $adjustment->type_label . ']';
            $sourceFlags[] = 'adjustment';
        }

        // Update source flags
        $summary->source_flags = array_unique(array_merge(
            $summary->source_flags ?? [],
            $sourceFlags
        ));

        $summary->save();

        Log::info('Recalculated attendance summary', [
            'summary_id' => $summary->id,
            'employee_id' => $summary->employee_id,
            'date' => $summary->date,
            'status' => $summary->status,
            'sources' => $sourceFlags,
        ]);

        return $summary;
    }

    /**
     * Calculate times from attendance log
     */
    protected function calculateFromLog(AttendanceSummary $summary, AttendanceLog $log, ?EmployeeSchedule $schedule): void
    {
        // Use correct field names from AttendanceLog model
        $summary->clock_in_at = $log->clock_in_time;
        $summary->clock_out_at = $log->clock_out_time;

        // Determine status based on log
        if (!$log->clock_in_time) {
            $summary->status = 'absent';
            return;
        }

        if ($log->is_late ?? false) {
            $summary->status = 'late';
            $summary->late_minutes = $log->late_duration_minutes ?? 0;
        } else {
            $summary->status = 'present';
        }

        // Calculate work duration
        $summary->total_work_minutes = $log->work_duration_minutes ?? 0;
        $summary->early_leave_minutes = $log->early_leave_minutes ?? 0;

        // Calculate detected overtime (system calculated, not approved)
        if ($schedule?->shift && $summary->total_work_minutes > 0) {
            $requiredMinutes = $schedule->shift->work_hours_required ?? ($schedule->shift->work_duration_minutes ?? 480);
            $detectedOT = max(0, $summary->total_work_minutes - $requiredMinutes);
            $summary->detected_overtime_minutes = $detectedOT;

            // Also update legacy field for backward compatibility
            $summary->overtime_minutes = $detectedOT;
        }
    }

    /**
     * Map leave type to attendance status
     */
    protected function mapLeaveTypeToStatus($leaveType): string
    {
        if (!$leaveType)
            return 'leave';

        $code = is_object($leaveType) ? ($leaveType->code ?? '') : '';

        return match (strtolower($code)) {
            'sick', 'sakit' => 'sick',
            'permission', 'izin' => 'permission',
            'wfh' => 'wfh',
            'business_trip', 'dinas' => 'business_trip',
            default => 'leave',
        };
    }

    /**
     * ========================================
     * BATCH OPERATIONS
     * ========================================
     */

    /**
     * Mark all unlogged scheduled work days as ALPHA
     * Called by daily scheduler at 23:55
     */
    public function markAlphaForDate($date): int
    {
        $date = Carbon::parse($date)->startOfDay();

        $updated = AttendanceSummary::where('date', $date->toDateString())
            ->where('status', 'absent')
            ->where('is_locked_for_payroll', false)
            ->update([
                'status' => 'alpha',
                'system_notes' => DB::raw("CONCAT(IFNULL(system_notes, ''), ' [Auto-marked as ALPHA]')"),
            ]);

        Log::info('Marked absent as alpha', [
            'date' => $date->toDateString(),
            'count' => $updated,
        ]);

        return $updated;
    }

    /**
     * Ensure summary rows exist for tomorrow (pre-create)
     * Called by scheduler at 00:10
     */
    public function ensureRowsForDate($date): int
    {
        $date = Carbon::parse($date)->startOfDay();

        // Get all employees with schedules for this date
        $schedules = EmployeeSchedule::whereDate('date', $date)
            ->with('shift')
            ->get();

        $created = 0;
        foreach ($schedules as $schedule) {
            $exists = AttendanceSummary::where('employee_id', $schedule->employee_id)
                ->where('date', $date->toDateString())
                ->exists();

            if (!$exists) {
                $this->createPlannedSummaryRow($schedule->employee_id, $date);
                $created++;
            }
        }

        Log::info('Pre-created summary rows for date', [
            'date' => $date->toDateString(),
            'created' => $created,
        ]);

        return $created;
    }

    /**
     * ========================================
     * PERIOD SUMMARY & LOCKING
     * ========================================
     */

    /**
     * Generate period summaries for a payroll period
     */
    public function generatePeriodSummaries(PayrollPeriod $period, ?int $userId = null): int
    {
        $startDate = $period->start_date;
        $endDate = $period->end_date;

        // Get all employees with attendance in this period
        $employeeIds = AttendanceSummary::whereBetween('date', [$startDate, $endDate])
            ->distinct()
            ->pluck('employee_id');

        $generated = 0;
        foreach ($employeeIds as $employeeId) {
            AttendancePeriodSummary::generateFromDailySummaries(
                $employeeId,
                $period->id,
                $startDate,
                $endDate,
                $userId
            );
            $generated++;
        }

        // Update period timestamp
        $period->update(['period_summary_generated_at' => now()]);

        Log::info('Generated period summaries', [
            'period_id' => $period->id,
            'employee_count' => $generated,
        ]);

        return $generated;
    }

    /**
     * Lock period for payroll processing
     */
    public function lockPeriod(PayrollPeriod $period, int $userId): array
    {
        return DB::transaction(function () use ($period, $userId) {
            // 1. Lock all daily summaries
            $lockedDailies = AttendanceSummary::whereBetween('date', [$period->start_date, $period->end_date])
                ->where('is_locked_for_payroll', false)
                ->update([
                    'is_locked_for_payroll' => true,
                    'locked_at' => now(),
                    'locked_by' => $userId,
                    'payroll_period_id' => $period->id,
                ]);

            // 2. Lock all period summaries
            $lockedPeriods = AttendancePeriodSummary::where('payroll_period_id', $period->id)
                ->where('is_locked', false)
                ->update([
                    'is_locked' => true,
                    'locked_at' => now(),
                    'locked_by' => $userId,
                ]);

            // 3. Update period
            $period->update([
                'attendance_locked' => true,
                'attendance_locked_at' => now(),
                'attendance_locked_by' => $userId,
            ]);

            Log::info('Period locked for payroll', [
                'period_id' => $period->id,
                'daily_locked' => $lockedDailies,
                'period_summaries_locked' => $lockedPeriods,
                'locked_by' => $userId,
            ]);

            return [
                'daily_locked' => $lockedDailies,
                'period_summaries_locked' => $lockedPeriods,
            ];
        });
    }

    /**
     * ========================================
     * ADJUSTMENT HANDLING
     * ========================================
     */

    /**
     * Handle change when period is locked - create adjustment
     */
    public function createAdjustmentIfLocked(
        AttendanceSummary $summary,
        string $type,
        int $amountMinutes,
        string $reason,
        int $userId,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?PayrollAdjustment {
        if (!$summary->is_locked_for_payroll) {
            // Not locked, no adjustment needed
            return null;
        }

        // Find target period (current or next open period)
        $targetPeriod = $this->findTargetPeriodForAdjustment($summary->payroll_period_id);

        if (!$targetPeriod) {
            throw new \Exception('No open payroll period found for adjustment');
        }

        $adjustment = PayrollAdjustment::create([
            'employee_id' => $summary->employee_id,
            'payroll_period_id' => $targetPeriod->id,
            'source_period_id' => $summary->payroll_period_id,
            'source_date' => $summary->date,
            'type' => $type,
            'amount_minutes' => $amountMinutes,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => PayrollAdjustment::STATUS_PENDING,
            'created_by' => $userId,
        ]);

        Log::info('Created payroll adjustment for locked period', [
            'adjustment_id' => $adjustment->id,
            'source_summary_id' => $summary->id,
            'target_period_id' => $targetPeriod->id,
        ]);

        return $adjustment;
    }

    /**
     * Find target period for adjustment (next open period)
     */
    protected function findTargetPeriodForAdjustment(?int $sourcePeriodId): ?PayrollPeriod
    {
        // Try to find next open period
        return PayrollPeriod::where('attendance_locked', false)
            ->where('status', 'draft')
            ->orderBy('start_date', 'asc')
            ->first();
    }

    /**
     * Handle overtime approved after period lock
     */
    public function handleLateOvertimeApproval(OvertimeRequest $overtime, int $userId): ?PayrollAdjustment
    {
        $summary = AttendanceSummary::where('employee_id', $overtime->employee_id)
            ->where('date', $overtime->date)
            ->first();

        if (!$summary || !$summary->is_locked_for_payroll) {
            // Not locked, normal sync will handle it
            return null;
        }

        return $this->createAdjustmentIfLocked(
            $summary,
            PayrollAdjustment::TYPE_OVERTIME,
            $overtime->approved_duration_minutes,
            "Overtime disetujui setelah periode dikunci",
            $userId,
            OvertimeRequest::class,
            $overtime->id
        );
    }
}
