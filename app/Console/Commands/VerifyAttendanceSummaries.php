<?php

namespace App\Console\Commands;

use App\Services\Attendance\AttendanceRebuildService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class VerifyAttendanceSummaries extends Command
{
    protected $signature = 'attendance:verify 
        {--employee= : Employee ID to verify (optional, all if not specified)}
        {--start= : Start date (Y-m-d)}
        {--end= : End date (Y-m-d)}
        {--fix : Actually rebuild mismatched summaries}';

    protected $description = 'Verify attendance summaries match what would be rebuilt from events (for audit)';

    public function handle(AttendanceRebuildService $rebuildService): int
    {
        $startDate = Carbon::parse($this->option('start') ?? now()->startOfMonth()->format('Y-m-d'));
        $endDate = Carbon::parse($this->option('end') ?? now()->format('Y-m-d'));
        $employeeId = $this->option('employee');
        $shouldFix = $this->option('fix');

        $this->info("Verifying attendance summaries from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        if ($employeeId) {
            $result = $rebuildService->verify((int) $employeeId, $startDate, $endDate);
            $this->displayResult($result, $shouldFix, $rebuildService);
        } else {
            $employees = \App\Models\Employee::where('status', 'active')->get();
            $totalDiscrepancies = 0;

            $bar = $this->output->createProgressBar($employees->count());
            $bar->start();

            foreach ($employees as $employee) {
                $result = $rebuildService->verify($employee->id, $startDate, $endDate);

                if ($result['discrepancy_count'] > 0) {
                    $totalDiscrepancies += $result['discrepancy_count'];
                    $bar->clear();
                    $this->displayResult($result, $shouldFix, $rebuildService, $employee);
                    $bar->display();
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            if ($totalDiscrepancies === 0) {
                $this->info("✅ All {$employees->count()} employees verified. 0 discrepancies found.");
            } else {
                $this->warn("⚠️ Found {$totalDiscrepancies} discrepancies across {$employees->count()} employees.");
            }
        }

        return 0;
    }

    private function displayResult(
        array $result,
        bool $shouldFix,
        AttendanceRebuildService $rebuildService,
        ?\App\Models\Employee $employee = null
    ): void {
        if ($result['discrepancy_count'] === 0) {
            $this->info("✅ {$result['total_days']} days verified. No discrepancies.");
            return;
        }

        $name = $employee ? $employee->full_name : "Employee #{$result['employee_id']}";
        $this->warn("⚠️ {$name}: {$result['discrepancy_count']} discrepancies found");

        foreach ($result['discrepancies'] as $disc) {
            $this->line("  📅 {$disc['date']}:");
            foreach ($disc['differences'] as $field => $diff) {
                $this->line("     {$field}: {$diff['existing']} → {$diff['rebuilt']}");
            }
        }

        if ($shouldFix) {
            $this->info("  🔧 Rebuilding...");
            foreach ($result['discrepancies'] as $disc) {
                $rebuildService->rebuildDay(
                    $result['employee_id'],
                    Carbon::parse($disc['date'])
                );
            }
            $this->info("  ✅ Fixed {$result['discrepancy_count']} days");
        }
    }
}
