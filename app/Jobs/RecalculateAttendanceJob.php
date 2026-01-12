<?php

namespace App\Jobs;

use App\Services\Attendance\AttendanceRebuildService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Async Attendance Recalculation Job
 * 
 * Dispatched by observers instead of sync recalc.
 * Makes bulk operations efficient and prevents race conditions.
 * 
 * ShouldBeUniqueUntilProcessing: prevents duplicate jobs from queueing
 * for the same employee+date until the current one starts processing.
 */
class RecalculateAttendanceJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public int $employeeId,
        public string $date,
        public ?string $trigger = null
    ) {
        // Uses default queue - no need for separate attendance queue
    }

    public function handle(AttendanceRebuildService $rebuildService): void
    {
        $date = Carbon::parse($this->date);

        Log::info('Async recalculating attendance', [
            'employee_id' => $this->employeeId,
            'date' => $this->date,
            'trigger' => $this->trigger,
        ]);

        try {
            $rebuildService->rebuildDay($this->employeeId, $date);
        } catch (\Exception $e) {
            Log::error('Async recalculation failed', [
                'employee_id' => $this->employeeId,
                'date' => $this->date,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Unique job ID to prevent duplicate processing
     */
    public function uniqueId(): string
    {
        return "recalc:{$this->employeeId}:{$this->date}";
    }
}
