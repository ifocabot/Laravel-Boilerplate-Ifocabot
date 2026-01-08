<?php

namespace Database\Seeders;

use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\Shift;
use App\Models\EmployeeSchedule;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollTestSeeder extends Seeder
{
    /**
     * ==============================================
     * CONFIGURATION - Customize these values!
     * ==============================================
     */

    // Period to seed (change as needed)
    protected int $year = 2026;
    protected int $month = 1;

    // Attendance scenarios per employee (customize!)
    protected array $scenarios = [
        // Format: 'employee_nik' => [present, late, alpha, leave, overtime_requests]
        // Leave these as-is to apply to first N employees found
    ];

    // Default distribution if no specific scenario
    protected array $defaultDistribution = [
        'present_days' => 18,      // Normal working days
        'late_days' => 3,          // Days late (still present but late)
        'alpha_days' => 1,         // Absent without reason
        'leave_days' => 1,         // Cuti
        'sick_days' => 1,          // Sakit
        'overtime_days' => 5,      // Days with overtime
        'avg_overtime_minutes' => 120, // Average OT per day (2 hours)
    ];

    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $this->command->info('=== Payroll Test Seeder ===');
        $this->command->info("Period: {$this->month}/{$this->year}");

        // Get or create period
        $period = $this->getOrCreatePeriod();
        $this->command->info("Period: {$period->period_name}");

        // Get active employees
        $employees = Employee::where('status', 'active')
            ->with('sensitiveData')
            ->take(10) // Limit to 10 for testing
            ->get();

        if ($employees->isEmpty()) {
            $this->command->error('No active employees found!');
            return;
        }

        $this->command->info("Seeding for {$employees->count()} employees...");

        // Get default shift
        $shift = Shift::first();
        if (!$shift) {
            $this->command->error('No shift found! Create shifts first.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $this->seedEmployeeAttendance($employee, $period, $shift);
            }

            DB::commit();
            $this->command->info('✅ Seeding completed!');
            $this->command->newLine();
            $this->command->info('Next steps:');
            $this->command->info('1. Go to Payroll Periods');
            $this->command->info('2. Click "Generate Slips" on period: ' . $period->period_name);
            $this->command->info('3. Review generated slips');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get or create payroll period
     */
    protected function getOrCreatePeriod(): PayrollPeriod
    {
        // For custom cutoff (21st to 20th)
        $cutoffStart = 21;
        $cutoffEnd = 20;

        // Calculate actual dates
        $startDate = Carbon::create($this->year, $this->month, $cutoffStart)->subMonth();
        $endDate = Carbon::create($this->year, $this->month, $cutoffEnd);

        // Standard 1st to end of month (comment above, uncomment below if needed)
        // $startDate = Carbon::create($this->year, $this->month, 1);
        // $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return PayrollPeriod::firstOrCreate(
            [
                'year' => $this->year,
                'month' => $this->month,
            ],
            [
                'period_code' => "PAY-{$this->year}-" . str_pad($this->month, 2, '0', STR_PAD_LEFT),
                'period_name' => "Gaji " . Carbon::create($this->year, $this->month)->translatedFormat('F Y'),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_date' => $endDate->copy()->addDays(5),
                'cutoff_start_day' => $cutoffStart,
                'cutoff_end_day' => $cutoffEnd,
                'status' => 'draft',
            ]
        );
    }

    /**
     * Seed attendance for single employee
     */
    protected function seedEmployeeAttendance(Employee $employee, PayrollPeriod $period, Shift $shift): void
    {
        $distribution = $this->scenarios[$employee->nik] ?? $this->defaultDistribution;

        $this->command->info("  → {$employee->full_name} ({$employee->nik})");

        // Get working days in period
        $workingDays = $this->getWorkingDays($period->start_date, $period->end_date);
        $totalDays = count($workingDays);

        // Assign statuses
        $statusPool = [];

        // Fill status pool based on distribution
        for ($i = 0; $i < ($distribution['present_days'] ?? 15); $i++) {
            $statusPool[] = 'present';
        }
        for ($i = 0; $i < ($distribution['late_days'] ?? 2); $i++) {
            $statusPool[] = 'late';
        }
        for ($i = 0; $i < ($distribution['alpha_days'] ?? 1); $i++) {
            $statusPool[] = 'alpha';
        }
        for ($i = 0; $i < ($distribution['leave_days'] ?? 1); $i++) {
            $statusPool[] = 'leave';
        }
        for ($i = 0; $i < ($distribution['sick_days'] ?? 1); $i++) {
            $statusPool[] = 'sick';
        }

        // Shuffle for randomness
        shuffle($statusPool);

        // Pad or trim to match working days
        while (count($statusPool) < $totalDays) {
            $statusPool[] = 'present';
        }
        $statusPool = array_slice($statusPool, 0, $totalDays);

        // Create attendance summaries
        $overtimeDaysCount = $distribution['overtime_days'] ?? 3;
        $avgOvertimeMinutes = $distribution['avg_overtime_minutes'] ?? 120;
        $overtimeDaysAssigned = 0;

        foreach ($workingDays as $index => $date) {
            $status = $statusPool[$index];

            $summary = $this->createAttendanceSummary(
                $employee,
                $period,
                $shift,
                $date,
                $status
            );

            // Add overtime to some present/late days
            if (in_array($status, ['present', 'late']) && $overtimeDaysAssigned < $overtimeDaysCount) {
                $this->createOvertimeRequest($employee, $date, $avgOvertimeMinutes, $summary);
                $overtimeDaysAssigned++;
            }
        }

        // Add weekend/holidays as offday
        $this->createOffdaySummaries($employee, $period);
    }

    /**
     * Get working days (Mon-Fri) in period
     */
    protected function getWorkingDays($startDate, $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $workingDays = [];

        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $workingDays[] = $date->copy();
            }
        }

        return $workingDays;
    }

    /**
     * Create single attendance summary
     */
    protected function createAttendanceSummary(
        Employee $employee,
        PayrollPeriod $period,
        Shift $shift,
        Carbon $date,
        string $status
    ): AttendanceSummary {
        // Calculate times based on status
        $plannedStart = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);
        $plannedEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time);

        // Handle cross-midnight
        if ($plannedEnd->lt($plannedStart)) {
            $plannedEnd->addDay();
        }

        $data = [
            'employee_id' => $employee->id,
            'date' => $date->toDateString(),
            'payroll_period_id' => $period->id,
            'shift_id' => $shift->id,
            'planned_start_at' => $plannedStart,
            'planned_end_at' => $plannedEnd,
            'status' => $status,
            'source_flags' => ['seeder'],
        ];

        // Set times based on status
        switch ($status) {
            case 'present':
                $data['clock_in_at'] = $plannedStart->copy()->subMinutes(rand(5, 15));
                $data['clock_out_at'] = $plannedEnd->copy()->addMinutes(rand(0, 30));
                $data['total_work_minutes'] = $plannedStart->diffInMinutes($plannedEnd);
                $data['late_minutes'] = 0;
                break;

            case 'late':
                $lateMinutes = rand(10, 60);
                $data['clock_in_at'] = $plannedStart->copy()->addMinutes($lateMinutes);
                $data['clock_out_at'] = $plannedEnd->copy()->addMinutes(rand(0, 30));
                $data['total_work_minutes'] = $plannedStart->copy()->addMinutes($lateMinutes)->diffInMinutes($data['clock_out_at']);
                $data['late_minutes'] = $lateMinutes;
                break;

            case 'alpha':
            case 'absent':
                $data['clock_in_at'] = null;
                $data['clock_out_at'] = null;
                $data['total_work_minutes'] = 0;
                $data['late_minutes'] = 0;
                break;

            case 'leave':
            case 'sick':
                $data['clock_in_at'] = null;
                $data['clock_out_at'] = null;
                $data['total_work_minutes'] = 0;
                $data['late_minutes'] = 0;
                break;
        }

        return AttendanceSummary::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
            ],
            $data
        );
    }

    /**
     * Create overtime request (approved)
     */
    protected function createOvertimeRequest(
        Employee $employee,
        Carbon $date,
        int $avgMinutes,
        AttendanceSummary $summary
    ): void {
        // Random overtime between 1-3 hours
        $minutes = rand(max(60, $avgMinutes - 60), $avgMinutes + 60);

        $overtime = OvertimeRequest::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $date->toDateString(),
            ],
            [
                'requested_duration_minutes' => $minutes,
                'approved_duration_minutes' => $minutes,
                'reason' => 'Test overtime - generated by seeder',
                'status' => 'approved',
                'approved_by' => 1, // Admin user
                'approved_at' => now(),
            ]
        );

        // Update summary with overtime
        $summary->update([
            'overtime_request_id' => $overtime->id,
            'detected_overtime_minutes' => $minutes,
            'approved_overtime_minutes' => $minutes,
        ]);
    }

    /**
     * Create offday summaries for weekends
     */
    protected function createOffdaySummaries(Employee $employee, PayrollPeriod $period): void
    {
        $periodRange = CarbonPeriod::create($period->start_date, $period->end_date);

        foreach ($periodRange as $date) {
            if ($date->isWeekend()) {
                AttendanceSummary::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'payroll_period_id' => $period->id,
                        'status' => 'offday',
                        'total_work_minutes' => 0,
                        'source_flags' => ['seeder', 'weekend'],
                    ]
                );
            }
        }
    }
}
