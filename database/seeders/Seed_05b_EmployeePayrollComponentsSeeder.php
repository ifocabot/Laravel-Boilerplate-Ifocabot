<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\PayrollComponent;
use App\Models\EmployeePayrollComponent;

/**
 * Seeder 05b: Employee Payroll Components
 * 
 * Assigns payroll components (salary, allowances) to employees.
 */
class Seed_05b_EmployeePayrollComponentsSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::whereIn('nik', [
            'EMP-A-001',
            'EMP-B-002',
            'EMP-C-003',
            'EMP-D-004',
            'EMP-E-005',
            'EMP-F-006',
            'EMP-G-007',
        ])->get();

        $basicComp = PayrollComponent::where('code', 'BASIC_SALARY')->first();
        $mealComp = PayrollComponent::where('code', 'MEAL_ALLOWANCE')->first();
        $transportComp = PayrollComponent::where('code', 'TRANSPORT_ALLOWANCE')->first();

        $salaryMap = [
            'EMP-A-001' => 5000000,
            'EMP-B-002' => 5000000,
            'EMP-C-003' => 5000000,
            'EMP-D-004' => 6000000,
            'EMP-E-005' => 5000000,
            'EMP-F-006' => 5000000,
            'EMP-G-007' => 2000000,
        ];

        foreach ($employees as $employee) {
            $basicSalary = $salaryMap[$employee->nik] ?? 5000000;

            // Basic Salary
            if ($basicComp) {
                EmployeePayrollComponent::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'component_id' => $basicComp->id,
                    ],
                    [
                        'employee_id' => $employee->id,
                        'component_id' => $basicComp->id,
                        'amount' => $basicSalary,
                        'effective_from' => $employee->join_date,
                        'is_active' => true,
                        'is_recurring' => true,
                    ]
                );
            }

            // Meal Allowance (daily rate)
            if ($mealComp) {
                EmployeePayrollComponent::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'component_id' => $mealComp->id,
                    ],
                    [
                        'employee_id' => $employee->id,
                        'component_id' => $mealComp->id,
                        'amount' => 30000,
                        'effective_from' => $employee->join_date,
                        'is_active' => true,
                        'is_recurring' => true,
                    ]
                );
            }

            // Transport Allowance
            if ($transportComp) {
                EmployeePayrollComponent::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'component_id' => $transportComp->id,
                    ],
                    [
                        'employee_id' => $employee->id,
                        'component_id' => $transportComp->id,
                        'amount' => 25000,
                        'effective_from' => $employee->join_date,
                        'is_active' => true,
                        'is_recurring' => true,
                    ]
                );
            }
        }

        $this->command->info('✅ Seed_05b: Employee payroll components seeded');
    }
}
