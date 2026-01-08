<?php

namespace App\Console\Commands;

use App\Services\Attendance\AttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PrepareAttendanceRows extends Command
{
    protected $signature = 'attendance:prepare-rows {date?}';
    protected $description = 'Pre-create attendance summary rows for a date (default: tomorrow)';

    public function handle(AttendanceSummaryService $service): int
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))
            : Carbon::tomorrow();

        $this->info("Pre-creating attendance rows for: {$date->format('Y-m-d')}");

        $count = $service->ensureRowsForDate($date);

        $this->info("Created {$count} attendance summary rows.");

        return Command::SUCCESS;
    }
}
