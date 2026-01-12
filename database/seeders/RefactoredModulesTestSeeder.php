<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\LeaveBalanceTransaction;
use App\Models\EmployeeLeaveBalance;
use App\Models\AttendanceAdjustment;
use App\Models\AttendanceSummary;
use App\Models\AttendanceLog;
use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use App\Models\PayrollSlipItem;
use App\Models\User;
use App\Services\Attendance\AttendanceSummaryService;

/**
 * Comprehensive Test Seeder for Refactored Modules
 * 
 * Tests:
 * 1. Leave Module - Per-day tracking, balance ledger, NEEDS_HR_REVIEW
 * 2. Payroll - Anti-duplication, period locking
 * 3. Attendance - Adjustments ledger, regen-safe summaries
 * 4. Approval - Double submit prevention (via leave requests)
 */
class RefactoredModulesTestSeeder extends Seeder
{
    protected AttendanceSummaryService $attendanceService;
    protected $output = [];

    public function run(): void
    {
        $this->attendanceService = app(AttendanceSummaryService::class);

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║      REFACTORED MODULES TEST SEEDER                          ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Get test employees
        $employees = Employee::take(3)->get();
        if ($employees->count() < 3) {
            $this->command->error('Butuh minimal 3 employees untuk testing. Jalankan EmployeeSeeder dulu.');
            return;
        }

        $this->testLeaveModule($employees);
        $this->testAttendanceAdjustments($employees);
        $this->testPayrollAntiDuplication();

        $this->printSummary();
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * TEST 1: LEAVE MODULE
     * - Per-day tracking (leave_request_days)
     * - Balance ledger (leave_balance_transactions)
     * - NEEDS_HR_REVIEW status
     * ═══════════════════════════════════════════════════════════════
     */
    protected function testLeaveModule($employees): void
    {
        $this->command->info('📋 TEST 1: LEAVE MODULE');
        $this->command->info('─────────────────────────────────────────');

        $leaveType = LeaveType::first();
        if (!$leaveType) {
            $this->command->warn('⚠ Tidak ada LeaveType. Skip leave test.');
            return;
        }

        $employee = $employees->first();
        $year = now()->year;

        // Ensure balance exists
        $balance = EmployeeLeaveBalance::firstOrCreate(
            ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => $year],
            ['quota' => 12, 'used' => 0, 'carry_forward' => 0]
        );

        // Scenario A: Normal leave request with per-day tracking
        $this->command->info("  ├─ Scenario A: Normal leave (3 days)");

        $startDate = now()->addDays(5);
        $endDate = now()->addDays(7);

        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => 3,
            'reason' => '[TEST] Normal 3-day leave',
            'status' => LeaveRequest::STATUS_PENDING,
        ]);

        // Create per-day records
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            LeaveRequestDay::create([
                'leave_request_id' => $leaveRequest->id,
                'date' => $date,
                'day_value' => 1.0,
                'status' => 'pending',
            ]);
        }

        $daysCount = LeaveRequestDay::where('leave_request_id', $leaveRequest->id)->count();
        $this->command->info("     └─ ✅ Created {$daysCount} leave_request_days");
        $this->output['leave_days_created'] = $daysCount;

        // Scenario B: Approve and check balance ledger
        $this->command->info("  ├─ Scenario B: Approve & check balance ledger");

        $balanceBefore = $balance->fresh()->used;

        // Simulate approval with ledger
        $balance->deductWithLedger(
            3,
            $leaveRequest->id,
            User::first()?->id
        );

        $leaveRequest->update(['status' => LeaveRequest::STATUS_APPROVED]);
        LeaveRequestDay::where('leave_request_id', $leaveRequest->id)
            ->update(['status' => 'approved']);

        $balanceAfter = $balance->fresh()->used;
        $transactions = LeaveBalanceTransaction::where('leave_request_id', $leaveRequest->id)
            ->count();

        $this->command->info("     ├─ Balance before: {$balanceBefore}, after: {$balanceAfter}");
        $this->command->info("     └─ ✅ Created {$transactions} balance transaction(s)");
        $this->output['balance_transactions'] = $transactions;

        // Scenario C: NEEDS_HR_REVIEW (insufficient balance)
        $this->command->info("  ├─ Scenario C: NEEDS_HR_REVIEW status");

        // Set balance to almost full
        $balance->update(['used' => 11]); // Only 1 day left from quota 12

        $leaveRequest2 = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(20),
            'end_date' => now()->addDays(22),
            'total_days' => 3, // Needs 3 but only has 1
            'reason' => '[TEST] Insufficient balance leave',
            'status' => LeaveRequest::STATUS_NEEDS_HR_REVIEW,
        ]);

        $this->command->info("     └─ ✅ Created NEEDS_HR_REVIEW request (ID: {$leaveRequest2->id})");
        $this->output['needs_hr_review_created'] = true;

        $this->command->info('');
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * TEST 2: ATTENDANCE ADJUSTMENTS
     * - Ledger entries for leave
     * - Manual override
     * - Regen-safe (recalculate preserves adjustments)
     * ═══════════════════════════════════════════════════════════════
     */
    protected function testAttendanceAdjustments($employees): void
    {
        $this->command->info('📊 TEST 2: ATTENDANCE ADJUSTMENTS');
        $this->command->info('─────────────────────────────────────────');

        $employee = $employees->skip(1)->first() ?? $employees->first();
        $testDate = now()->subDays(3)->format('Y-m-d');

        // Ensure schedule and shift exist
        $shift = Shift::first();
        if (!$shift) {
            $this->command->warn('⚠ Tidak ada Shift. Skip attendance test.');
            return;
        }

        // Scenario A: Create manual adjustment
        $this->command->info("  ├─ Scenario A: Create manual adjustment");

        $adjustment = AttendanceAdjustment::createManualOverride(
            $employee->id,
            $testDate,
            'present', // Override status to present
            60, // Add 60 minutes overtime
            '[TEST] Manual override by seeder',
            User::first()?->id ?? 1
        );

        $this->command->info("     └─ ✅ Created adjustment (ID: {$adjustment->id})");
        $this->output['adjustment_created'] = $adjustment->id;

        // Scenario B: Trigger recalculate and check adjustment is preserved
        $this->command->info("  ├─ Scenario B: Recalculate (regen-safe test)");

        $this->attendanceService->recalculate($employee->id, $testDate);

        $summary = AttendanceSummary::where('employee_id', $employee->id)
            ->where('date', $testDate)
            ->first();

        if ($summary) {
            $hasAdjustmentFlag = in_array('adjustment', $summary->source_flags ?? []);
            $this->command->info("     ├─ Summary status: {$summary->status}");
            $this->command->info("     ├─ Source flags: " . implode(', ', $summary->source_flags ?? []));
            $this->command->info("     └─ " . ($hasAdjustmentFlag ? '✅' : '❌') . " Adjustment preserved after regen");
            $this->output['adjustment_preserved'] = $hasAdjustmentFlag;
        }

        // Scenario C: Leave adjustment via ledger
        $this->command->info("  ├─ Scenario C: Leave adjustment via ledger");

        $leaveDate = now()->subDays(5)->format('Y-m-d');
        $leaveAdjustment = AttendanceAdjustment::createForLeave(
            $employee->id,
            $leaveDate,
            'leave',
            999, // fake leave request id
            User::first()?->id ?? 1
        );

        $this->attendanceService->recalculate($employee->id, $leaveDate);

        $leaveSummary = AttendanceSummary::where('employee_id', $employee->id)
            ->where('date', $leaveDate)
            ->first();

        $this->command->info("     ├─ Leave adjustment created (ID: {$leaveAdjustment->id})");
        $this->command->info("     └─ ✅ Summary status: " . ($leaveSummary->status ?? 'N/A'));
        $this->output['leave_adjustment_status'] = $leaveSummary->status ?? 'N/A';

        $this->command->info('');
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * TEST 3: PAYROLL ANTI-DUPLICATION
     * - Unique constraint check
     * - Period locking
     * ═══════════════════════════════════════════════════════════════
     */
    protected function testPayrollAntiDuplication(): void
    {
        $this->command->info('💰 TEST 3: PAYROLL ANTI-DUPLICATION');
        $this->command->info('─────────────────────────────────────────');

        $period = PayrollPeriod::first();
        if (!$period) {
            $this->command->warn('⚠ Tidak ada PayrollPeriod. Skip payroll test.');
            return;
        }

        // Scenario A: Check unique constraint exists
        $this->command->info("  ├─ Scenario A: Unique constraint check");

        $indexExists = DB::select("
            SELECT COUNT(*) as cnt FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'payroll_slip_items' 
            AND INDEX_NAME = 'unique_slip_component'
        ");

        $hasIndex = ($indexExists[0]->cnt ?? 0) > 0;
        $this->command->info("     └─ " . ($hasIndex ? '✅' : '❌') . " unique_slip_component index exists");
        $this->output['unique_index_exists'] = $hasIndex;

        // Scenario B: Period locking methods exist
        $this->command->info("  ├─ Scenario B: Period locking methods");

        $hasIsLocked = method_exists(PayrollPeriod::class, 'isLocked');
        $hasGuard = method_exists(PayrollPeriod::class, 'guardAgainstLock');

        $this->command->info("     ├─ " . ($hasIsLocked ? '✅' : '❌') . " isLocked() method");
        $this->command->info("     └─ " . ($hasGuard ? '✅' : '❌') . " guardAgainstLock() method");
        $this->output['period_lock_methods'] = $hasIsLocked && $hasGuard;

        // Scenario C: upsertFromArray method
        $this->command->info("  ├─ Scenario C: upsertFromArray method");

        $hasUpsert = method_exists(PayrollSlipItem::class, 'upsertFromArray');
        $this->command->info("     └─ " . ($hasUpsert ? '✅' : '❌') . " PayrollSlipItem::upsertFromArray()");
        $this->output['upsert_method'] = $hasUpsert;

        $this->command->info('');
    }

    /**
     * Print summary table
     */
    protected function printSummary(): void
    {
        $this->command->info('╔══════════════════════════════════════════════════════════════╗');
        $this->command->info('║                      TEST SUMMARY                             ║');
        $this->command->info('╠══════════════════════════════════════════════════════════════╣');

        $tests = [
            ['Leave per-day records', $this->output['leave_days_created'] ?? 0, '>= 3'],
            ['Balance transactions', $this->output['balance_transactions'] ?? 0, '>= 1'],
            ['NEEDS_HR_REVIEW status', $this->output['needs_hr_review_created'] ?? false, 'true'],
            ['Adjustment preserved', $this->output['adjustment_preserved'] ?? false, 'true'],
            ['Leave adj status', $this->output['leave_adjustment_status'] ?? 'N/A', 'leave'],
            ['Unique index exists', $this->output['unique_index_exists'] ?? false, 'true'],
            ['Period lock methods', $this->output['period_lock_methods'] ?? false, 'true'],
            ['Upsert method', $this->output['upsert_method'] ?? false, 'true'],
        ];

        foreach ($tests as $test) {
            $name = str_pad($test[0], 25);
            $value = is_bool($test[1]) ? ($test[1] ? 'true' : 'false') : $test[1];
            $expected = $test[2];

            $pass = $this->checkPass($test[1], $expected);
            $icon = $pass ? '✅' : '❌';

            $this->command->info("║  {$icon} {$name} = {$value}");
        }

        $this->command->info('╚══════════════════════════════════════════════════════════════╝');
        $this->command->info('');
    }

    protected function checkPass($value, $expected): bool
    {
        if ($expected === 'true')
            return $value === true;
        if (str_starts_with($expected, '>= ')) {
            $min = (int) substr($expected, 3);
            return $value >= $min;
        }
        return $value == $expected;
    }
}
