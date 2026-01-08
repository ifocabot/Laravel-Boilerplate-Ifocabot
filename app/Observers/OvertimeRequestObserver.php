<?php

namespace App\Observers;

use App\Models\OvertimeRequest;
use App\Models\PayrollAdjustment;
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
        // Only handle when status changed to approved
        if (!$overtime->isDirty('status')) {
            return;
        }

        if ($overtime->status === 'approved') {
            $this->handleApproval($overtime);
        } elseif (in_array($overtime->status, ['rejected', 'cancelled'])) {
            $this->handleRejection($overtime);
        }
    }

    /**
     * Handle overtime approval
     */
    protected function handleApproval(OvertimeRequest $overtime): void
    {
        try {
            $summary = \App\Models\AttendanceSummary::where('employee_id', $overtime->employee_id)
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

            // Normal recalculation
            $this->summaryService->recalculate(
                $overtime->employee_id,
                $overtime->date
            );

        } catch (\Exception $e) {
            \Log::error('Failed to handle overtime approval', [
                'overtime_request_id' => $overtime->id,
                'employee_id' => $overtime->employee_id,
                'date' => $overtime->date,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle overtime rejection/cancellation
     */
    protected function handleRejection(OvertimeRequest $overtime): void
    {
        try {
            $this->summaryService->recalculate(
                $overtime->employee_id,
                $overtime->date
            );
        } catch (\Exception $e) {
            \Log::error('Failed to handle overtime rejection', [
                'overtime_request_id' => $overtime->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
