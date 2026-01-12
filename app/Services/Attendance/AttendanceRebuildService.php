<?php

namespace App\Services\Attendance;

use App\Models\AttendanceEvent;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Enums\AttendanceEventType;
use App\Enums\AttendanceStatus;
use App\Enums\AttendanceSummaryStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Attendance Rebuild Service
 * 
 * Deterministic recalculation of attendance summaries from events.
 * For audit/compliance: running rebuild at any time produces IDENTICAL results.
 */
class AttendanceRebuildService
{
    /**
     * Rebuild a single day's attendance from events only.
     * 
     * This is DETERMINISTIC: same events → same result, always.
     */
    public function rebuildDay(int $employeeId, Carbon $date): AttendanceSummary
    {
        // Fetch all events for this employee/date in chronological order
        $events = AttendanceEvent::forEmployee($employeeId)
            ->forDate($date)
            ->affectingSummary()
            ->chronological()
            ->get();

        // Initialize fresh state
        $state = $this->createFreshState($employeeId, $date);

        // Apply events one by one (reducer pattern)
        foreach ($events as $event) {
            $this->applyEvent($state, $event);
        }

        // Persist to summary (upsert)
        return $this->persistState($state);
    }

    /**
     * Rebuild attendance for a date range
     */
    public function rebuildPeriod(int $employeeId, Carbon $startDate, Carbon $endDate): Collection
    {
        $summaries = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $summary = $this->rebuildDay($employeeId, $currentDate);
            $summaries->push($summary);
            $currentDate->addDay();
        }

