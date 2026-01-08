<?php

namespace App\Observers;

use App\Models\EmployeeSchedule;
use App\Services\Attendance\AttendanceSummaryService;

class EmployeeScheduleObserver
{
    public function __construct(
        protected AttendanceSummaryService $summaryService
    ) {
    }

    /**
     * Handle the EmployeeSchedule "created" event.
     */
    public function created(EmployeeSchedule $schedule): void
    {
        $this->recalculate($schedule);
    }

    /**
     * Handle the EmployeeSchedule "updated" event.
     */
    public function updated(EmployeeSchedule $schedule): void
    {
        // Only recalculate if relevant fields changed
        if ($schedule->isDirty(['shift_id', 'is_day_off', 'date'])) {
            $this->recalculate($schedule);
        }
    }

    /**
     * Handle the EmployeeSchedule "deleted" event.
     */
    public function deleted(EmployeeSchedule $schedule): void
    {
        $this->recalculate($schedule);
    }

    /**
     * Recalculate attendance summary for schedule date
     */
    protected function recalculate(EmployeeSchedule $schedule): void
    {
        try {
            $this->summaryService->recalculate(
                $schedule->employee_id,
                $schedule->date
            );
        } catch (\Exception $e) {
            \Log::error('Failed to recalculate attendance for schedule', [
                'schedule_id' => $schedule->id,
                'employee_id' => $schedule->employee_id,
                'date' => $schedule->date,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
