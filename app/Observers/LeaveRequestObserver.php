<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use App\Models\AttendanceEvent;
use App\Jobs\RecalculateAttendanceJob;
use App\Enums\AttendanceEventType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "updated" event.
     * Triggered when leave is approved/rejected/cancelled.
     */
    public function updated(LeaveRequest $leave): void
    {
        // Only process if status changed
        if (!$leave->isDirty('status')) {
            return;
        }

        $newStatus = $leave->status;
        $oldStatus = $leave->getOriginal('status');

        // Emit events and recalculate for each day in leave range
        $this->processLeaveStatusChange($leave, $oldStatus, $newStatus);
    }

    /**
     * Process leave status change - emit events and recalculate
     */
    protected function processLeaveStatusChange(LeaveRequest $leave, ?string $oldStatus, string $newStatus): void
    {
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Emit appropriate event based on status change
            $this->emitLeaveEvent($leave, $date, $oldStatus, $newStatus);

            // Dispatch async recalculation
            dispatch(new RecalculateAttendanceJob(
                $leave->employee_id,
                $date->format('Y-m-d'),
                'leave_' . $newStatus
            ));
        }
    }

    /**
     * Emit leave event for audit trail
     */
    protected function emitLeaveEvent(LeaveRequest $leave, Carbon $date, ?string $oldStatus, string $newStatus): void
    {
        $eventType = match ($newStatus) {
            'approved' => AttendanceEventType::LEAVE_APPROVED,
            'rejected' => AttendanceEventType::LEAVE_CANCELLED,
            'cancelled' => AttendanceEventType::LEAVE_CANCELLED,
            default => null,
        };

        if (!$eventType) {
            return;
        }

        AttendanceEvent::create([
            'employee_id' => $leave->employee_id,
            'date' => $date,
            'event_type' => $eventType,
            'payload' => [
                'leave_type' => $leave->leaveType?->name ?? 'Cuti',
                'leave_type_code' => $leave->leaveType?->code,
                'status_override' => $newStatus === 'approved' ? $this->mapLeaveTypeToStatus($leave) : null,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'reason' => $leave->reason,
            ],
            'source_type' => LeaveRequest::class,
            'source_id' => $leave->id,
            'created_by' => $leave->approved_by ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    /**
     * Map leave type to attendance status
     */
    protected function mapLeaveTypeToStatus(LeaveRequest $leave): string
    {
        $code = strtolower($leave->leaveType?->code ?? '');

        return match ($code) {
            'sick', 'sakit' => 'sick',
            'permission', 'izin' => 'permission',
            'wfh' => 'wfh',
            'business_trip', 'dinas' => 'business_trip',
            default => 'leave',
        };
    }
}

