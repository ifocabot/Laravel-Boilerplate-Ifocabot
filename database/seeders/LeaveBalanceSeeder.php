<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveBalance;

class LeaveBalanceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Leave Balances...');

        $year = now()->year;
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();

        $count = 0;
        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                EmployeeLeaveBalance::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                    ],
                    [
                        'quota' => $leaveType->default_days ?? 12,
                        'used' => 0,
                        'carry_forward' => 0,
                    ]
                );
                $count++;
            }
        }

        $this->command->info("✅ {$count} leave balances created!");
    }
}
