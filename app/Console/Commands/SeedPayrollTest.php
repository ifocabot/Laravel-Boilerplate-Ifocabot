<?php

namespace App\Console\Commands;

use App\Models\AttendanceLog;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;
use App\Models\EmployeeSchedule;
use App\Models\PayrollComponent;
use App\Models\PayrollPeriod;
use App\Models\Shift;
use App\Models\OvertimeRequest;
use App\Services\Attendance\AttendanceSummaryService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedPayrollTest extends Command
{
    protected $signature = 'payroll:seed-test 
                            {--year= : Year (default: current)}
                            {--month= : Month (default: current)}
                            {--employees=10 : Number of employees to seed}
                            {--present=18 : Number of present days}
                            {--late=2 : Number of late days}
                            {--alpha=1 : Number of alpha/absent days}
                            {--leave=1 : Number of leave days}
                            {--sick=1 : Number of sick days}
                            {--overtime=5 : Number of days with overtime}
                            {--overtime-minutes=120 : Average overtime minutes per day}
                            {--clean : Clear existing data for this period first}
                            {--skip-recalculate : Skip automatic recalculation (faster)}';

    protected $description = 'Seed complete payroll flow: Components → Schedule → Logs → Summary → Overtime';

    protected AttendanceSummaryService $summaryService;

    public function __construct(AttendanceSummaryService $summaryService)
    {
        parent::__construct();
        $this->summaryService = $summaryService;
    }

    public function handle(): int
    {
        $year = $this->option('year') ?: now()->year;
        $month = $this->option('month') ?: now()->month;

        $this->info('=== Payroll Test Seeder (Full Flow) ===');
        $this->info("Period: {$month}/{$year}");
        $this->newLine();

        $this->table(['Parameter', 'Value'], [
            ['Employees', $this->option('employees')],
            ['Present Days', $this->option('present')],
            ['Late Days', $this->option('late')],
            ['Alpha Days', $this->option('alpha')],
            ['Leave Days', $this->option('leave')],
            ['Sick Days', $this->option('sick')],
            ['Overtime Days', $this->option('overtime')],
            ['Avg OT Minutes', $this->option('overtime-minutes')],
        ]);

        if (!$this->confirm('Proceed with seeding?')) {
            return Command::SUCCESS;
        }

        // Get or create period
        $period = $this->getOrCreatePeriod($year, $month);
        $this->info("Period: {$period->period_name}");
        $this->info("Range: {$period->start_date->format('d M Y')} - {$period->end_date->format('d M Y')}");

        // Clean if requested
        if ($this->option('clean')) {
            $this->warn('Cleaning existing data...');
            $this->cleanData($period);
        }

        // Get employees
        $employees = Employee::where('status', 'active')
            ->take((int) $this->option('employees'))
            ->get();

        if ($employees->isEmpty()) {
            $this->error('No active employees found!');
            return Command::FAILURE;
        }

        // Get shift
        $shift = Shift::first();
        if (!$shift) {
            $this->error('No shift found! Create shifts first.');
            return Command::FAILURE;
        }

        $this->info("Seeding for {$employees->count()} employees...");
        $this->newLine();

        $this->info('Step 1: Assigning Payroll Components...');
        $bar = $this->output->createProgressBar($employees->count());
        $bar->start();

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $this->assignPayrollComponents($employee);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            $this->info('Step 2: Creating Schedules...');
            $bar = $this->output->createProgressBar($employees->count());
            $bar->start();

            foreach ($employees as $employee) {
                $this->createSchedules($employee, $period, $shift);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            $this->info('Step 3: Creating Attendance Logs...');
            $bar = $this->output->createProgressBar($employees->count());
            $bar->start();

            foreach ($employees as $employee) {
                $this->createAttendanceLogs($employee, $period, $shift);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            $this->info('Step 4: Creating Overtime Requests...');
            $bar = $this->output->createProgressBar($employees->count());
            $bar->start();

            foreach ($employees as $employee) {
                $this->createOvertimeRequests($employee, $period);
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();

            if (!$this->option('skip-recalculate')) {
                $this->info('Step 5: Recalculating Summaries...');
                $bar = $this->output->createProgressBar($employees->count());
                $bar->start();

                foreach ($employees as $employee) {
                    $this->recalculateSummaries($employee, $period);
                    $bar->advance();
                }
                $bar->finish();
                $this->newLine();

                // Step 6: Generate Period Summaries
                $this->info('Step 6: Generating Period Summaries...');
                $bar = $this->output->createProgressBar($employees->count());
                $bar->start();

                foreach ($employees as $employee) {
                    \App\Models\AttendancePeriodSummary::generateFromDailySummaries(
                        $employee->id,
                        $period->id,
                        $period->start_date,
                        $period->end_date,
                        1 // system user
                    );
                    $bar->advance();
                }
                $bar->finish();
                $this->newLine();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine();
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Seeding completed!');
        $this->newLine();

        // Show summary
        $this->showStats($period);

        $this->newLine();
        $this->info('Next steps:');
        $this->info("1. Go to /hris/payroll/periods/{$period->id}");
        $this->info('2. Click "Generate Slips"');

        return Command::SUCCESS;
    }

    protected function getOrCreatePeriod(int $year, int $month): PayrollPeriod
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        return PayrollPeriod::firstOrCreate(
            ['year' => $year, 'month' => $month],
            [
                'period_code' => "PAY-{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT),
                'period_name' => "Gaji " . $startDate->translatedFormat('F Y'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_date' => $endDate->copy()->addDays(5),
                'status' => 'draft',
            ]
        );
    }

    protected function cleanData(PayrollPeriod $period): void
    {
        // Delete in correct order (foreign keys)
        // 1. Delete payroll slips first
        \App\Models\PayrollSlip::where('payroll_period_id', $period->id)->delete();

        // 2. Delete period summaries
        \App\Models\AttendancePeriodSummary::where('payroll_period_id', $period->id)->delete();

        // 3. Delete attendance data
        AttendanceSummary::where('payroll_period_id', $period->id)->delete();
        // Also delete by date range in case period_id wasn't set
        AttendanceSummary::whereBetween('date', [$period->start_date, $period->end_date])->delete();
        OvertimeRequest::whereBetween('date', [$period->start_date, $period->end_date])->delete();
        AttendanceLog::whereBetween('date', [$period->start_date, $period->end_date])->delete();
        EmployeeSchedule::whereBetween('date', [$period->start_date, $period->end_date])->delete();

        // 4. Reset period status to draft
        $period->update([
            'status' => 'draft',
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'employee_count' => 0,
            'attendance_locked' => false,
            'attendance_locked_at' => null,
            'attendance_locked_by' => null,
        ]);
    }

    /**
     * Step 1: Assign payroll components to employees
     */
    protected function assignPayrollComponents(Employee $employee): void
    {
        // Check if already has basic salary
        $hasBasicSalary = EmployeePayrollComponent::where('employee_id', $employee->id)
            ->whereHas('component', fn($q) => $q->where('code', 'BASIC_SALARY'))
            ->where('is_active', true)
            ->exists();

        if ($hasBasicSalary) {
            return; // Already has components assigned
        }

        // Get all active payroll components
        $components = PayrollComponent::where('is_active', true)->get()->keyBy('code');

        $effectiveFrom = now()->startOfMonth();

        // Random base salary between 4-8 million
        $baseSalary = rand(4, 8) * 1_000_000;

        // Define components to assign with amounts
        $componentAssignments = [
            'BASIC_SALARY' => $baseSalary,
            'TRANSPORT' => rand(3, 6) * 100_000,    // 300k - 600k
            'MEAL' => rand(3, 5) * 100_000,         // 300k - 500k
            'POSITION' => rand(0, 3) * 250_000,     // 0 - 750k
            'BPJS_KES' => $baseSalary * 0.01,       // 1%
            'BPJS_TK' => $baseSalary * 0.02,        // 2%
        ];

        foreach ($componentAssignments as $code => $amount) {
            $component = $components->get($code);

            if (!$component || $amount <= 0) {
                continue;
            }

            EmployeePayrollComponent::create([
                'employee_id' => $employee->id,
                'component_id' => $component->id,
                'amount' => $amount,
                'unit' => 'monthly',
                'effective_from' => $effectiveFrom,
                'effective_to' => null,
                'is_active' => true,
                'is_recurring' => true,
                'notes' => 'Generated by payroll test seeder',
            ]);
        }
    }

    /**
     * Step 2: Create schedules for all days in period
     */
    protected function createSchedules(Employee $employee, PayrollPeriod $period, Shift $shift): void
    {
        $periodRange = CarbonPeriod::create($period->start_date, $period->end_date);

        foreach ($periodRange as $date) {
            $isWeekend = $date->isWeekend();

            EmployeeSchedule::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $date->toDateString()],
                [
                    'shift_id' => $isWeekend ? null : $shift->id,
                    'is_day_off' => $isWeekend,
                    'is_holiday' => false,
                    'notes' => 'Generated by seeder',
                ]
            );
        }
    }

    /**
     * Step 2: Create attendance logs based on scenario distribution
     */
    protected function createAttendanceLogs(Employee $employee, PayrollPeriod $period, Shift $shift): void
    {
        $workingDays = $this->getWorkingDays($period->start_date, $period->end_date);

        // Build status pool
        $statusPool = array_merge(
            array_fill(0, (int) $this->option('present'), 'present'),
            array_fill(0, (int) $this->option('late'), 'late'),
            array_fill(0, (int) $this->option('alpha'), 'alpha'),
            array_fill(0, (int) $this->option('leave'), 'leave'),
            array_fill(0, (int) $this->option('sick'), 'sick'),
        );

        shuffle($statusPool);

        // Pad to match working days
        while (count($statusPool) < count($workingDays)) {
            $statusPool[] = 'present';
        }
        $statusPool = array_slice($statusPool, 0, count($workingDays));

        // Get schedule for this employee
        $schedules = EmployeeSchedule::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->pluck('id', 'date');

        foreach ($workingDays as $i => $date) {
            $status = $statusPool[$i];
            $scheduleId = $schedules[$date->toDateString()] ?? null;

            // Only create log for present/late (alpha = no log!)
            if (in_array($status, ['present', 'late'])) {
                $this->createLog($employee, $date, $shift, $status, $scheduleId);
            }
            // For leave/sick, we could create LeaveRequest but skip for simplicity
        }
    }

    protected function createLog(Employee $employee, Carbon $date, Shift $shift, string $status, ?int $scheduleId): void
    {
        $shiftStart = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);
        $shiftEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time);

        if ($shiftEnd->lt($shiftStart)) {
            $shiftEnd->addDay();
        }

        $clockIn = match ($status) {
            'present' => $shiftStart->copy()->subMinutes(rand(5, 15)),
            'late' => $shiftStart->copy()->addMinutes(rand(10, 60)),
            default => null,
        };

        $clockOut = $shiftEnd->copy()->addMinutes(rand(0, 30));

        $isLate = $status === 'late';
        $lateMinutes = $isLate ? $clockIn->diffInMinutes($shiftStart) : 0;
        $workMinutes = $clockIn ? $clockIn->diffInMinutes($clockOut) : 0;

        AttendanceLog::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date->toDateString()],
            [
                'schedule_id' => $scheduleId,
                'shift_id' => $shift->id,
                'clock_in_time' => $clockIn,
                'clock_out_time' => $clockOut,
                'is_late' => $isLate,
                'late_duration_minutes' => $lateMinutes,
                'work_duration_minutes' => $workMinutes,
                'clock_in_notes' => 'Seeder generated',
            ]
        );
    }

    /**
     * Step 3: Create overtime requests
     */
    protected function createOvertimeRequests(Employee $employee, PayrollPeriod $period): void
    {
        $overtimeDays = (int) $this->option('overtime');
        $avgMinutes = (int) $this->option('overtime-minutes');

        // Get logs with clock out (present or late)
        $logs = AttendanceLog::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->whereNotNull('clock_out_time')
            ->inRandomOrder()
            ->take($overtimeDays)
            ->get();

        foreach ($logs as $log) {
            $minutes = rand(max(60, $avgMinutes - 60), $avgMinutes + 60);

            $startTime = '17:00:00';
            $endTime = Carbon::parse($log->date->format('Y-m-d') . ' 17:00:00')
                ->addMinutes($minutes)
                ->format('H:i:s');

            OvertimeRequest::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $log->date->toDateString()],
                [
                    'start_at' => $startTime,
                    'end_at' => $endTime,
                    'duration_minutes' => $minutes,
                    'approved_duration_minutes' => $minutes,
                    'reason' => 'Test overtime - generated by seeder',
                    'status' => 'approved',
                    'approver_id' => 1,
                    'approved_at' => now(),
                ]
            );
        }
    }

    /**
     * Step 4: Recalculate attendance summaries using service
     */
    protected function recalculateSummaries(Employee $employee, PayrollPeriod $period): void
    {
        $periodRange = CarbonPeriod::create($period->start_date, $period->end_date);

        foreach ($periodRange as $date) {
            $this->summaryService->recalculate($employee->id, $date);
        }
    }

    protected function getWorkingDays($start, $end): array
    {
        return collect(CarbonPeriod::create($start, $end))
            ->filter(fn($d) => !$d->isWeekend())
            ->values()
            ->toArray();
    }

    protected function showStats(PayrollPeriod $period): void
    {
        $componentCount = EmployeePayrollComponent::where('is_active', true)->count();
        $scheduleCount = EmployeeSchedule::whereBetween('date', [$period->start_date, $period->end_date])->count();
        $logCount = AttendanceLog::whereBetween('date', [$period->start_date, $period->end_date])->count();
        $otCount = OvertimeRequest::whereBetween('date', [$period->start_date, $period->end_date])->count();
        $periodSummaryCount = \App\Models\AttendancePeriodSummary::where('payroll_period_id', $period->id)->count();

        $summaryStats = AttendanceSummary::whereBetween('date', [$period->start_date, $period->end_date])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->table(['Data', 'Count'], [
            ['Employee Payroll Components', $componentCount],
            ['Schedules', $scheduleCount],
            ['Attendance Logs', $logCount],
            ['Overtime Requests', $otCount],
            ['Period Summaries', $periodSummaryCount],
        ]);

        if (!empty($summaryStats)) {
            $this->newLine();
            $this->info('Attendance Summaries by Status:');
            $this->table(['Status', 'Count'], collect($summaryStats)->map(fn($c, $s) => [$s, $c])->values()->toArray());
        }
    }
}
