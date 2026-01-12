<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Seeder 06: Attendance Events
 * 
 * Seeds clock-in/clock-out events for test employees.
 */
class Seed_06_AttendanceEventsSeeder extends Seeder
{
    public function run(): void
    {
        $period = PayrollPeriod::where('period_code', '2026-01')->first();
        if (!$period) {
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
        ])->get()->keyBy('nik');

        // Generate dates for January 2026 (skip weekends)
        $workDays = $this->getWorkDays($period->start_date, $period->end_date);

        // Employee A - Full month, perfect attendance
        $empA = $employees['EMP-A-001'] ?? null;
        if ($empA) {
            foreach ($workDays as $date) {
                $this->createAttendance($empA->id, $date, '08:55', '18:05');
            }
            $this->command->info("  - EMP-A: {$workDays->count()} days, perfect attendance");
        }

        // Employee B - Join mid-month (Jan 16)
        $empB = $employees['EMP-B-002'] ?? null;
        if ($empB) {
            $startDate = Carbon::parse('2026-01-16');
            $bWorkDays = $workDays->filter(fn($d) => $d->gte($startDate));
            foreach ($bWorkDays as $date) {
                $this->createAttendance($empB->id, $date, '08:58', '18:02');
            }
            $this->command->info("  - EMP-B: {$bWorkDays->count()} days (started Jan 16)");
        }

        // Employee C - Alpha & Late issues
        $empC = $employees['EMP-C-003'] ?? null;
        if ($empC) {
            $cWorkDays = $workDays->values();
            $alphaCount = 0;
            $lateCount = 0;

            foreach ($cWorkDays as $idx => $date) {
                // Alpha on day 5 and 10
                if (in_array($idx, [4, 9])) {
                    $alphaCount++;
                    continue;
                }

                // Late on day 2, 7, 15 (30 min late each)
                if (in_array($idx, [1, 6, 14])) {
                    $this->createAttendance($empC->id, $date, '09:30', '18:00', 30);
                    $lateCount++;
                    continue;
                }

                $this->createAttendance($empC->id, $date, '08:55', '18:00');
            }
            $this->command->info("  - EMP-C: {$alphaCount} alpha, {$lateCount} late days");
        }

        // Employee D - Overnight shift (22:00 - 06:00)
        $empD = $employees['EMP-D-004'] ?? null;
        if ($empD) {
            foreach ($workDays as $date) {
                $clockIn = $date->copy()->setTime(22, 5);
                $clockOut = $date->copy()->addDay()->setTime(6, 10);
                $this->createOvernightAttendance($empD->id, $clockIn, $clockOut);
            }
            $this->command->info("  - EMP-D: {$workDays->count()} overnight shifts");
        }

        // Employee E - Overtime (worked late, will be approved later)
        $empE = $employees['EMP-E-005'] ?? null;
        if ($empE) {
            foreach ($workDays as $idx => $date) {
                // OT on day 3, 8 (worked until 20:00)
                if (in_array($idx, [2, 7])) {
                    $this->createAttendance($empE->id, $date, '08:55', '20:00');
                    continue;
                }
                $this->createAttendance($empE->id, $date, '08:55', '18:00');
            }
            $this->command->info("  - EMP-E: normal + 2 OT days");
        }

        // Employee F - Late waived after lock
        $empF = $employees['EMP-F-006'] ?? null;
        if ($empF) {
            foreach ($workDays as $idx => $date) {
                // Late 60 min on day 1
                if ($idx === 0) {
                    $this->createAttendance($empF->id, $date, '10:00', '18:00', 60);
                    continue;
                }
                $this->createAttendance($empF->id, $date, '08:55', '18:00');
            }
            $this->command->info("  - EMP-F: 1 late day (60 min)");
        }

        // Employee G - Low salary, multiple late
        $empG = $employees['EMP-G-007'] ?? null;
        if ($empG) {
            $lateCount = 0;
            foreach ($workDays as $idx => $date) {
                // Late 45 min on every other day
                if ($idx % 2 === 0) {
                    $this->createAttendance($empG->id, $date, '09:45', '18:00', 45);
                    $lateCount++;
                    continue;
                }
                $this->createAttendance($empG->id, $date, '08:55', '18:00');
            }
            $this->command->info("  - EMP-G: {$lateCount} late days (45 min each)");
        }

        $this->command->info('✅ Seed_06: Attendance events seeded');
    }

    private function getWorkDays($startDate, $endDate): \Illuminate\Support\Collection
    {
        return collect(CarbonPeriod::create($startDate, $endDate))
            ->filter(fn($date) => !$date->isWeekend())
            ->values();
    }

    private function createAttendance(
        int $employeeId,
        Carbon $date,
        string $clockInTime,
        string $clockOutTime,
        int $lateMinutes = 0
    ): void {
        $clockIn = $date->copy()->setTimeFromTimeString($clockInTime);
        $clockOut = $date->copy()->setTimeFromTimeString($clockOutTime);
        $workMinutes = $clockIn->diffInMinutes($clockOut);

        AttendanceLog::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date->format('Y-m-d'),
            ],
            [
                'employee_id' => $employeeId,
                'date' => $date->format('Y-m-d'),
                'clock_in_time' => $clockIn,
                'clock_out_time' => $clockOut,
                'is_late' => $lateMinutes > 0,
                'late_duration_minutes' => $lateMinutes,
                'work_duration_minutes' => $workMinutes,
            ]
        );
    }

    private function createOvernightAttendance(
        int $employeeId,
        Carbon $clockIn,
        Carbon $clockOut
    ): void {
        $workMinutes = $clockIn->diffInMinutes($clockOut);

        AttendanceLog::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $clockIn->format('Y-m-d'),
            ],
            [
                'employee_id' => $employeeId,
                'date' => $clockIn->format('Y-m-d'),
                'clock_in_time' => $clockIn,
                'clock_out_time' => $clockOut,
                'is_late' => true,
                'late_duration_minutes' => 5,
                'work_duration_minutes' => $workMinutes,
            ]
        );
    }
}
