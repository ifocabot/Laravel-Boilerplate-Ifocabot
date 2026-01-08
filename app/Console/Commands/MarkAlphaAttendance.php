<?php

namespace App\Console\Commands;

use App\Services\Attendance\AttendanceSummaryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAlphaAttendance extends Command
{
    protected $signature = 'attendance:mark-alpha {date?}';
    protected $description = 'Mark absent employees as ALPHA for a specific date (default: today)';

    public function handle(AttendanceSummaryService $service): int
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))
            : Carbon::today();

        $this->info("Marking absent as ALPHA for: {$date->format('Y-m-d')}");

        $count = $service->markAlphaForDate($date);

        $this->info("Marked {$count} employees as ALPHA.");

        return Command::SUCCESS;
    }
}
