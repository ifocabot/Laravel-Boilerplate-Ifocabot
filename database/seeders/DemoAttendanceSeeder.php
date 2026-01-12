<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\AttendanceLog;
use App\Models\AttendanceSummary;
use App\Models\Shift;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\EmployeeLeaveBalance;
use App\Models\NationalHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

/**
 * Demo Attendance Seeder - Customizable attendance data generator
 * 
 * Usage:
 *   php artisan db:seed --class=DemoAttendanceSeeder
 *   
 * Customize values in $config array below
 */
class DemoAttendanceSeeder extends Seeder
{
    /**
     * ======================================
     * CUSTOMIZABLE CONFIGURATION
     * ======================================
     * Edit these values to generate different attendance patterns
     */
    protected array $config = [
        // Date range
        'start_date' => null,              // null = start of current month
        'end_date' => null,                // null = today

        // Attendance counts (in days) - auto-adjusted if exceeds working days
        'present_days' => 1,               // Normal present days
        'late_days' => 0,                  // Days coming late
        'sick_days' => 0,                  // Sick leave days
        'leave_days' => 0,                 // Annual leave days
        'wfh_days' => 0,                   // Work from home days
        'alpha_days' => 0,                 // Absent without notice

        // Overtime configuration
        'overtime_days' => 0,              // Days with overtime
        'min_overtime_minutes' => 60,      // Minimum overtime per day
        'max_overtime_minutes' => 180,     // Maximum overtime per day

        // Late details
        'min_late_minutes' => 10,          // Minimum late duration
        'max_late_minutes' => 45,          // Maximum late duration

        // Early leave (pulang cepat)
        'early_leave_days' => 0,           // Days leaving early
        'min_early_leave_minutes' => 30,   // Minimum early leave
        'max_early_leave_minutes' => 60,   // Maximum early leave
    ];

    protected Employee $employee;
    protected Shift $shift;
    protected Carbon $startDate;
    protected Carbon $endDate;
    protected array $workingDays = [];
    protected array $holidays = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📅 Starting Demo Attendance Seeder...');
        $this->command->info('   Configuration:');

        // Get demo employee
        $this->employee = Employee::where('nik', 'EMP00001')->first();
        if (!$this->employee) {
            $this->command->error('   ❌ Demo employee not found! Run DemoSeeder first.');
            return;
        }

        // Get shift
        $this->shift = Shift::where('code', 'SH001')->first();
        if (!$this->shift) {
            $this->command->error('   ❌ Default shift not found!');
            return;
        }

        // Setup date range
        $this->startDate = $this->config['start_date']
            ? Carbon::parse($this->config['start_date'])
            : Carbon::now()->startOfMonth();
        $this->endDate = $this->config['end_date']
            ? Carbon::parse($this->config['end_date'])
            : Carbon::now();

        $this->command->info("   📆 Period: {$this->startDate->format('Y-m-d')} to {$this->endDate->format('Y-m-d')}");

        // Get holidays
        $this->holidays = NationalHoliday::whereBetween('date', [$this->startDate, $this->endDate])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        // Calculate working days (exclude weekends and holidays)
        $this->workingDays = $this->getWorkingDays();
        $totalWorkingDays = count($this->workingDays);

        $this->command->info("   📊 Working days: {$totalWorkingDays}");
        $this->displayConfig();

        // Validate configuration
        $totalConfigDays = $this->config['present_days']
            + $this->config['late_days']
            + $this->config['sick_days']
            + $this->config['leave_days']
            + $this->config['wfh_days']
            + $this->config['alpha_days'];

        if ($totalConfigDays > $totalWorkingDays) {
            $this->command->warn("   ⚠️ Config total ({$totalConfigDays}) > working days ({$totalWorkingDays}). Adjusting...");
            $this->adjustConfig($totalWorkingDays);
        }

        // Clean existing data
        $this->cleanExistingData();

