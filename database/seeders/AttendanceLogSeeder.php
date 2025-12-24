<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AttendanceLog::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🚀 Starting Attendance Logs Seeding...');

        // Get all active employees
        $employees = Employee::where('status', 'active')->get();

        if ($employees->isEmpty()) {
            $this->command->error('❌ No active employees found. Please run EmployeeSeeder first.');
            return;
        }

        $this->command->info("📊 Found {$employees->count()} active employees");

        // Seed for current month and previous month
        $currentMonth = Carbon::now();
        $previousMonth = Carbon::now()->subMonth();

        $this->seedMonthLogs($employees, $previousMonth);
        $this->seedMonthLogs($employees, $currentMonth);

        $totalLogs = AttendanceLog::count();
        $this->command->info("✅ Successfully seeded {$totalLogs} attendance logs!");

        // Show statistics
        $this->showStatistics();
    }

    /**
     * Seed logs for a specific month
     */
    private function seedMonthLogs($employees, Carbon $month): void
    {
        $monthName = $month->translatedFormat('F Y');
        $this->command->info("📅 Seeding logs for {$monthName}...");

        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $logsCreated = 0;

        // Loop through each day in the month
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Skip weekends for some variety (70% chance to work on weekends)
            if ($date->isWeekend() && rand(1, 100) > 70) {
                continue;
            }

            // Skip future dates
            if ($date->isFuture()) {
                continue;
            }

            foreach ($employees as $employee) {
                // Get schedule for this employee on this date
                $schedule = EmployeeSchedule::where('employee_id', $employee->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->first();

                // Skip if no schedule or day off or holiday
                if (!$schedule || $schedule->is_day_off || $schedule->is_holiday || !$schedule->shift) {
                    continue;
                }

                // 90% chance employee will clock in (simulate absences)
                if (rand(1, 100) > 90) {
                    continue;
                }

                $shift = $schedule->shift;

                // Create attendance log
                $log = $this->createAttendanceLog($employee, $schedule, $shift, $date);

                if ($log) {
                    $logsCreated++;
                }
            }
        }

        $this->command->info("   ✓ Created {$logsCreated} logs for {$monthName}");
    }

    /**
     * Create a single attendance log with realistic data
     */
    private function createAttendanceLog($employee, $schedule, $shift, Carbon $date): ?AttendanceLog
    {
        // Parse shift times
        $shiftStart = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->start_time);
        $shiftEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $shift->end_time);

        // Handle overnight shifts
        if ($shift->is_overnight) {
            $shiftEnd->addDay();
        }

        // Generate clock in time (with some variance)
        $clockInVariance = $this->getClockInVariance();
        $clockInTime = $shiftStart->copy()->addMinutes($clockInVariance);

        // Don't create future clock ins
        if ($clockInTime->isFuture()) {
            return null;
        }

        // Check if late
        $isLate = $clockInVariance > $shift->late_tolerance_minutes;
        $lateDuration = $isLate ? $clockInVariance - $shift->late_tolerance_minutes : 0;

        // Generate clock out time (90% will clock out if not today)
        $clockOutTime = null;
        $workDuration = 0;
        $isEarlyOut = false;

        if (!$date->isToday() || rand(1, 100) > 80) {
            // Clock out variance (-30 to +60 minutes from shift end)
            $clockOutVariance = rand(-30, 60);
            $clockOutTime = $shiftEnd->copy()->addMinutes($clockOutVariance);

            // Don't create future clock outs
            if ($clockOutTime->isFuture()) {
                $clockOutTime = null;
            } else {
                // Calculate work duration (exclude break time)
                $totalMinutes = $clockInTime->diffInMinutes($clockOutTime);

                if ($shift->break_start && $shift->break_end) {
                    $breakMinutes = Carbon::parse($shift->break_start)
                        ->diffInMinutes(Carbon::parse($shift->break_end));
                    $totalMinutes -= $breakMinutes;
                }

                $workDuration = max(0, $totalMinutes);

                // Check if early out
                $expectedOut = $shiftEnd->copy();
                $isEarlyOut = $clockOutTime->lessThan($expectedOut);
            }
        }

        // Generate GPS coordinates (Jakarta area with variance)
        $baseLat = -6.2088;
        $baseLong = 106.8456;
        $clockInLat = $baseLat + (rand(-1000, 1000) / 10000);
        $clockInLong = $baseLong + (rand(-1000, 1000) / 10000);
        $clockOutLat = $clockInLat + (rand(-100, 100) / 100000);
        $clockOutLong = $clockInLong + (rand(-100, 100) / 100000);

        // Device info
        $devices = [
            'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) Mobile',
            'Mozilla/5.0 (Android 13; Mobile) Chrome/108.0.0.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/108.0.0.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15',
        ];

        // Create the log
        return AttendanceLog::create([
            'employee_id' => $employee->id,
            'schedule_id' => $schedule->id,
            'shift_id' => $shift->id,
            'date' => $date->format('Y-m-d'),

            // Clock In
            'clock_in_time' => $clockInTime,
            'clock_in_lat' => $clockInLat,
            'clock_in_long' => $clockInLong,
            'clock_in_device' => $devices[array_rand($devices)],
            'clock_in_notes' => $this->getRandomClockInNotes(),

            // Clock Out
            'clock_out_time' => $clockOutTime,
            'clock_out_lat' => $clockOutTime ? $clockOutLat : null,
            'clock_out_long' => $clockOutTime ? $clockOutLong : null,
            'clock_out_device' => $clockOutTime ? $devices[array_rand($devices)] : null,
            'clock_out_notes' => $clockOutTime ? $this->getRandomClockOutNotes() : null,

            // Status
            'is_late' => $isLate,
            'is_early_out' => $isEarlyOut,
            'late_duration_minutes' => $lateDuration,
            'work_duration_minutes' => $workDuration,
        ]);
    }

    /**
     * Get clock in variance in minutes
     * Most employees on time, some late, few very late
     */
    private function getClockInVariance(): int
    {
        $random = rand(1, 100);

        if ($random <= 70) {
            // 70% on time or early (-5 to +5 minutes)
            return rand(-5, 5);
        } elseif ($random <= 90) {
            // 20% slightly late (6 to 15 minutes)
            return rand(6, 15);
        } elseif ($random <= 97) {
            // 7% moderately late (16 to 30 minutes)
            return rand(16, 30);
        } else {
            // 3% very late (31 to 60 minutes)
            return rand(31, 60);
        }
    }

    /**
     * Get random clock in notes
     */
    private function getRandomClockInNotes(): ?string
    {
        $notes = [
            null, // 60% no notes
            null,
            null,
            null,
            null,
            null,
            'Macet di jalan',
            'Kendaraan mogok',
            'Ada keperluan keluarga',
            'Bangun kesiangan',
            'Hujan deras',
            'Lalu lintas padat',
            'Meeting pagi',
            'Antar anak sekolah dulu',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Get random clock out notes
     */
    private function getRandomClockOutNotes(): ?string
    {
        $notes = [
            null, // 80% no notes
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            'Overtime selesai',
            'Meeting sampai sore',
            'Ada urusan mendadak',
            'Harus jemput anak',
            'Lembur project',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Show statistics after seeding
     */
    private function showStatistics(): void
    {
        $this->command->info("\n📊 Attendance Logs Statistics:");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $total = AttendanceLog::count();
        $complete = AttendanceLog::complete()->count();
        $incomplete = AttendanceLog::incomplete()->count();
        $late = AttendanceLog::late()->count();
        $earlyOut = AttendanceLog::earlyOut()->count();

        $this->command->table(
            ['Metric', 'Count', 'Percentage'],
            [
                ['Total Logs', $total, '100%'],
                ['Complete (Clock In & Out)', $complete, $this->percentage($complete, $total)],
                ['Incomplete (No Clock Out)', $incomplete, $this->percentage($incomplete, $total)],
                ['Late Clock In', $late, $this->percentage($late, $total)],
                ['Early Clock Out', $earlyOut, $this->percentage($earlyOut, $total)],
            ]
        );

        // Average work duration
        $avgDuration = AttendanceLog::complete()->avg('work_duration_minutes');
        $avgHours = round($avgDuration / 60, 2);

        $this->command->info("\n📈 Additional Stats:");
        $this->command->info("   Average Work Duration: {$avgHours} hours");

        // Latest logs
        $this->command->info("\n🕒 Recent Logs Sample:");
        AttendanceLog::with('employee')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->each(function ($log) {
                $status = $log->is_complete ? '✓' : '⏳';
                $late = $log->is_late ? '🔴' : '🟢';
                $this->command->info("   {$status} {$late} {$log->employee->full_name} - {$log->formatted_date}");
            });

        $this->command->info("\n✨ Seeding completed successfully!");
    }

    /**
     * Calculate percentage
     */
    private function percentage($value, $total): string
    {
        if ($total == 0)
            return '0%';
        return round(($value / $total) * 100, 1) . '%';
    }
}