        return $summaries;
    }

    /**
     * Rebuild all employees for a date range (e.g., for payroll period)
     */
    public function rebuildAll(Carbon $startDate, Carbon $endDate): int
    {
        $employees = Employee::where('status', 'active')->pluck('id');
        $count = 0;

        foreach ($employees as $employeeId) {
            $this->rebuildPeriod($employeeId, $startDate, $endDate);
            $count++;
        }

        \Log::info('Rebuilt attendance summaries', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'employee_count' => $count,
        ]);

        return $count;
    }

    /**
     * Verify current summaries match what would be rebuilt.
     * 
     * Returns discrepancies for audit.
     */
    public function verify(int $employeeId, Carbon $startDate, Carbon $endDate): array
    {
        $discrepancies = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $existing = AttendanceSummary::forEmployee($employeeId)
                ->forDate($currentDate)
                ->first();

            $rebuilt = $this->rebuildDayWithoutSave($employeeId, $currentDate);

            if ($existing) {
                $diffs = $this->compareStates($existing, $rebuilt);
                if (!empty($diffs)) {
                    $discrepancies[] = [
                        'date' => $currentDate->format('Y-m-d'),
                        'differences' => $diffs,
                    ];
                }
            }

            $currentDate->addDay();
        }

        return [
            'employee_id' => $employeeId,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'total_days' => $startDate->diffInDays($endDate) + 1,
            'discrepancy_count' => count($discrepancies),
            'discrepancies' => $discrepancies,
            'verified_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Create fresh state object, hydrated from schedule & holiday baseline.
     * 
     * This is critical: without schedule/holiday context, we can't tell
     * offday vs absent, holiday vs absent, or detect late/early correctly.
     */
    private function createFreshState(int $employeeId, Carbon $date): object
    {
        // Get schedule for this date
        $schedule = \App\Models\EmployeeSchedule::where('employee_id', $employeeId)
            ->where('date', $date->format('Y-m-d'))
            ->with('shift')
            ->first();

        // Check for national holiday
        $holiday = \App\Models\NationalHoliday::where('date', $date->format('Y-m-d'))->first();

        // Determine baseline status from schedule/holiday
        $baselineStatus = AttendanceStatus::ABSENT;  // Default if no clock-in
        $shiftId = null;
        $isWorkday = true;

        if ($holiday) {
            $baselineStatus = AttendanceStatus::HOLIDAY;
            $isWorkday = false;
        } elseif ($schedule) {
            $shiftId = $schedule->shift_id;

            if ($schedule->is_day_off) {
                $baselineStatus = AttendanceStatus::OFFDAY;
                $isWorkday = false;
            } elseif ($schedule->is_leave && $schedule->leave_request_id) {
                $baselineStatus = AttendanceStatus::LEAVE;
                $isWorkday = false;
            }
        } else {
            // No schedule = check if weekend (default non-working)
            if ($date->isWeekend()) {
                $baselineStatus = AttendanceStatus::OFFDAY;
                $isWorkday = false;
            }
        }

        return (object) [
            'employee_id' => $employeeId,
            'date' => $date,
            'baseline_status' => $baselineStatus,  // Fix #2: Track baseline for cancel restore
            'status' => $baselineStatus,
            'is_workday' => $isWorkday,
            'clock_in_at' => null,
            'clock_out_at' => null,
            'total_work_minutes' => 0,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 0,
            'approved_overtime_minutes' => 0,
            'overtime_request_id' => null,
            'leave_request_id' => $schedule?->leave_request_id,
            'shift_id' => $shiftId,
            'shift' => $schedule?->shift,  // Keep shift object for late/early calc
            'notes' => $holiday?->name,
            'system_notes' => $holiday ? ["Libur nasional: {$holiday->name}"] : [],
        ];
    }

    /**
     * Apply a single event to the state (reducer)
     */
    private function applyEvent(object $state, AttendanceEvent $event): void
    {
        $payload = $event->payload ?? [];

        match ($event->event_type) {
            AttendanceEventType::CLOCK_IN => $this->applyClockIn($state, $payload),
            AttendanceEventType::CLOCK_OUT => $this->applyClockOut($state, $payload),
            AttendanceEventType::CLOCK_IN_CORRECTED => $this->applyClockInCorrected($state, $payload),
            AttendanceEventType::CLOCK_OUT_CORRECTED => $this->applyClockOutCorrected($state, $payload),
            AttendanceEventType::OVERTIME_APPROVED => $this->applyOvertimeApproved($state, $payload, $event),
            AttendanceEventType::OVERTIME_CANCELLED => $this->applyOvertimeCancelled($state, $event),
            AttendanceEventType::LEAVE_APPROVED => $this->applyLeaveApproved($state, $payload, $event),
            AttendanceEventType::LEAVE_CANCELLED => $this->applyLeaveCancelled($state),
            AttendanceEventType::LATE_WAIVED => $this->applyLateWaived($state, $payload),
            AttendanceEventType::EARLY_LEAVE_WAIVED => $this->applyEarlyLeaveWaived($state, $payload),
            AttendanceEventType::STATUS_OVERRIDE => $this->applyStatusOverride($state, $payload),
            AttendanceEventType::MANUAL_CORRECTION => $this->applyManualCorrection($state, $payload),
            default => null, // Lifecycle events don't affect state
        };
    }

    private function applyClockIn(object $state, array $payload): void
    {
        $state->clock_in_at = $payload['time'] ?? null;

        // Fix #5: Recalculate late from shift if not in payload (or recalc mode)
        if (isset($payload['late_minutes'])) {
            // Use stored value (backward compatibility)
            $state->late_minutes = $payload['late_minutes'];
            $state->status = ($payload['is_late'] ?? false) ? AttendanceStatus::LATE : AttendanceStatus::PRESENT;
        } else {
            // Recalculate from shift (more resilient to rule changes)
            $lateCalc = $this->calculateLateMinutesFromShift($state, $payload);
            $state->late_minutes = $lateCalc['minutes'];
            $state->status = $lateCalc['is_late'] ? AttendanceStatus::LATE : AttendanceStatus::PRESENT;
        }

        $state->system_notes[] = "Clock in: " . ($payload['time'] ?? 'N/A');
    }

    /**
     * Calculate late minutes from shift start time
     * Fix #5: Makes rebuild resilient to rule changes
     * 
     * BUG FIX: Use copy() to avoid mutating shiftStart when adding tolerance
     * FIX #4: Use full datetime based on state->date for correct overnight handling
     * FIX: Handle overnight shift with post-midnight clock-in
     */
    private function calculateLateMinutesFromShift(object $state, array $payload): array
    {
        if (!$state->shift || !$state->shift->start_time) {
            return ['minutes' => 0, 'is_late' => false];
        }

        // FIX #4: Build full datetime using state->date
        $baseDate = $state->date->format('Y-m-d');
        $clockIn = Carbon::parse($baseDate . ' ' . ($payload['time'] ?? '00:00'));
        $shiftStart = Carbon::parse($baseDate . ' ' . $state->shift->start_time);
        $tolerance = $state->shift->late_tolerance_minutes ?? 0;

        // FIX: Handle overnight shift (clock-in after midnight)
        // If shift is overnight or shift start > clock-in time, clock-in is next day
        $isOvernight = $state->shift->is_overnight ?? false;
        if (!$isOvernight && $state->shift->end_time) {
            $shiftEnd = Carbon::parse($state->shift->end_time);
            $shiftStartTime = Carbon::parse($state->shift->start_time);
            $isOvernight = $shiftEnd->lt($shiftStartTime);
        }

        if ($isOvernight && $clockIn->lt($shiftStart)) {
            // Clock-in at 01:00 with shift start 22:00 → clock-in is next day
            $clockIn = $clockIn->addDay();
        }

        // Use copy() to get tolerance boundary without mutating shiftStart
        $toleranceBoundary = $shiftStart->copy()->addMinutes($tolerance);

        if ($clockIn->gt($toleranceBoundary)) {
            // Calculate diff from ORIGINAL shiftStart, not tolerance boundary
            $minutes = $shiftStart->diffInMinutes($clockIn);
            return ['minutes' => $minutes, 'is_late' => true];
        }

        return ['minutes' => 0, 'is_late' => false];
    }

    private function applyClockOut(object $state, array $payload): void
    {
        $state->clock_out_at = $payload['time'] ?? null;

        // Issue 1 Fix: Always recalculate work minutes from clock times (deterministic)
        // Payload work_duration_minutes is only used as fallback if recalc fails
        $this->recalculateWorkMinutes($state);
        if ($state->total_work_minutes === 0) {
            // Fallback to payload if recalc couldn't determine
            $state->total_work_minutes = $payload['work_duration_minutes'] ?? 0;
        }

        // Always calculate early leave from shift
        $state->early_leave_minutes = $this->calculateEarlyLeaveMinutes($state, $payload);

        $state->system_notes[] = "Clock out: " . ($payload['time'] ?? 'N/A');
    }

    /**
     * Calculate early leave minutes from shift end time
     * FIX #4: Uses full datetime based on state->date
     * Handles overnight shifts where end_time < start_time
     */
    private function calculateEarlyLeaveMinutes(object $state, array $payload): int
    {
        if (!$state->shift || !$state->shift->end_time) {
            return 0;
        }

        $baseDate = $state->date->format('Y-m-d');
        $clockOut = Carbon::parse($baseDate . ' ' . ($payload['time'] ?? '00:00'));
        $shiftEnd = Carbon::parse($baseDate . ' ' . $state->shift->end_time);

        // Handle overnight shift: if shift is overnight or end_time < start_time
        $isOvernight = $state->shift->is_overnight ?? false;
        if (!$isOvernight && $state->shift->start_time && $state->shift->end_time) {
            $shiftStartTime = Carbon::parse($state->shift->start_time);
            $shiftEndTime = Carbon::parse($state->shift->end_time);
            $isOvernight = $shiftEndTime->lt($shiftStartTime);
        }

        // For overnight shifts, shift end is next day
        if ($isOvernight) {
            $shiftEnd = $shiftEnd->addDay();
        }

        // Auto-detect: if clock_out time < shift start time, it's next day
        $clockInTime = $state->clock_in_at ? Carbon::parse($baseDate . ' ' . $state->clock_in_at) : null;
        if ($clockInTime && $clockOut->lt($clockInTime)) {
            $clockOut = $clockOut->addDay();
        }

        if ($clockOut->lt($shiftEnd)) {
            return $shiftEnd->diffInMinutes($clockOut);
        }

        return 0;
    }

    /**
     * Build full datetime for clock_out, handling overnight shifts
     * 
     * Issue 5 Fix: Uses clock_in comparison as primary heuristic
     * instead of fragile hour < 12 assumption
     */
    private function buildClockOutDatetime(object $state): ?Carbon
    {
        if (!$state->clock_out_at) {
            return null;
        }

        $baseDate = $state->date->format('Y-m-d');
        $clockOutTime = Carbon::parse($state->clock_out_at);
        $clockInTime = $state->clock_in_at ? Carbon::parse($state->clock_in_at) : null;

        // Primary heuristic: if clock_out time < clock_in time, it's next day
        // This is the MOST reliable indicator
        if ($clockInTime && $clockOutTime->format('H:i:s') < $clockInTime->format('H:i:s')) {
            return Carbon::parse($baseDate . ' ' . $state->clock_out_at)->addDay();
        }

        // Secondary: check shift's is_overnight flag + expected end time
        $isOvernight = $state->shift->is_overnight ?? false;
        if ($isOvernight && $state->shift->end_time) {
            $shiftEndTime = Carbon::parse($state->shift->end_time);
            // If shift end is in early hours (before noon), clock_out should be next day
            if ($shiftEndTime->hour < 12 && $clockOutTime->hour < 12) {
                return Carbon::parse($baseDate . ' ' . $state->clock_out_at)->addDay();
            }
        }

        return Carbon::parse($baseDate . ' ' . $state->clock_out_at);
    }

    private function applyClockInCorrected(object $state, array $payload): void
    {
        $oldTime = $payload['old_time'] ?? $state->clock_in_at ?? 'N/A';
        $newTime = $payload['new_time'] ?? $state->clock_in_at;
        $state->clock_in_at = $newTime;

        // Issue 2 Fix: Recalculate late minutes after correction
        $lateCalc = $this->calculateLateMinutesFromShift($state, ['time' => $state->clock_in_at]);
        $state->late_minutes = $lateCalc['minutes'];
        $state->status = $lateCalc['is_late'] ? AttendanceStatus::LATE : AttendanceStatus::PRESENT;

        // Recalculate work minutes if both clock times exist
        $this->recalculateWorkMinutes($state);

        $state->system_notes[] = "Clock in dikoreksi: {$oldTime} → {$newTime}";
    }

    private function applyClockOutCorrected(object $state, array $payload): void
    {
        $oldTime = $payload['old_time'] ?? $state->clock_out_at ?? 'N/A';
        $newTime = $payload['new_time'] ?? $state->clock_out_at;
        $state->clock_out_at = $newTime;

        // Issue 2 Fix: Recalculate early leave and work minutes after correction
        $state->early_leave_minutes = $this->calculateEarlyLeaveMinutes($state, ['time' => $state->clock_out_at]);

        // Recalculate work minutes if both clock times exist
        $this->recalculateWorkMinutes($state);

        $state->system_notes[] = "Clock out dikoreksi: {$oldTime} → {$newTime}";
    }

    /**
     * Issue 3 Fix: Recalculate total_work_minutes from clock in/out times
     * This ensures deterministic calculation even if payload was wrong
     * Issue 2 Fix: Break datetime uses baseDate for overnight-safe calculation
     */
    private function recalculateWorkMinutes(object $state): void
    {
        if (!$state->clock_in_at || !$state->clock_out_at) {
            return;
        }

        $baseDate = $state->date->format('Y-m-d');
        $clockIn = Carbon::parse($baseDate . ' ' . $state->clock_in_at);
        $clockOut = $this->buildClockOutDatetime($state);

        if ($clockOut) {
            $totalMinutes = $clockIn->diffInMinutes($clockOut);

            // Subtract break time if shift has it
            // Issue 2 Fix: Build break datetime with baseDate for overnight-safe
            $breakMinutes = 0;
            if ($state->shift && $state->shift->break_start && $state->shift->break_end) {
                $breakStart = Carbon::parse($baseDate . ' ' . $state->shift->break_start);
                $breakEnd = Carbon::parse($baseDate . ' ' . $state->shift->break_end);

                // Handle overnight break (break_end < break_start)
                if ($breakEnd->lt($breakStart)) {
                    $breakEnd = $breakEnd->addDay();
                }

                $breakMinutes = $breakStart->diffInMinutes($breakEnd);
            }

            $state->total_work_minutes = max(0, $totalMinutes - $breakMinutes);
        }
    }

    private function applyOvertimeApproved(object $state, array $payload, AttendanceEvent $event): void
    {
        $state->approved_overtime_minutes = $payload['approved_minutes'] ?? 0;
        $state->overtime_request_id = $event->source_id;
        $state->system_notes[] = "Overtime disetujui: {$state->approved_overtime_minutes} menit";
    }

    private function applyOvertimeCancelled(object $state, AttendanceEvent $event): void
    {
        $state->approved_overtime_minutes = 0;
        $state->overtime_request_id = null;
        $state->system_notes[] = "Overtime dibatalkan";
    }

    private function applyLeaveApproved(object $state, array $payload, AttendanceEvent $event): void
    {
        // Map leave type to status or use override
        $statusOverride = $payload['status_override'] ?? null;
        if ($statusOverride) {
            $state->status = AttendanceStatus::tryFrom($statusOverride) ?? AttendanceStatus::LEAVE;
        } else {
            $state->status = AttendanceStatus::LEAVE;
        }
        $state->leave_request_id = $event->source_id;
        $state->system_notes[] = "Cuti: " . ($payload['leave_type'] ?? 'Cuti');
    }

    private function applyLeaveCancelled(object $state): void
    {
        // Fix #2: Restore to baseline (holiday/offday), not blindly to absent
        if ($state->clock_in_at) {
            $state->status = $state->late_minutes > 0 ? AttendanceStatus::LATE : AttendanceStatus::PRESENT;
        } else {
            // Restore to baseline (could be holiday, offday, or absent)
            $state->status = $state->baseline_status ?? AttendanceStatus::ABSENT;
        }
        $state->leave_request_id = null;
        $statusLabel = $state->status instanceof AttendanceStatus ? $state->status->value : $state->status;
        $state->system_notes[] = "Cuti dibatalkan, status: {$statusLabel}";
    }

    private function applyLateWaived(object $state, array $payload): void
    {
        $state->late_minutes = 0;

        // Issue 4 Fix: Only set PRESENT if it makes sense for this baseline
        // If baseline is HOLIDAY/OFFDAY, don't change status - just clear late
        $baseline = $state->baseline_status ?? AttendanceStatus::ABSENT;

        if ($baseline->isNonWorkingDay()) {
            // Keep the baseline status - this is a non-working day
            // late waive only clears the late_minutes penalty
            $state->system_notes[] = "Keterlambatan dihapuskan (hari libur): " . ($payload['reason'] ?? '-');
        } else {
            // Normal workday - upgrade from late to present
            $state->status = AttendanceStatus::PRESENT;
            $state->system_notes[] = "Keterlambatan dihapuskan: " . ($payload['reason'] ?? '-');
        }
    }

    private function applyEarlyLeaveWaived(object $state, array $payload): void
    {
        $state->early_leave_minutes = 0;
        $state->system_notes[] = "Pulang cepat dihapuskan";
    }

    private function applyStatusOverride(object $state, array $payload): void
    {
        $newStatus = $payload['new_status'] ?? null;
        if ($newStatus) {
            $state->status = AttendanceStatus::tryFrom($newStatus) ?? $state->status;
        }
        $state->system_notes[] = "Status diubah: {$payload['old_status']} → {$payload['new_status']}";
    }

    private function applyManualCorrection(object $state, array $payload): void
    {
        foreach (($payload['changes'] ?? []) as $field => $value) {
            if (property_exists($state, $field)) {
                $state->$field = $value;
            }
        }
        $state->system_notes[] = "Koreksi manual: " . ($payload['reason'] ?? '-');
    }

    /**
     * Persist state to database
     * 
     * Fix #1: Respect payroll lock - don't overwrite locked summaries
     */
    private function persistState(object $state): AttendanceSummary
    {
        // Check for existing locked summary
        $existing = AttendanceSummary::where('employee_id', $state->employee_id)
            ->where('date', $state->date->format('Y-m-d'))
            ->first();

        if ($existing && $existing->is_locked_for_payroll) {
            // Don't modify locked summary - log and return existing
            \Log::warning('Rebuild skipped: summary is locked for payroll', [
                'employee_id' => $state->employee_id,
                'date' => $state->date->format('Y-m-d'),
            ]);
            return $existing;
        }

        return AttendanceSummary::updateOrCreate(
            [
                'employee_id' => $state->employee_id,
                'date' => $state->date->format('Y-m-d'), // FIX #5: Consistent Y-m-d format
            ],
            [
                // Convert enum to string if needed
                'status' => $state->status instanceof AttendanceStatus ? $state->status->value : $state->status,
                'clock_in_at' => $state->clock_in_at ? Carbon::parse($state->date->format('Y-m-d') . ' ' . $state->clock_in_at) : null,
                'clock_out_at' => $this->buildClockOutDatetime($state),
                'total_work_minutes' => $state->total_work_minutes,
                'late_minutes' => $state->late_minutes,
                'early_leave_minutes' => $state->early_leave_minutes,
                'overtime_minutes' => $state->overtime_minutes,
                'approved_overtime_minutes' => $state->approved_overtime_minutes,
                'overtime_request_id' => $state->overtime_request_id,
                'leave_request_id' => $state->leave_request_id,
                'shift_id' => $state->shift_id,
                'system_notes' => implode('. ', $state->system_notes),
            ]
        );
    }

    /**
     * Rebuild without saving (for verification)
     */
    private function rebuildDayWithoutSave(int $employeeId, Carbon $date): object
    {
        $events = AttendanceEvent::forEmployee($employeeId)
            ->forDate($date)
            ->affectingSummary()
            ->chronological()
            ->get();

        $state = $this->createFreshState($employeeId, $date);

        foreach ($events as $event) {
            $this->applyEvent($state, $event);
        }

        return $state;
    }

    /**
     * Compare existing summary with rebuilt state
     * FIX: Normalize enum values to strings before comparison
     */
    private function compareStates(AttendanceSummary $existing, object $rebuilt): array
    {
        $diffs = [];
        $fieldsToCompare = [
            'status',
            'total_work_minutes',
            'late_minutes',
            'approved_overtime_minutes',
        ];

        foreach ($fieldsToCompare as $field) {
            $existingValue = $existing->$field;
            $rebuiltValue = $rebuilt->$field;

            // FIX: Normalize enum to string for comparison
            if ($existingValue instanceof AttendanceStatus) {
                $existingValue = $existingValue->value;
            }
            if ($rebuiltValue instanceof AttendanceStatus) {
                $rebuiltValue = $rebuiltValue->value;
            }

            if ($existingValue != $rebuiltValue) {
                $diffs[$field] = [
                    'existing' => $existingValue,
                    'rebuilt' => $rebuiltValue,
                ];
            }
        }

        return $diffs;
    }
}