        // Generate attendance data
        try {
            $this->generateAttendanceData();
        } catch (\Exception $e) {
            $this->command->error('❌ Error: ' . $e->getMessage());
            $this->command->error('   File: ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }

        $this->command->info('✅ Demo Attendance Seeder completed!');
        $this->showSummary();
    }

    /**
     * Display current configuration
     */
    protected function displayConfig(): void
    {
        $this->command->table(
            ['Type', 'Days'],
            [
                ['Present (normal)', $this->config['present_days']],
                ['Late', $this->config['late_days']],
                ['Sick', $this->config['sick_days']],
                ['Leave', $this->config['leave_days']],
                ['WFH', $this->config['wfh_days']],
                ['Alpha', $this->config['alpha_days']],
                ['With Overtime', $this->config['overtime_days']],
            ]
        );
    }

    /**
     * Get working days in the date range
     */
    protected function getWorkingDays(): array
    {
        $workingDays = [];
        $period = CarbonPeriod::create($this->startDate, $this->endDate);

        foreach ($period as $date) {
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($date->dayOfWeek === Carbon::SATURDAY || $date->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }
            // Skip holidays
            if (in_array($date->format('Y-m-d'), $this->holidays)) {
                continue;
            }
            $workingDays[] = $date->copy();
        }

        return $workingDays;
    }

    /**
     * Adjust config if total exceeds working days
     */
    protected function adjustConfig(int $maxDays): void
    {
        $ratio = $maxDays / ($this->config['present_days']
            + $this->config['late_days']
            + $this->config['sick_days']
            + $this->config['leave_days']
            + $this->config['wfh_days']
            + $this->config['alpha_days']);

        $this->config['present_days'] = (int) floor($this->config['present_days'] * $ratio);
        $this->config['late_days'] = (int) floor($this->config['late_days'] * $ratio);
        $this->config['sick_days'] = (int) floor($this->config['sick_days'] * $ratio);
        $this->config['leave_days'] = (int) floor($this->config['leave_days'] * $ratio);
        $this->config['wfh_days'] = (int) floor($this->config['wfh_days'] * $ratio);
        $this->config['alpha_days'] = (int) floor($this->config['alpha_days'] * $ratio);
    }

    /**
     * Clean existing attendance data for demo employee
     */
    protected function cleanExistingData(): void
    {
        $this->command->info('🧹 Cleaning existing demo data...');

        // Delete attendance summaries
        AttendanceSummary::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->delete();

        // Delete attendance logs
        AttendanceLog::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->delete();

        // Delete schedules
        EmployeeSchedule::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->delete();

        // Delete leave requests in this period
        LeaveRequest::where('employee_id', $this->employee->id)
            ->where(function ($q) {
                $q->whereBetween('start_date', [$this->startDate, $this->endDate])
                    ->orWhereBetween('end_date', [$this->startDate, $this->endDate]);
            })
            ->delete();
    }

    /**
     * Generate attendance data
     */
    protected function generateAttendanceData(): void
    {
        $this->command->info('📝 Generating attendance data...');

        // Shuffle working days for random distribution
        $shuffledDays = $this->workingDays;
        shuffle($shuffledDays);

        $index = 0;
        $overtimeIndex = 0;
        $earlyLeaveIndex = 0;

        // Select days with overtime (from present days)
        $overtimeDays = array_slice($shuffledDays, 0, min($this->config['overtime_days'], count($shuffledDays)));

        // 1. Alpha days (no attendance at all)
        for ($i = 0; $i < $this->config['alpha_days'] && $index < count($shuffledDays); $i++, $index++) {
            $this->createAlphaDay($shuffledDays[$index]);
        }

        // 2. Sick days
        $sickDays = [];
        for ($i = 0; $i < $this->config['sick_days'] && $index < count($shuffledDays); $i++, $index++) {
            $sickDays[] = $shuffledDays[$index];
            $this->createSickDay($shuffledDays[$index]);
        }
        if (count($sickDays) > 0) {
            $this->createSickLeaveRequest($sickDays);
        }

        // 3. Leave days
        $leaveDays = [];
        for ($i = 0; $i < $this->config['leave_days'] && $index < count($shuffledDays); $i++, $index++) {
            $leaveDays[] = $shuffledDays[$index];
            $this->createLeaveDay($shuffledDays[$index]);
        }
        if (count($leaveDays) > 0) {
            $this->createAnnualLeaveRequest($leaveDays);
        }

        // 4. WFH days
        for ($i = 0; $i < $this->config['wfh_days'] && $index < count($shuffledDays); $i++, $index++) {
            $hasOvertime = in_array($shuffledDays[$index], $overtimeDays);
            $this->createWfhDay($shuffledDays[$index], $hasOvertime);
        }

        // 5. Late days
        for ($i = 0; $i < $this->config['late_days'] && $index < count($shuffledDays); $i++, $index++) {
            $hasOvertime = in_array($shuffledDays[$index], $overtimeDays);
            $hasEarlyLeave = $earlyLeaveIndex < $this->config['early_leave_days'];
            $this->createLateDay($shuffledDays[$index], $hasOvertime, $hasEarlyLeave);
            if ($hasEarlyLeave)
                $earlyLeaveIndex++;
        }

        // 6. Normal present days (remaining)
        for (; $index < count($shuffledDays); $index++) {
            $hasOvertime = in_array($shuffledDays[$index], $overtimeDays);
            $this->createPresentDay($shuffledDays[$index], $hasOvertime);
        }

        $this->command->info('   ✅ Schedules, logs, and summaries created');
    }

    /**
     * Create alpha day (absent without notice)
     */
    protected function createAlphaDay(Carbon $date): void
    {
        // Schedule only, no log, no summary attendance
        EmployeeSchedule::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'is_day_off' => false,
            'is_holiday' => false,
            'is_leave' => false,
        ]);

