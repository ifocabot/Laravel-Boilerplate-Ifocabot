<?php

namespace App\Observers;

use App\Models\OvertimeRequest;
use App\Models\AttendanceEvent;
use App\Models\AttendanceSummary;
use App\Jobs\RecalculateAttendanceJob;
use App\Enums\AttendanceEventType;
use App\Services\Attendance\AttendanceSummaryService;

class OvertimeRequestObserver
{
    public function __construct(
        protected AttendanceSummaryService $summaryService
    ) {
    }

    /**
     * Handle the OvertimeRequest "updated" event.
     * Triggered when overtime is approved/rejected.
     */
    public function updated(OvertimeRequest $overtime): void
    {
        // Only handle when status changed
        if (!$overtime->isDirty('status')) {
            return;
        }

        $newStatus = $overtime->status;
        $oldStatus = $overtime->getOriginal('status');

        // Emit event for audit trail
        $this->emitOvertimeEvent($overtime, $oldStatus, $newStatus);

        if ($newStatus === 'approved') {
            $this->handleApproval($overtime);
        } elseif (in_array($newStatus, ['rejected', 'cancelled'])) {
            $this->handleRejection($overtime);
        }
    }

    /**
     * Emit overtime event for audit trail
     */
    protected function emitOvertimeEvent(OvertimeRequest $overtime, ?string $oldStatus, string $newStatus): void
    {
        $eventType = match ($newStatus) {
            'approved' => AttendanceEventType::OVERTIME_APPROVED,
            'rejected' => AttendanceEventType::OVERTIME_REJECTED,
            'cancelled' => AttendanceEventType::OVERTIME_CANCELLED,
            default => null,
        };

        if (!$eventType) {
            return;
        }

        AttendanceEvent::create([
            'employee_id' => $overtime->employee_id,
            'date' => $overtime->date,
            'event_type' => $eventType,
            'payload' => [
                'requested_minutes' => $overtime->duration_minutes,
                'approved_minutes' => $newStatus === 'approved' ? $overtime->approved_duration_minutes : 0,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $overtime->reason,
                'approved_by' => $overtime->approved_by,
                'rejected_reason' => $overtime->reject_reason ?? null,
            ],
            'source_type' => OvertimeRequest::class,
            'source_id' => $overtime->id,
            'created_by' => $overtime->approved_by ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    /**
     * Handle overtime approval
     */
    protected function handleApproval(OvertimeRequest $overtime): void
    {
        $summary = AttendanceSummary::where('employee_id', $overtime->employee_id)
            ->where('date', $overtime->date)
            ->first();

        // Check if period is locked - create adjustment instead
        if ($summary && $summary->is_locked_for_payroll) {
            $adjustment = $this->summaryService->handleLateOvertimeApproval(
                $overtime,
                auth()->id() ?? 1
            );

            \Log::info('Created adjustment for late overtime approval', [
                'overtime_request_id' => $overtime->id,
                'adjustment_id' => $adjustment?->id,
            ]);

            return;
        }

        // Dispatch async recalculation
        dispatch(new RecalculateAttendanceJob(
            $overtime->employee_id,
            $overtime->date->format('Y-m-d'),
            'overtime_approved'
        ));
    }

    /**
     * Handle overtime rejection/cancellation
     */
    protected function handleRejection(OvertimeRequest $overtime): void
    {
        // Dispatch async recalculation
        dispatch(new RecalculateAttendanceJob(
            $overtime->employee_id,
            $overtime->date->format('Y-m-d'),
            'overtime_rejected'
        ));
    }
}

