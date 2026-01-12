<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\AttendancePeriodSummary;
use App\Services\Payroll\PayrollCalculator;
use Carbon\CarbonPeriod;

/**
 * Seeder 08: Payroll Run
 * 
 * Generates payroll slips for test periods using PayrollCalculator.
 */
class Seed_08_PayrollRunSeeder extends Seeder
{
    public function run(): void
    {
        $calculator = app(PayrollCalculator::class);

        $janPeriod = PayrollPeriod::where('period_code', '2026-01')->first();

        if (!$janPeriod) {
            $this->command->error('Period 2026-01 not found!');
            return;
        }

        $employees = Employee::whereIn('nik', [
            'EMP-A-001',
            'EMP-B-002',
            'EMP-C-003',
            'EMP-D-004',
            'EMP-E-005',
            'EMP-F-006',
            'EMP-G-007',
        ])->with(['activePayrollComponents.component', 'sensitiveData', 'currentCareer'])->get();

        $this->command->info("Generating payroll slips for {$janPeriod->period_name}...");

        $slipCount = 0;
        foreach ($employees as $employee) {
            // Skip if joined after period end
            if ($employee->join_date && $employee->join_date > $janPeriod->end_date) {
                $this->command->info("  - {$employee->nik}: Skipped (joined after period)");
                continue;
            }

            // Get or create attendance summary
            $summary = AttendancePeriodSummary::where('employee_id', $employee->id)
                ->where('payroll_period_id', $janPeriod->id)
                ->first();

            if (!$summary) {
                $summary = $this->createBasicSummary($employee, $janPeriod);
            }

            try {
                $slip = $calculator->calculateFromPeriodSummary($janPeriod, $employee, $summary);
                $slipCount++;

                $this->command->info(sprintf(
                    "  - %s: Gross=%s, Deduct=%s, Net=%s",
                    $employee->nik,
                    number_format((float) $slip->gross_salary, 0),
                    number_format((float) $slip->total_deductions, 0),
                    number_format((float) $slip->net_salary, 0)
                ));

            } catch (\Exception $e) {
                $this->command->error("  - {$employee->nik}: Error - {$e->getMessage()}");
            }
        }

        // Update period totals
        $janPeriod->calculateTotals();

        $this->command->info("✅ Seed_08: Generated {$slipCount} payroll slips");
    }

    private function createBasicSummary(Employee $employee, PayrollPeriod $period): AttendancePeriodSummary
    {
        // Calculate work days in period
        $startDate = $employee->join_date && $employee->join_date > $period->start_date
            ? $employee->join_date
            : $period->start_date;

        $workDays = collect(CarbonPeriod::create($startDate, $period->end_date))
            ->filter(fn($d) => !$d->isWeekend())
            ->count();

        $scheduledDays = collect(CarbonPeriod::create($period->start_date, $period->end_date))
            ->filter(fn($d) => !$d->isWeekend())
            ->count();

        return AttendancePeriodSummary::create([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'scheduled_work_days' => $scheduledDays,
            'present_days' => $workDays,
            'late_days' => 0,
            'alpha_days' => 0,
            'leave_days' => 0,
            'sick_days' => 0,
            'permission_days' => 0,
            'offday_days' => 0,
            'holiday_days' => 0,
            'wfh_days' => 0,
            'business_trip_days' => 0,
            'total_worked_minutes' => $workDays * 8 * 60,
            'total_late_minutes' => 0,
            'total_early_leave_minutes' => 0,
            'total_detected_overtime_minutes' => 0,
            'total_approved_overtime_minutes' => 0,
            'is_locked' => false,
        ]);
    }
}
