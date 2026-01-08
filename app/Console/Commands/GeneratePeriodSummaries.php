<?php

namespace App\Console\Commands;

use App\Models\PayrollPeriod;
use App\Services\Attendance\AttendanceSummaryService;
use Illuminate\Console\Command;

class GeneratePeriodSummaries extends Command
{
    protected $signature = 'payroll:generate-summaries {period_id} {--lock : Also lock the period}';
    protected $description = 'Generate attendance period summaries for a payroll period';

    public function handle(AttendanceSummaryService $service): int
    {
        $periodId = $this->argument('period_id');
        $period = PayrollPeriod::find($periodId);

        if (!$period) {
            $this->error("Payroll period #{$periodId} not found.");
            return Command::FAILURE;
        }

        $this->info("Generating summaries for: {$period->period_name}");
        $this->info("Period: {$period->start_date->format('d M Y')} - {$period->end_date->format('d M Y')}");

        $count = $service->generatePeriodSummaries($period, auth()->id());

        $this->info("Generated summaries for {$count} employees.");

        if ($this->option('lock')) {
            $this->info("Locking period...");
            $result = $service->lockPeriod($period, auth()->id() ?? 1);
            $this->info("Locked {$result['daily_locked']} daily summaries.");
            $this->info("Locked {$result['period_summaries_locked']} period summaries.");
        }

        return Command::SUCCESS;
    }
}
