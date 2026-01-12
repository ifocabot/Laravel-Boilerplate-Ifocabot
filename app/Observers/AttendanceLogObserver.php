<?php

namespace App\Observers;

use App\Models\AttendanceLog;
use App\Models\AttendanceEvent;
use App\Jobs\RecalculateAttendanceJob;

class AttendanceLogObserver
{
    /**
     * Get current user ID or fallback for system/cron contexts.
     * Returns null if no user (will be stored as system action).
     */
    protected function getCreatedBy(): ?int
    {
        return auth()->id(); // Null is OK - means system/cron/fingerprint created
    }
    /**
     * Handle the AttendanceLog "created" event.
     * This handles CLOCK IN.
     */
    public function created(AttendanceLog $log): void
    {
        // Record clock in event for audit trail
        // NOTE: is_late/late_minutes NOT sent - RebuildService calculates from shift
        if ($log->clock_in_time) {
            AttendanceEvent::recordClockIn(
                $log->employee_id,
                $log->date,
                [
                    'time' => $log->clock_in_time?->format('H:i:s'),
                    'latitude' => $log->clock_in_lat,
                    'longitude' => $log->clock_in_long,
                    'device' => $log->clock_in_device,
                    'photo' => $log->clock_in_photo,
                ],
                $log->id
            );
        }

        $this->dispatchRecalculation($log, 'clock_in');
    }

    /**
     * Handle the AttendanceLog "updated" event.
     * This handles CLOCK OUT or corrections.
     */
    public function updated(AttendanceLog $log): void
    {
        // Record clock out event for audit trail
        // NOTE: is_early_out NOT sent - RebuildService calculates from shift
        if ($log->wasChanged('clock_out_time') && $log->clock_out_time) {
            AttendanceEvent::recordClockOut(
                $log->employee_id,
                $log->date,
                [
                    'time' => $log->clock_out_time?->format('H:i:s'),
                    'latitude' => $log->clock_out_lat,
                    'longitude' => $log->clock_out_long,
                    'device' => $log->clock_out_device,
                    'photo' => $log->clock_out_photo,
                    'work_duration_minutes' => $log->work_duration_minutes,
                ],
                $log->id
            );
        }

        // Record clock in correction if changed
        if ($log->wasChanged('clock_in_time') && !$log->wasRecentlyCreated) {
            AttendanceEvent::create([
                'employee_id' => $log->employee_id,
                'date' => $log->date,
                'event_type' => \App\Enums\AttendanceEventType::CLOCK_IN_CORRECTED,
                'payload' => [
                    'old_time' => $log->getOriginal('clock_in_time'),
                    'new_time' => $log->clock_in_time?->format('H:i:s'),
                ],
                'source_type' => AttendanceLog::class,
                'source_id' => $log->id,
                'created_by' => $this->getCreatedBy(),
                'created_at' => now(),
            ]);
        }

        $this->dispatchRecalculation($log, 'clock_out');
    }

    /**
     * Dispatch async recalculation job
     * In testing, runs synchronously
     */
    protected function dispatchRecalculation(AttendanceLog $log, string $trigger): void
    {
        $job = new RecalculateAttendanceJob(
            $log->employee_id,
            $log->date->format('Y-m-d'),
            $trigger
        );

        // In testing or when queue is sync, this runs immediately
        // In production with queue worker, this is async
        dispatch($job);
    }
}

