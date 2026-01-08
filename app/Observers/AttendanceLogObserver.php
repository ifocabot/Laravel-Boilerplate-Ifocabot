<?php

namespace App\Observers;

use App\Models\AttendanceLog;
use App\Services\Attendance\AttendanceSummaryService;

class AttendanceLogObserver
{
    public function __construct(
        protected AttendanceSummaryService $summaryService
    ) {
    }

    /**
     * Handle the AttendanceLog "created" event.
     */
    public function created(AttendanceLog $log): void
    {
        $this->recalculate($log);
    }

    /**
     * Handle the AttendanceLog "updated" event.
     */
    public function updated(AttendanceLog $log): void
    {
        $this->recalculate($log);
    }

    /**
     * Recalculate attendance summary
     */
    protected function recalculate(AttendanceLog $log): void
    {
        try {
            $this->summaryService->recalculate(
                $log->employee_id,
                $log->date
            );
        } catch (\Exception $e) {
            \Log::error('Failed to recalculate attendance summary', [
                'attendance_log_id' => $log->id,
                'employee_id' => $log->employee_id,
                'date' => $log->date,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
