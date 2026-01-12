<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\LeaveBalanceTransaction;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating Leave Requests, Days, and Ledger...');

        // Get active employees
        $employees = Employee::where('status', 'active')->get();

        if ($employees->isEmpty()) {
            $this->command->error('❌ No active employees found. Please run EmployeeSeeder first.');
            return;
        }

        // Get leave types
        $leaveTypes = LeaveType::active()->get();

        if ($leaveTypes->isEmpty()) {
            $this->command->error('❌ No leave types found. Please run LeaveTypeSeeder first.');
            return;
        }

        $currentYear = now()->year;
        $this->command->newLine();

        // Step 1: Create leave balances for all employees with initial allocation ledger
        $this->command->info('📋 Creating Leave Balances with Ledger...');
        $balancesCreated = 0;

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                // Skip if already exists
                $existing = EmployeeLeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $currentYear)
                    ->first();

                if (!$existing) {
                    $balance = EmployeeLeaveBalance::create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $currentYear,
                        'quota' => $leaveType->default_quota,
                        'used' => 0,
                        'carry_forward' => 0,
                    ]);

                    // ⭐ Create allocation ledger entry
                    LeaveBalanceTransaction::create([
                        'employee_leave_balance_id' => $balance->id,
                        'leave_request_id' => null,
                        'type' => LeaveBalanceTransaction::TYPE_ALLOCATION,
                        'amount' => $leaveType->default_quota,
                        'balance_after' => $leaveType->default_quota,
                        'description' => "Alokasi cuti tahunan {$currentYear}",
                        'created_by' => null,
                    ]);

                    $balancesCreated++;
                }
            }
        }

        $this->command->info("  ✅ Created {$balancesCreated} leave balances with ledger entries");

        // Step 2: Create sample leave requests with per-day records
        $this->command->newLine();
        $this->command->info('📝 Creating Sample Leave Requests with Days...');

        $annualLeave = LeaveType::where('code', 'ANNUAL')->first();
        $sickLeave = LeaveType::where('code', 'SICK')->first();

        // Get admin/HR user for approval
        $approver = User::whereHas('employee.currentCareer', function ($q) {
            $q->whereHas('level', function ($lq) {
                $lq->where('approval_order', '>=', 2);
            });
        })->first() ?? User::first();

        $leaveRequests = [];

        // Create diverse leave requests for demo
        if ($employees->count() >= 1 && $annualLeave) {
            // Request 1: Past approved annual leave (2 days ago)
            $leaveRequests[] = [
                'employee_id' => $employees[0]->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => now()->subDays(10)->format('Y-m-d'),
                'end_date' => now()->subDays(8)->format('Y-m-d'),
                'reason' => 'Liburan keluarga ke Bali',
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now()->subDays(12),
            ];
        }

        if ($employees->count() >= 2 && $annualLeave) {
            // Request 2: Pending annual leave (next week)
            $leaveRequests[] = [
                'employee_id' => $employees[1]->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => now()->addDays(7)->format('Y-m-d'),
                'end_date' => now()->addDays(9)->format('Y-m-d'),
                'reason' => 'Acara pernikahan saudara',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ];
        }

        if ($employees->count() >= 3 && $sickLeave) {
            // Request 3: Approved sick leave (yesterday)
            $leaveRequests[] = [
                'employee_id' => $employees[2]->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => now()->subDays(3)->format('Y-m-d'),
                'end_date' => now()->subDays(2)->format('Y-m-d'),
                'reason' => 'Demam dan flu',
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now()->subDays(3),
            ];
        }

        if ($employees->count() >= 4 && $annualLeave) {
            // Request 4: Rejected leave
            $leaveRequests[] = [
                'employee_id' => $employees[3]->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => now()->addDays(3)->format('Y-m-d'),
                'end_date' => now()->addDays(10)->format('Y-m-d'),
                'reason' => 'Liburan panjang',
                'status' => 'rejected',
                'approved_by' => $approver->id,
                'approved_at' => now()->subDays(1),
                'rejection_reason' => 'Jadwal proyek penting, mohon reschedule',
            ];
        }

        if ($employees->count() >= 5 && $annualLeave) {
            // Request 5: Needs HR Review (insufficient balance scenario)
            $leaveRequests[] = [
                'employee_id' => $employees[4]->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => now()->addDays(14)->format('Y-m-d'),
                'end_date' => now()->addDays(14)->format('Y-m-d'),
                'reason' => 'Keperluan pribadi',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
            ];
        }

        $requestsCreated = 0;
        $daysCreated = 0;
        $ledgerEntriesCreated = 0;

        foreach ($leaveRequests as $data) {
            // Check if similar request exists
            $exists = LeaveRequest::where('employee_id', $data['employee_id'])
                ->where('start_date', $data['start_date'])
                ->exists();

            if (!$exists) {
                // Calculate dates
                $startDate = Carbon::parse($data['start_date']);
                $endDate = Carbon::parse($data['end_date']);
                $period = CarbonPeriod::create($startDate, $endDate);
                $dates = [];
                foreach ($period as $date) {
                    $dates[] = $date->format('Y-m-d');
                }
                $totalDays = count($dates);

                // Create leave request
                $leaveRequest = LeaveRequest::create([
                    'employee_id' => $data['employee_id'],
                    'leave_type_id' => $data['leave_type_id'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'total_days' => $totalDays,
                    'reason' => $data['reason'],
                    'status' => $data['status'],
                    'approved_by' => $data['approved_by'] ?? null,
                    'approved_at' => $data['approved_at'] ?? null,
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                ]);
                $requestsCreated++;

                // ⭐ Create per-day records
                $dayStatus = match ($data['status']) {
                    'approved' => LeaveRequestDay::STATUS_APPROVED,
                    'cancelled' => LeaveRequestDay::STATUS_CANCELLED,
                    default => LeaveRequestDay::STATUS_PENDING,
                };

                foreach ($dates as $date) {
                    LeaveRequestDay::create([
                        'leave_request_id' => $leaveRequest->id,
                        'date' => $date,
                        'day_value' => 1.0,
                        'status' => $dayStatus,
                    ]);
                    $daysCreated++;
                }

                // ⭐ Update balance and create ledger for approved requests
                if ($data['status'] === 'approved') {
                    $balance = EmployeeLeaveBalance::where('employee_id', $data['employee_id'])
                        ->where('leave_type_id', $data['leave_type_id'])
                        ->where('year', $currentYear)
                        ->first();

                    if ($balance) {
                        $balance->increment('used', $totalDays);

                        LeaveBalanceTransaction::create([
                            'employee_leave_balance_id' => $balance->id,
                            'leave_request_id' => $leaveRequest->id,
                            'type' => LeaveBalanceTransaction::TYPE_DEDUCTION,
                            'amount' => -$totalDays,
                            'balance_after' => $balance->fresh()->remaining,
                            'description' => "Penggunaan cuti untuk request #{$leaveRequest->id}",
                            'created_by' => $data['approved_by'],
                        ]);
                        $ledgerEntriesCreated++;
                    }
                }

                $employee = Employee::find($data['employee_id']);
                $status = strtoupper($data['status']);
                $this->command->info("  ✅ Created: {$employee->full_name} - {$totalDays} days ({$status})");
            }
        }

        // Summary
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->table(
            ['Metric', 'Value'],
            [
                ['Leave Balances Created', $balancesCreated],
                ['Leave Requests Created', $requestsCreated],
                ['Leave Request Days Created', $daysCreated],
                ['Ledger Entries Created', $ledgerEntriesCreated + $balancesCreated],
                ['Pending Requests', LeaveRequest::pending()->count()],
                ['Approved Requests', LeaveRequest::approved()->count()],
                ['Rejected Requests', LeaveRequest::rejected()->count()],
            ]
        );

        // Show leave balance summary for first 5 employees
        $this->command->newLine();
        $this->command->info('📋 Leave Balance with Ledger (Annual Leave):');

        $balances = EmployeeLeaveBalance::with(['employee', 'leaveType', 'transactions'])
            ->whereHas('leaveType', fn($q) => $q->where('code', 'ANNUAL'))
            ->where('year', $currentYear)
            ->limit(5)
            ->get();

        $this->command->table(
            ['Employee', 'Quota', 'Used', 'Remaining', 'Ledger Entries'],
            $balances->map(fn($b) => [
                $b->employee->full_name,
                $b->quota,
                $b->used,
                $b->remaining,
                $b->transactions->count(),
            ])
        );

        $this->command->newLine();
        $this->command->info('✨ Leave requests with days and ledger seeded successfully!');
    }
}