        // Create summary with alpha status
        AttendanceSummary::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'status' => 'alpha',
            'clock_in_at' => null,
            'clock_out_at' => null,
            'total_work_minutes' => 0,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 0,
        ]);
    }

    /**
     * Create sick day
     */
    protected function createSickDay(Carbon $date): void
    {
        EmployeeSchedule::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'is_day_off' => false,
            'is_holiday' => false,
            'is_leave' => true,
            'notes' => 'Sakit',
        ]);

        AttendanceSummary::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'status' => 'sick',
            'clock_in_at' => null,
            'clock_out_at' => null,
            'total_work_minutes' => 0,
            'notes' => 'Sakit - surat dokter',
        ]);
    }

    /**
     * Create leave day
     */
    protected function createLeaveDay(Carbon $date): void
    {
        EmployeeSchedule::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'is_day_off' => false,
            'is_holiday' => false,
            'is_leave' => true,
            'notes' => 'Cuti tahunan',
        ]);

        AttendanceSummary::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'status' => 'leave',
            'clock_in_at' => null,
            'clock_out_at' => null,
            'total_work_minutes' => 0,
            'notes' => 'Cuti tahunan',
        ]);
    }

    /**
     * Create WFH day
     */
    protected function createWfhDay(Carbon $date, bool $hasOvertime = false): void
    {
        $shiftStart = Carbon::parse($this->shift->start_time);
        $shiftEnd = Carbon::parse($this->shift->end_time);

        $clockIn = $date->copy()->setTime($shiftStart->hour, $shiftStart->minute);
        $clockOut = $date->copy()->setTime($shiftEnd->hour, $shiftEnd->minute);

        if ($hasOvertime) {
            $overtimeMinutes = rand($this->config['min_overtime_minutes'], $this->config['max_overtime_minutes']);
            $clockOut->addMinutes($overtimeMinutes);
        }

        $workMinutes = $clockIn->diffInMinutes($clockOut);

        EmployeeSchedule::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'is_day_off' => false,
            'is_holiday' => false,
            'is_leave' => false,
            'notes' => 'WFH',
        ]);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'clock_in_time' => $clockIn,
            'clock_out_time' => $clockOut,
            'clock_in_device' => 'system',
            'clock_out_device' => 'system',
            'work_duration_minutes' => $workMinutes,
        ]);

        AttendanceSummary::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'status' => 'wfh',
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'total_work_minutes' => $workMinutes,
            'overtime_minutes' => $hasOvertime ? ($clockOut->diffInMinutes($date->copy()->setTime($shiftEnd->hour, $shiftEnd->minute))) : 0,
            'notes' => 'Work from Home',
        ]);
    }

    /**
     * Create late day
     */
    protected function createLateDay(Carbon $date, bool $hasOvertime = false, bool $hasEarlyLeave = false): void
    {
        $shiftStart = Carbon::parse($this->shift->start_time);
        $shiftEnd = Carbon::parse($this->shift->end_time);

        $lateMinutes = rand($this->config['min_late_minutes'], $this->config['max_late_minutes']);
        $clockIn = $date->copy()->setTime($shiftStart->hour, $shiftStart->minute)->addMinutes($lateMinutes);
        $clockOut = $date->copy()->setTime($shiftEnd->hour, $shiftEnd->minute);

        $earlyLeaveMinutes = 0;
        if ($hasEarlyLeave) {
            $earlyLeaveMinutes = rand($this->config['min_early_leave_minutes'], $this->config['max_early_leave_minutes']);
            $clockOut->subMinutes($earlyLeaveMinutes);
        }

        if ($hasOvertime) {
            $overtimeMinutes = rand($this->config['min_overtime_minutes'], $this->config['max_overtime_minutes']);
            $clockOut->addMinutes($overtimeMinutes);
        }

        $workMinutes = $clockIn->diffInMinutes($clockOut);

        EmployeeSchedule::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'is_day_off' => false,
            'is_holiday' => false,
            'is_leave' => false,
        ]);

        AttendanceLog::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'clock_in_time' => $clockIn,
            'clock_out_time' => $clockOut,
            'clock_in_device' => 'mobile',
            'clock_out_device' => 'mobile',
            'work_duration_minutes' => $workMinutes,
            'is_late' => true,
            'late_duration_minutes' => $lateMinutes,
        ]);

        AttendanceSummary::create([
            'employee_id' => $this->employee->id,
            'date' => $date,
            'shift_id' => $this->shift->id,
            'status' => 'late',
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'total_work_minutes' => $workMinutes,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => $earlyLeaveMinutes,
            'overtime_minutes' => $hasOvertime ? rand($this->config['min_overtime_minutes'], $this->config['max_overtime_minutes']) : 0,
        ]);
    }

    /**
     * Create normal present day
     */
    protected function createPresentDay(Carbon $date, bool $hasOvertime = false): void
    {
        $shiftStart = Carbon::parse($this->shift->start_time);
        $shiftEnd = Carbon::parse($this->shift->end_time);

        // Slight random variance for clock in (0-5 minutes early)
        $earlyMinutes = rand(0, 5);
        $clockIn = $date->copy()->setTime($shiftStart->hour, $shiftStart->minute)->subMinutes($earlyMinutes);
        $clockOut = $date->copy()->setTime($shiftEnd->hour, $shiftEnd->minute);

        $overtimeMinutes = 0;
        if ($hasOvertime) {
            $overtimeMinutes = rand($this->config['min_overtime_minutes'], $this->config['max_overtime_minutes']);
            $clockOut->addMinutes($overtimeMinutes);
        }

        $workMinutes = $clockIn->diffInMinutes($clockOut);

        EmployeeSchedule::updateOrCreate(
            ['employee_id' => $this->employee->id, 'date' => $date->format('Y-m-d')],
            [
                'shift_id' => $this->shift->id,
                'is_day_off' => false,
                'is_holiday' => false,
                'is_leave' => false,
            ]
        );

        AttendanceLog::updateOrCreate(
            ['employee_id' => $this->employee->id, 'date' => $date->format('Y-m-d')],
            [
                'shift_id' => $this->shift->id,
                'clock_in_time' => $clockIn,
                'clock_out_time' => $clockOut,
                'clock_in_device' => 'mobile',
                'clock_out_device' => 'mobile',
                'work_duration_minutes' => $workMinutes,
            ]
        );

        AttendanceSummary::updateOrCreate(
            ['employee_id' => $this->employee->id, 'date' => $date->format('Y-m-d')],
            [
                'shift_id' => $this->shift->id,
                'status' => 'present',
                'clock_in_at' => $clockIn,
                'clock_out_at' => $clockOut,
                'total_work_minutes' => $workMinutes,
                'late_minutes' => 0,
                'overtime_minutes' => $overtimeMinutes,
            ]
        );
    }

    /**
     * Create sick leave request
     */
    protected function createSickLeaveRequest(array $sickDays): void
    {
        if (empty($sickDays))
            return;

        $leaveType = LeaveType::where('code', 'SICK')->first();
        if (!$leaveType)
            return;

        // Sort days
        usort($sickDays, fn($a, $b) => $a->timestamp - $b->timestamp);
        $startDate = $sickDays[0];
        $endDate = end($sickDays);

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => count($sickDays),
            'reason' => 'Sakit - demam dan flu',
            'status' => 'approved',
            'approved_at' => $startDate->copy()->addDay(),
            'approved_by' => 1,
        ]);

        // Create leave request days
        foreach ($sickDays as $day) {
            LeaveRequestDay::create([
                'leave_request_id' => $leaveRequest->id,
                'date' => $day,
                'day_value' => 1.0,
                'status' => 'approved',
            ]);
        }
    }

    /**
     * Create annual leave request
     */
    protected function createAnnualLeaveRequest(array $leaveDays): void
    {
        if (empty($leaveDays))
            return;

        $leaveType = LeaveType::where('code', 'ANNUAL')->first();
        if (!$leaveType)
            return;

        // Sort days
        usort($leaveDays, fn($a, $b) => $a->timestamp - $b->timestamp);
        $startDate = $leaveDays[0];
        $endDate = end($leaveDays);

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => count($leaveDays),
            'reason' => 'Keperluan keluarga',
            'status' => 'approved',
            'approved_at' => $startDate->copy()->subDays(3),
            'approved_by' => 1,
        ]);

        // Create leave request days
        foreach ($leaveDays as $day) {
            LeaveRequestDay::create([
                'leave_request_id' => $leaveRequest->id,
                'date' => $day,
                'day_value' => 1.0,
                'status' => 'approved',
            ]);
        }

        // Update leave balance
        $balance = EmployeeLeaveBalance::where('employee_id', $this->employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', Carbon::now()->year)
            ->first();

        if ($balance) {
            $balance->used += count($leaveDays);
            $balance->save();
        }
    }

    /**
     * Show summary of generated data
     */
    protected function showSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 Generated Attendance Summary:');

        $summaries = AttendanceSummary::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $rows = $summaries->map(fn($s) => [$s->status, $s->count])->toArray();
        $this->command->table(['Status', 'Count'], $rows);

        $totalOvertimeMinutes = AttendanceSummary::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('overtime_minutes');

        $totalLateMinutes = AttendanceSummary::where('employee_id', $this->employee->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('late_minutes');

        $this->command->info("   Total Overtime: " . round($totalOvertimeMinutes / 60, 1) . " hours");
        $this->command->info("   Total Late: " . round($totalLateMinutes / 60, 1) . " hours");
    }
}
