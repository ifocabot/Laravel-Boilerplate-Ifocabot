<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeContract;

/**
 * Seeder 05: Employee Contracts
 * 
 * Creates permanent contracts for all test employees.
 */
class Seed_05_EmployeeContractSeeder extends Seeder
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

        foreach ($employees as $employee) {
            EmployeeContract::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'start_date' => $employee->join_date,
                ],
                [
                    'employee_id' => $employee->id,
                    'contract_number' => 'CTR-' . $employee->nik,
                    'type' => 'pkwtt', // Permanent
                    'start_date' => $employee->join_date,
                    'end_date' => null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Seed_05: Employee contracts seeded');
    }
}
