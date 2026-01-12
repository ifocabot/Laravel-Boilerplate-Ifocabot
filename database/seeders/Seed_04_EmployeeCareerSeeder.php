<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeCareer;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;

/**
 * Seeder 04: Employee Careers
 * 
 * Links employees to departments, positions, and levels.
 */
class Seed_04_EmployeeCareerSeeder extends Seeder
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

        $itDept = Department::where('code', 'IT')->first();
        $hrDept = Department::where('code', 'HRD')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        $staffPos = Position::where('name', 'Staff')->first();
        $srStaffPos = Position::where('name', 'Senior Staff')->first();
        $spvPos = Position::where('name', 'Supervisor')->first();
        $jrStaffPos = Position::where('name', 'Junior Staff')->first();

        $l3 = Level::where('grade_code', 'L3')->first();
        $l4 = Level::where('grade_code', 'L4')->first();
        $l2 = Level::where('grade_code', 'L2')->first();
        $l1 = Level::where('grade_code', 'L1')->first();

        $careerMap = [
            'EMP-A-001' => ['dept' => $itDept, 'pos' => $staffPos, 'level' => $l3, 'start' => '2024-01-01'],
            'EMP-B-002' => ['dept' => $hrDept, 'pos' => $staffPos, 'level' => $l3, 'start' => '2026-01-16'],
            'EMP-C-003' => ['dept' => $itDept, 'pos' => $srStaffPos, 'level' => $l4, 'start' => '2023-06-01'],
            'EMP-D-004' => ['dept' => $opsDept, 'pos' => $staffPos, 'level' => $l3, 'start' => '2024-06-01'],
            'EMP-E-005' => ['dept' => $itDept, 'pos' => $staffPos, 'level' => $l3, 'start' => '2024-03-01'],
            'EMP-F-006' => ['dept' => $hrDept, 'pos' => $staffPos, 'level' => $l3, 'start' => '2024-02-01'],
            'EMP-G-007' => ['dept' => $opsDept, 'pos' => $jrStaffPos, 'level' => $l1, 'start' => '2025-06-01'],
        ];

        foreach ($employees as $employee) {
            $mapping = $careerMap[$employee->nik] ?? null;
            if (!$mapping)
                continue;

            EmployeeCareer::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'start_date' => $mapping['start'],
                ],
                [
                    'employee_id' => $employee->id,
                    'department_id' => $mapping['dept']?->id,
                    'position_id' => $mapping['pos']?->id,
                    'level_id' => $mapping['level']?->id,
                    'start_date' => $mapping['start'],
                    'end_date' => null, // Current career
                    'is_current' => true,
                ]
            );
        }

        $this->command->info('✅ Seed_04: Employee careers seeded');
    }
}
