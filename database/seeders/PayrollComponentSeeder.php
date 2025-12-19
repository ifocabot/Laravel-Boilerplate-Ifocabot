<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PayrollComponentSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            // EARNINGS - Basic Salary
            [
                'code' => 'BASIC_SALARY',
                'name' => 'Gaji Pokok',
                'description' => 'Gaji pokok bulanan karyawan',
                'type' => 'earning',
                'category' => 'basic_salary',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => true,
                'display_order' => 1,
                'show_on_slip' => true,
                'is_active' => true,
            ],

            // EARNINGS - Fixed Allowances
            [
                'code' => 'TRANSPORT_ALLOWANCE',
                'name' => 'Tunjangan Transport',
                'description' => 'Tunjangan transport tetap bulanan',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'display_order' => 2,
                'show_on_slip' => true,
                'is_active' => true,
            ],
            [
                'code' => 'MEAL_ALLOWANCE',
                'name' => 'Tunjangan Makan',
                'description' => 'Tunjangan makan bulanan',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'display_order' => 3,
                'show_on_slip' => true,
                'is_active' => true,
            ],
            [
                'code' => 'POSITION_ALLOWANCE',
                'name' => 'Tunjangan Jabatan',
                'description' => 'Tunjangan berdasarkan posisi/jabatan',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'display_order' => 4,
                'show_on_slip' => true,
                'is_active' => true,
            ],

            // EARNINGS - Variable Allowances
            [
                'code' => 'OVERTIME',
                'name' => 'Lembur',
                'description' => 'Uang lembur (variabel)',
                'type' => 'earning',
                'category' => 'variable_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'display_order' => 5,
                'show_on_slip' => true,
                'is_active' => true,
            ],
            [
                'code' => 'BONUS',
                'name' => 'Bonus',
                'description' => 'Bonus kinerja/achievement',
                'type' => 'earning',
                'category' => 'variable_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'display_order' => 6,
                'show_on_slip' => true,
                'is_active' => true,
            ],

            // DEDUCTIONS - Statutory
            [
                'code' => 'TAX_PPH21',
                'name' => 'PPh 21',
                'description' => 'Pajak Penghasilan Pasal 21',
                'type' => 'deduction',
                'category' => 'statutory',
                'calculation_type' => 'formula',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 10,
                'show_on_slip' => true,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_TK_EMPLOYEE',
                'name' => 'BPJS Ketenagakerjaan (Karyawan)',
                'description' => 'Iuran BPJS TK yang dipotong dari gaji',
                'type' => 'deduction',
                'category' => 'statutory',
                'calculation_type' => 'percentage',
                'calculation_formula' => '2%', // 2% dari upah
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 11,
                'show_on_slip' => true,
                'is_active' => true,
            ],
            [
                'code' => 'BPJS_KES_EMPLOYEE',
                'name' => 'BPJS Kesehatan (Karyawan)',
                'description' => 'Iuran BPJS Kesehatan yang dipotong dari gaji',
                'type' => 'deduction',
                'category' => 'statutory',
                'calculation_type' => 'percentage',
                'calculation_formula' => '1%', // 1% dari upah
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 12,
                'show_on_slip' => true,
                'is_active' => true,
            ],

            // DEDUCTIONS - Other
            [
                'code' => 'LOAN_INSTALLMENT',
                'name' => 'Cicilan Pinjaman',
                'description' => 'Cicilan pinjaman karyawan',
                'type' => 'deduction',
                'category' => 'other_deduction',
                'calculation_type' => 'fixed',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 15,
                'show_on_slip' => true,
                'is_active' => true,
            ],
            [
                'code' => 'COOPERATIVE',
                'name' => 'Koperasi',
                'description' => 'Simpanan/cicilan koperasi',
                'type' => 'deduction',
                'category' => 'other_deduction',
                'calculation_type' => 'fixed',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 16,
                'show_on_slip' => true,
                'is_active' => true,
            ],
            [
                'code' => 'LATE_DEDUCTION',
                'name' => 'Potongan Keterlambatan',
                'description' => 'Potongan karena terlambat/absen',
                'type' => 'deduction',
                'category' => 'other_deduction',
                'calculation_type' => 'fixed',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 17,
                'show_on_slip' => true,
                'is_active' => true,
            ],
        ];

        foreach ($components as $component) {
            DB::table('payroll_components')->insert(array_merge($component, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}