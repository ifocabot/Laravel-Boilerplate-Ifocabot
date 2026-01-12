<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Master Seeder for Payroll Testing
 * 
 * Runs all seeders in the correct order for a complete test environment.
 * 
 * Usage:
 *   php artisan db:seed --class=PayrollTestSeeder
 */
class PayrollTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════════════════╗');
        $this->command->info('║     PAYROLL TEST SEEDER - Enterprise Test Suite       ║');
        $this->command->info('╚════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Run seeders in order
        $seeders = [
                // Tax compliance (required for tax calculation)
            TaxComplianceSeeder::class,
            PayrollPolicySeeder::class,

                // Step 0: Master/Reference data
            Seed_00_MasterReferenceSeeder::class,

                // Step 1: Payroll components
            Seed_01_PayrollComponentsSeeder::class,

                // Step 2: Payroll periods
            Seed_02_PayrollPeriodsSeeder::class,

                // Step 3: Employees
            Seed_03_EmployeesSeeder::class,

                // Step 4: Employee careers
            Seed_04_EmployeeCareerSeeder::class,

                // Step 5: Employee contracts
            Seed_05_EmployeeContractSeeder::class,

                // Step 5b: Employee payroll components
            Seed_05b_EmployeePayrollComponentsSeeder::class,

                // Step 6: Attendance events (with rebuild)
            Seed_06_AttendanceEventsSeeder::class,

                // Step 7: Payroll adjustments
            Seed_07_PayrollAdjustmentsSeeder::class,

                // Step 8: Payroll run (generate slips)
            Seed_08_PayrollRunSeeder::class,

                // Step 9: Expected results (JSON fixtures)
            Seed_09_PayrollExpectedResultsSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->command->info('');
            $this->call($seeder);
        }

        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════════════════╗');
        $this->command->info('║     ✅ All seeders completed successfully!             ║');
        $this->command->info('╚════════════════════════════════════════════════════════╝');
        $this->command->info('');
        $this->command->info('Test employees created:');
        $this->command->info('  EMP-A-001: Normal full month');
        $this->command->info('  EMP-B-002: Join mid-month (proration)');
        $this->command->info('  EMP-C-003: Alpha + late issues');
        $this->command->info('  EMP-D-004: Overnight shift');
        $this->command->info('  EMP-E-005: Overtime late approve');
        $this->command->info('  EMP-F-006: Late waived after lock');
        $this->command->info('  EMP-G-007: Low salary (excess deduction)');
        $this->command->info('');
        $this->command->info('Expected results: tests/fixtures/payroll/expected_2026_01.json');
        $this->command->info('');
    }
}
