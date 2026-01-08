<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use App\Services\Attendance\AttendanceSummaryService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestObserver
{
    public function __construct(
        protected AttendanceSummaryService $summaryService
    ) {
    }

    /**
     * Handle the LeaveRequest "updated" event.
     * Triggered when leave is approved/rejected/cancelled.
     */
    public function updated(LeaveRequest $leave): void
    {
        // Only recalculate if status changed
        if (!$leave->isDirty('status')) {
            return;
        }

        $this->recalculateRange($leave);
    }

    /**
     * Recalculate attendance summary for each day in leave range
     */
    protected function recalculateRange(LeaveRequest $leave): void
    {
        $startDate = Carbon::parse($leave->start_date);
        $endDate = Carbon::parse($leave->end_date);
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            try {
                $this->summaryService->recalculate(
                    $leave->employee_id,
                    $date
                );
            } catch (\Exception $e) {
                \Log::error('Failed to recalculate attendance for leave', [
                    'leave_request_id' => $leave->id,
                    'employee_id' => $leave->employee_id,
                    'date' => $date->toDateString(),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
