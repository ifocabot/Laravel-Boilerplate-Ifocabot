<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeSensitiveData;
use App\Models\Shift;

/**
 * Seeder 03: Test Employees
 * 
 * Creates diverse employee types for comprehensive testing.
 */
class Seed_03_EmployeesSeeder extends Seeder
{
    public function run(): void
    {
        $normalShift = Shift::where('code', 'NORMAL')->first();
        $overnightShift = Shift::where('code', 'OVERNIGHT')->first();

        $employees = [
            // Employee A - Normal Full Month
            [
                'employee' => [
                    'nik' => 'EMP-A-001',
                    'full_name' => 'Andi Saputra',
                    'email_corporate' => 'andi.saputra@company.test',
                    'phone_number' => '081234567001',
                    'gender' => 'male',
                    'date_of_birth' => '1990-01-15',
                    'place_of_birth' => 'Jakarta',
                    'marital_status' => 'single',
                    'religion' => 'islam',
                    'join_date' => '2024-01-01',
                    'status' => 'active',
                ],
                'sensitive' => [
                    'bank_name' => 'BCA',
                    'bank_account_number' => '1234567001',
                    'bank_account_holder' => 'Andi Saputra',
                    'npwp_number' => '12.345.678.9-001.000',
                    'bpjs_kes_number' => '0001234567001',
                    'bpjs_tk_number' => '0001234567001',
                    'tax_status' => 'TK/0',
                ],
                'basic_salary' => 5000000,
            ],

            // Employee B - Join Mid-Month (Jan 16)
            [
                'employee' => [
                    'nik' => 'EMP-B-002',
                    'full_name' => 'Budi Hartono',
                    'email_corporate' => 'budi.hartono@company.test',
                    'phone_number' => '081234567002',
                    'gender' => 'male',
                    'date_of_birth' => '1988-05-20',
                    'place_of_birth' => 'Bandung',
                    'marital_status' => 'married',
                    'religion' => 'islam',
                    'join_date' => '2026-01-16', // Mid-month join
                    'status' => 'active',
                ],
                'sensitive' => [
                    'bank_name' => 'BCA',
                    'bank_account_number' => '1234567002',
                    'bank_account_holder' => 'Budi Hartono',
                    'npwp_number' => '12.345.678.9-002.000',
                    'bpjs_kes_number' => '0001234567002',
                    'bpjs_tk_number' => '0001234567002',
                    'tax_status' => 'K/1',
                ],
                'basic_salary' => 5000000,
            ],

            // Employee C - Alpha & Late Issues
            [
                'employee' => [
                    'nik' => 'EMP-C-003',
                    'full_name' => 'Citra Dewi',
                    'email_corporate' => 'citra.dewi@company.test',
                    'phone_number' => '081234567003',
                    'gender' => 'female',
                    'date_of_birth' => '1992-08-10',
                    'place_of_birth' => 'Surabaya',
                    'marital_status' => 'single',
                    'religion' => 'islam',
                    'join_date' => '2023-06-01',
                    'status' => 'active',
                ],
                'sensitive' => [
                    'bank_name' => 'Mandiri',
                    'bank_account_number' => '1234567003',
                    'bank_account_holder' => 'Citra Dewi',
                    'npwp_number' => '12.345.678.9-003.000',
                    'bpjs_kes_number' => '0001234567003',
                    'bpjs_tk_number' => '0001234567003',
                    'tax_status' => 'TK/0',
                ],
                'basic_salary' => 5000000,
            ],

            // Employee D - Overnight Shift
            [
                'employee' => [
                    'nik' => 'EMP-D-004',
                    'full_name' => 'Dedi Prasetyo',
                    'email_corporate' => 'dedi.prasetyo@company.test',
                    'phone_number' => '081234567004',
                    'gender' => 'male',
                    'date_of_birth' => '1985-03-25',
                    'place_of_birth' => 'Semarang',
                    'marital_status' => 'married',
                    'religion' => 'islam',
                    'join_date' => '2024-06-01',
                    'status' => 'active',
                ],
                'sensitive' => [
                    'bank_name' => 'BRI',
                    'bank_account_number' => '1234567004',
                    'bank_account_holder' => 'Dedi Prasetyo',
                    'npwp_number' => '12.345.678.9-004.000',
                    'bpjs_kes_number' => '0001234567004',
                    'bpjs_tk_number' => '0001234567004',
                    'tax_status' => 'K/0',
                ],
                'basic_salary' => 6000000,
                'shift_code' => 'OVERNIGHT',
            ],

            // Employee E - Overtime Late Approve
            [
                'employee' => [
                    'nik' => 'EMP-E-005',
                    'full_name' => 'Eka Putra',
                    'email_corporate' => 'eka.putra@company.test',
                    'phone_number' => '081234567005',
                    'gender' => 'male',
                    'date_of_birth' => '1993-11-12',
                    'place_of_birth' => 'Yogyakarta',
                    'marital_status' => 'single',
                    'religion' => 'islam',
                    'join_date' => '2024-03-01',
                    'status' => 'active',
                ],
                'sensitive' => [
                    'bank_name' => 'BCA',
                    'bank_account_number' => '1234567005',
                    'bank_account_holder' => 'Eka Putra',
                    'npwp_number' => '12.345.678.9-005.000',
                    'bpjs_kes_number' => '0001234567005',
                    'bpjs_tk_number' => '0001234567005',
                    'tax_status' => 'TK/0',
                ],
                'basic_salary' => 5000000,
            ],

            // Employee F - Late Waived After Lock
            [
                'employee' => [
                    'nik' => 'EMP-F-006',
                    'full_name' => 'Fitri Handayani',
                    'email_corporate' => 'fitri.handayani@company.test',
                    'phone_number' => '081234567006',
                    'gender' => 'female',
                    'date_of_birth' => '1991-07-08',
                    'place_of_birth' => 'Malang',
                    'marital_status' => 'married',
                    'religion' => 'islam',
                    'join_date' => '2024-02-01',
                    'status' => 'active',
                ],
                'sensitive' => [
                    'bank_name' => 'Mandiri',
                    'bank_account_number' => '1234567006',
                    'bank_account_holder' => 'Fitri Handayani',
                    'npwp_number' => '12.345.678.9-006.000',
                    'bpjs_kes_number' => '0001234567006',
                    'bpjs_tk_number' => '0001234567006',
                    'tax_status' => 'K/2',
                ],
                'basic_salary' => 5000000,
            ],

            // Employee G - Low Salary / Negative Net
            [
                'employee' => [
                    'nik' => 'EMP-G-007',
                    'full_name' => 'Gilang Ramadhan',
                    'email_corporate' => 'gilang.ramadhan@company.test',
                    'phone_number' => '081234567007',
                    'gender' => 'male',
                    'date_of_birth' => '2000-01-20',
                    'place_of_birth' => 'Bekasi',
                    'marital_status' => 'single',
                    'religion' => 'islam',
                    'join_date' => '2025-06-01',
                    'status' => 'active',
                ],
                'sensitive' => [
                    'bank_name' => 'BNI',
                    'bank_account_number' => '1234567007',
                    'bank_account_holder' => 'Gilang Ramadhan',
                    'npwp_number' => null, // No NPWP
                    'bpjs_kes_number' => '0001234567007',
                    'bpjs_tk_number' => '0001234567007',
                    'tax_status' => 'TK/0',
                ],
                'basic_salary' => 2000000,
            ],
        ];

        foreach ($employees as $empData) {
            $employee = Employee::updateOrCreate(
                ['nik' => $empData['employee']['nik']],
                $empData['employee']
            );

            // Update sensitive data (created automatically via boot)
            if ($employee->sensitiveData) {
                $employee->sensitiveData->update($empData['sensitive']);
            } else {
                EmployeeSensitiveData::create(
                    array_merge($empData['sensitive'], ['employee_id' => $employee->id])
                );
            }

            // Store basic salary in cache for subsequent seeders
            cache()->put("test_employee_{$employee->nik}_basic_salary", $empData['basic_salary'], now()->addHour());
        }

        $this->command->info('✅ Seed_03: Employees seeded (' . count($employees) . ' employees)');
    }
}
