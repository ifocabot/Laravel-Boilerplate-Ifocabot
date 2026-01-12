<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
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

/**
 * Attendance Summary Service
 * 
 * ⚠️ DEPRECATION NOTICE:
 * This service is being phased out in favor of AttendanceRebuildService.
 * 
 * Still in use:
 * - createPlannedSummaryRow() - for initializing daily rows from schedule
 * - ensureRowsForDate() - cron job for pre-creating day's rows
 * - evaluateEndOfDay() - end-of-day status finalization
 * - Period locking/aggregation methods
 * 
 * DEPRECATED (use AttendanceRebuildService instead):
 * - recalculate() - now dispatched via RecalculateAttendanceJob
 * - Direct summary manipulation
 * 
 * The single source of truth is: Events → RebuildService → Summary
 */
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

        // Determine initial status - ALWAYS use enum values
        $status = AttendanceStatus::ABSENT->value; // default

        if ($isHoliday) {
            $status = AttendanceStatus::HOLIDAY->value;
        } elseif (!$schedule || $schedule->is_day_off) {
            $status = AttendanceStatus::OFFDAY->value;
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
     * 
     * @deprecated Use AttendanceRebuildService::rebuildDay() instead.
     *             This method now delegates to RebuildService for single source of truth.
     */
    public function recalculate(int $employeeId, $date): AttendanceSummary
    {
        $date = Carbon::parse($date)->startOfDay();

        // Delegate to RebuildService - the single source of computation
        $rebuildService = app(AttendanceRebuildService::class);

        return $rebuildService->rebuildDay($employeeId, $date);
    }

    /**
     * Perform the actual recalculation
     * 
     * @deprecated No longer used - recalculate() now delegates to AttendanceRebuildService.
     *             Kept for backward compatibility only.
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
            $summary->status = AttendanceStatus::HOLIDAY;
            $sourceFlags[] = 'holiday';
        } elseif ($leaveRequest) {
            $summary->status = AttendanceStatus::fromString($this->mapLeaveTypeToStatus($leaveRequest->leave_type));
            $summary->leave_request_id = $leaveRequest->id;
            $sourceFlags[] = 'leave';
        } elseif (!$schedule || $schedule->is_day_off) {
            $summary->status = AttendanceStatus::OFFDAY;
            $sourceFlags[] = 'schedule';
        } elseif ($log) {
            $this->calculateFromLog($summary, $log, $schedule);
            $sourceFlags[] = 'clock';
        } else {
            // Scheduled to work but no log and no leave = alpha/absent
            // We leave as 'absent' until end of day, then batch mark as alpha
            $summary->status = AttendanceStatus::ABSENT;
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
            $summary->status = AttendanceStatus::ABSENT;
            return;
        }

        if ($log->is_late ?? false) {
            $summary->status = AttendanceStatus::LATE;
            $summary->late_minutes = $log->late_duration_minutes ?? 0;
        } else {
            $summary->status = AttendanceStatus::PRESENT;
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
     * Evaluate and finalize status for dates that have passed
     * Called by daily scheduler at 23:55
     * 
     * This delegates to RebuildService for deterministic evaluation.
     * Single source of truth: Events → RebuildService → Summary
     */
    public function evaluateEndOfDay($date): array
    {
        $date = Carbon::parse($date)->startOfDay();
        $results = ['rebuilt' => 0, 'skipped_locked' => 0];

        // Get all employees with summaries for this date that need evaluation
        $summaries = AttendanceSummary::where('date', $date->toDateString())
            ->where('is_locked_for_payroll', false)
            ->get();

        $rebuildService = app(AttendanceRebuildService::class);

        foreach ($summaries as $summary) {
            try {
                // Delegate to RebuildService - the SINGLE source of truth
                $rebuildService->rebuildDay($summary->employee_id, $date);
                $results['rebuilt']++;
            } catch (\Exception $e) {
                Log::error('EOD rebuild failed', [
                    'employee_id' => $summary->employee_id,
                    'date' => $date->toDateString(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('End of day attendance evaluation via RebuildService', [
            'date' => $date->toDateString(),
            'results' => $results,
        ]);

        return $results;
    }

    /**
     * @deprecated No longer used - evaluateEndOfDay now delegates to RebuildService.
     * Kept for backward compatibility only.
     */
    protected function evaluateFinalStatus(AttendanceSummary $summary, Carbon $date): string
    {
        // Check for holiday
        if (NationalHoliday::where('date', $date->toDateString())->exists()) {
            return 'holiday';
        }

        // Check for approved leave
        $leave = LeaveRequest::where('employee_id', $summary->employee_id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if ($leave) {
            return $this->mapLeaveTypeToStatus($leave->leaveType);
        }

        // Check schedule - if no schedule or day off, not alpha
        $schedule = EmployeeSchedule::where('employee_id', $summary->employee_id)
            ->whereDate('date', $date)
            ->first();

        if (!$schedule || $schedule->is_day_off) {
            return 'offday';
        }

        // Has schedule, no clock in, no leave = absent (will be marked alpha)
        return 'absent';
    }

    /**
     * @deprecated Use evaluateEndOfDay() instead
     * Kept for backward compatibility
     */
    public function markAlphaForDate($date): int
    {
        $results = $this->evaluateEndOfDay($date);
        return $results['rebuilt'] ?? 0;
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

        // STEP 3 FIX: Use firstOrCreate for idempotency (prevent duplicates)
        $adjustment = PayrollAdjustment::firstOrCreate(
            // Natural key - lookup criteria
            [
                'employee_id' => $summary->employee_id,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'source_date' => $summary->date,
            ],
            // Attributes if creating new
            [
                'payroll_period_id' => $targetPeriod->id,
                'source_period_id' => $summary->payroll_period_id,
                'amount_minutes' => $amountMinutes,
                'reason' => $reason,
                'status' => PayrollAdjustment::STATUS_PENDING,
                'created_by' => $userId,
            ]
        );

        if ($adjustment->wasRecentlyCreated) {
            Log::info('Created payroll adjustment for locked period', [
                'adjustment_id' => $adjustment->id,
                'source_summary_id' => $summary->id,
                'target_period_id' => $targetPeriod->id,
            ]);
        }

        return $adjustment;
    }

    /**
     * STEP 2 FIX: Deterministic period targeting
     * 
     * Priority:
     * 1. If source period exists, find NEXT period after it
     * 2. Fallback: current active period
     * 3. Last fallback: next editable period
     */
    protected function findTargetPeriodForAdjustment(?int $sourcePeriodId): ?PayrollPeriod
    {
        // 1. If source period exists, get NEXT period
        if ($sourcePeriodId) {
            $source = PayrollPeriod::find($sourcePeriodId);
            if ($source) {
                $next = PayrollPeriod::whereDate('start_date', '>', $source->end_date)
                    ->whereIn('status', ['draft', 'processing'])
                    ->orderBy('start_date', 'asc')
                    ->first();
                if ($next)
                    return $next;
            }
        }

        // 2. Fallback: current active period
        $current = PayrollPeriod::whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->whereIn('status', ['draft', 'processing'])
            ->first();
        if ($current)
            return $current;

        // 3. Last fallback: next editable period
        return PayrollPeriod::whereIn('status', ['draft', 'processing'])
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
