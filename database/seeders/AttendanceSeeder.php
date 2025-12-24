<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating Attendance Logs for Demo Data...');

        // Get active employees
        $employees = Employee::where('status', 'active')->get();

        if ($employees->isEmpty()) {
            $this->command->error('❌ No active employees found. Please run EmployeeSeeder first.');
            return;
        }

        // Get default shift using model's static method
        $shift = Shift::getDefault();

        if (!$shift) {
            $this->command->error('❌ No shifts found. Please run ShiftSeeder first.');
            return;
        }

        // Office location (Jakarta area)
        $officeLat = -6.2088;
        $officeLong = 106.8456;

        // Generate attendance for the last 30 days (excluding weekends)
        $startDate = now()->subDays(30);
        $endDate = now()->subDay(); // Yesterday
        $period = CarbonPeriod::create($startDate, $endDate);

        $totalCreated = 0;
        $this->command->newLine();

        foreach ($employees as $employee) {
            $employeeAttendance = 0;

            foreach ($period as $date) {
                // Skip weekends
                if ($date->isWeekend()) {
                    continue;
                }

                // Check if attendance already exists
                $exists = AttendanceLog::where('employee_id', $employee->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Random scenarios (85% normal, 10% late, 5% absent)
                $scenario = $this->getRandomScenario();

                if ($scenario === 'absent') {
                    // Skip - no attendance record for this day (simulates alpha)
                    continue;
                }

                // Generate clock in time
                $clockInTime = $this->generateClockInTime($date, $shift, $scenario);

                // Generate clock out time
                $clockOutTime = $this->generateClockOutTime($clockInTime, $shift, $scenario);

                // Calculate late status
                $shiftStart = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);
                $isLate = $clockInTime->greaterThan($shiftStart->copy()->addMinutes($shift->late_tolerance ?? 15));
                $lateDuration = $isLate ? $shiftStart->diffInMinutes($clockInTime) : 0;

                // Calculate early out status
                $shiftEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time);
                $isEarlyOut = $clockOutTime->lessThan($shiftEnd);

                // Calculate work duration
                $workDuration = $clockInTime->diffInMinutes($clockOutTime);

                // Subtract break time if applicable
                if ($shift->break_start && $shift->break_end) {
                    $breakDuration = Carbon::parse($shift->break_start)->diffInMinutes(Carbon::parse($shift->break_end));
                    $workDuration = max(0, $workDuration - $breakDuration);
                }

                // Create attendance log
                AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'shift_id' => $shift->id,
                    'date' => $date->format('Y-m-d'),
                    'clock_in_time' => $clockInTime,
                    'clock_in_lat' => $this->randomizeLocation($officeLat, 0.002),
                    'clock_in_long' => $this->randomizeLocation($officeLong, 0.002),
                    'clock_in_device' => 'Web Browser',
                    'clock_in_notes' => $isLate ? 'Terjebak macet' : null,
                    'clock_out_time' => $clockOutTime,
                    'clock_out_lat' => $this->randomizeLocation($officeLat, 0.002),
                    'clock_out_long' => $this->randomizeLocation($officeLong, 0.002),
                    'clock_out_device' => 'Web Browser',
                    'clock_out_notes' => null,
                    'is_late' => $isLate,
                    'is_early_out' => $isEarlyOut,
                    'late_duration_minutes' => $lateDuration,
                    'work_duration_minutes' => $workDuration,
                ]);

                $employeeAttendance++;
                $totalCreated++;
            }

            $this->command->info("  ✅ {$employee->full_name}: {$employeeAttendance} days created");
        }

        $this->command->newLine();
        $this->command->info("📊 Summary:");
        $this->command->table(
            ['Metric', 'Value'],
            [
                ['Total Employees', $employees->count()],
                ['Total Attendance Records', $totalCreated],
                ['Date Range', $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')],
            ]
        );

        // Show statistics
        $this->command->newLine();
        $this->command->info("📈 Attendance Statistics:");
        $stats = AttendanceLog::selectRaw('
            COUNT(*) as total,
            SUM(is_late) as late_count,
            SUM(is_early_out) as early_out_count,
            AVG(work_duration_minutes) as avg_work_minutes
        ')->first();

        $this->command->table(
            ['Stat', 'Value'],
            [
                ['Total Records', $stats->total],
                ['Late Count', $stats->late_count],
                ['Early Out Count', $stats->early_out_count],
                ['Avg Work Hours', round($stats->avg_work_minutes / 60, 2) . ' hours'],
            ]
        );

        $this->command->newLine();
        $this->command->info('✨ Attendance seeded successfully!');
    }

    private function getRandomScenario(): string
    {
        $rand = rand(1, 100);

        if ($rand <= 85) {
            return 'normal';
        } elseif ($rand <= 95) {
            return 'late';
        } else {
            return 'absent';
        }
    }

    private function generateClockInTime(Carbon $date, Shift $shift, string $scenario): Carbon
    {
        $baseTime = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);

        return match ($scenario) {
            'late' => $baseTime->copy()->addMinutes(rand(15, 60)), // 15-60 minutes late
            default => $baseTime->copy()->subMinutes(rand(0, 15)), // 0-15 minutes early
        };
    }

    private function generateClockOutTime(Carbon $clockInTime, Shift $shift, string $scenario): Carbon
    {
        $baseTime = Carbon::parse($clockInTime->format('Y-m-d') . ' ' . $shift->end_time);

        // Random variation: -30 to +60 minutes from end time
        $variation = rand(-30, 60);

        return $baseTime->copy()->addMinutes($variation);
    }

    private function randomizeLocation(float $base, float $variance): float
    {
        return round($base + (rand(-100, 100) / 100) * $variance, 8);
    }
}
