<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceSummary;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class GenerateAttendanceSummaries extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:generate-summaries 
                            {--date= : Specific date to generate (Y-m-d format)}
                            {--from= : Start date for range (Y-m-d format)}
                            {--to= : End date for range (Y-m-d format)}
                            {--yesterday : Generate for yesterday}
                            {--today : Generate for today}
                            {--month= : Generate for specific month (Y-m format)}
                            {--force : Force regenerate existing summaries}';

    /**
     * The console command description.
     */
    protected $description = 'Generate attendance summaries from attendance logs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Attendance Summary Generation...');
        $this->newLine();

        // Determine date range
        [$startDate, $endDate] = $this->determineDateRange();

        if (!$startDate || !$endDate) {
            $this->error('❌ Invalid date range specified.');
            return Command::FAILURE;
        }

        $this->info("📅 Generating summaries from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->newLine();

        // Get attendance logs in date range
        $logs = AttendanceLog::whereBetween('date', [$startDate, $endDate])
            ->with('shift')
            ->orderBy('date')
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('⚠️  No attendance logs found in this date range.');
            return Command::SUCCESS;
        }

        $this->info("📊 Found {$logs->count()} attendance logs to process");

        // Create progress bar
        $progressBar = $this->output->createProgressBar($logs->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            // Check if summary already exists
            $existing = AttendanceSummary::where('employee_id', $log->employee_id)
                ->where('date', $log->date)
                ->first();

            if ($existing && !$this->option('force')) {
                $skipped++;
                $progressBar->advance();
                continue;
            }

            if ($existing) {
                // Update existing
                $existing->calculateFromLog($log);
                $updated++;
            } else {
                // Create new
                AttendanceSummary::generateFromLog($log);
                $created++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Show results
        $this->info('✅ Summary generation completed!');
        $this->newLine();

        $this->table(
            ['Action', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Total Processed', $logs->count()],
            ]
        );

        // Show sample summaries
        if ($created > 0 || $updated > 0) {
            $this->newLine();
            $this->info('📄 Sample Generated Summaries:');

            AttendanceSummary::with('employee')
                ->whereBetween('date', [$startDate, $endDate])
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->each(function ($summary) {
                    $this->line(
                        "   • {$summary->employee->full_name} - {$summary->formatted_date} | " .
                        "{$summary->status_label} | {$summary->formatted_total_work}"
                    );
                });
        }

        return Command::SUCCESS;
    }

    /**
     * Determine date range from options
     */
    private function determineDateRange(): array
    {
        // Specific date
        if ($date = $this->option('date')) {
            try {
                $carbon = Carbon::parse($date);
                return [$carbon, $carbon->copy()];
            } catch (\Exception $e) {
                $this->error("Invalid date format: {$date}");
                return [null, null];
            }
        }

        // Date range
        if ($from = $this->option('from')) {
            $to = $this->option('to') ?? $from;

            try {
                $startDate = Carbon::parse($from);
                $endDate = Carbon::parse($to);
                return [$startDate, $endDate];
            } catch (\Exception $e) {
                $this->error("Invalid date range format");
                return [null, null];
            }
        }

        // Yesterday
        if ($this->option('yesterday')) {
            $yesterday = Carbon::yesterday();
            return [$yesterday, $yesterday->copy()];
        }

        // Today
        if ($this->option('today')) {
            $today = Carbon::today();
            return [$today, $today->copy()];
        }

        // Month
        if ($month = $this->option('month')) {
            try {
                $date = Carbon::parse($month . '-01');
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
                return [$startDate, $endDate];
            } catch (\Exception $e) {
                $this->error("Invalid month format: {$month}");
                return [null, null];
            }
        }

        // Default: yesterday
        $yesterday = Carbon::yesterday();
        return [$yesterday, $yesterday->copy()];
    }
}