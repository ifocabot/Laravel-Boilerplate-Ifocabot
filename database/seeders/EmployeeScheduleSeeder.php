<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeSchedule;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class EmployeeScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating Employee Schedules...');

        // Get active employees
        $employees = Employee::where('status', 'active')->get();

        if ($employees->isEmpty()) {
            $this->command->error('❌ No active employees found. Please run EmployeeSeeder first.');
            return;
        }

        // Get default shift
        $defaultShift = Shift::getDefault();

        if (!$defaultShift) {
            $this->command->error('❌ No shifts found. Please run ShiftSeeder first.');
            return;
        }

        // Generate schedules for current month and previous month
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $previousMonth = now()->subMonth()->month;
        $previousYear = now()->subMonth()->year;

        $totalCreated = 0;
        $this->command->newLine();

        foreach ($employees as $employee) {
            $employeeSchedules = 0;

            // Generate for previous month
            $prevCreated = $this->generateMonthSchedule($employee, $previousYear, $previousMonth, $defaultShift);
            $employeeSchedules += $prevCreated;

            // Generate for current month
            $currCreated = $this->generateMonthSchedule($employee, $currentYear, $currentMonth, $defaultShift);
            $employeeSchedules += $currCreated;

            // Generate for next month
            $nextMonth = now()->addMonth();
            $nextCreated = $this->generateMonthSchedule($employee, $nextMonth->year, $nextMonth->month, $defaultShift);
            $employeeSchedules += $nextCreated;

            $this->command->info("  ✅ {$employee->full_name}: {$employeeSchedules} schedules created");
            $totalCreated += $employeeSchedules;
        }

        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->table(
            ['Metric', 'Value'],
            [
                ['Total Employees', $employees->count()],
                ['Total Schedules Created', $totalCreated],
                ['Default Shift', $defaultShift->name . ' (' . $defaultShift->code . ')'],
                ['Period', now()->subMonth()->format('M Y') . ' - ' . now()->addMonth()->format('M Y')],
            ]
        );

        // Show schedule type breakdown
        $this->command->newLine();
        $this->command->info('📋 Schedule Type Breakdown:');

        $stats = EmployeeSchedule::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN is_day_off = 0 AND is_holiday = 0 AND is_leave = 0 THEN 1 ELSE 0 END) as working_days,
            SUM(is_day_off) as day_offs,
            SUM(is_holiday) as holidays,
            SUM(is_leave) as leaves
        ')->first();

        $this->command->table(
            ['Type', 'Count'],
            [
                ['Working Days', $stats->working_days],
                ['Day Offs (Weekend)', $stats->day_offs],
                ['Holidays', $stats->holidays],
                ['Leaves', $stats->leaves],
                ['Total', $stats->total],
            ]
        );

        $this->command->newLine();
        $this->command->info('✨ Employee schedules seeded successfully!');
    }

    private function generateMonthSchedule(Employee $employee, int $year, int $month, Shift $shift): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $period = CarbonPeriod::create($startDate, $endDate);
        $created = 0;

        // Get working days from shift (default: Mon-Fri = 1-5)
        $workingDays = $shift->working_days ?? [1, 2, 3, 4, 5]; // ISO weekdays

        foreach ($period as $date) {
            // Check if schedule already exists
            $exists = EmployeeSchedule::where('employee_id', $employee->id)
                ->where('date', $date->format('Y-m-d'))
                ->exists();

            if ($exists) {
                continue;
            }

            // Check if it's a working day or day off
            $dayOfWeek = $date->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
            $isWorkingDay = in_array($dayOfWeek, $workingDays);

            EmployeeSchedule::create([
                'employee_id' => $employee->id,
                'shift_id' => $isWorkingDay ? $shift->id : null,
                'date' => $date->format('Y-m-d'),
                'is_day_off' => !$isWorkingDay,
                'is_holiday' => false,
                'is_leave' => false,
                'leave_request_id' => null,
                'notes' => !$isWorkingDay ? 'Weekend' : null,
            ]);

            $created++;
        }

        return $created;
    }
}
