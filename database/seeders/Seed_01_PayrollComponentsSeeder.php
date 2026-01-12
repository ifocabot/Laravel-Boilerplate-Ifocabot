<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollComponent;

/**
 * Seeder 01: Payroll Components
 * 
 * All payroll engine components - no hardcoding in calculator.
 */
class Seed_01_PayrollComponentsSeeder extends Seeder
{
    public function run(): void
    {
        $components = [
            // ========================================
            // EARNINGS
            // ========================================
            [
                'code' => 'BASIC_SALARY',
                'name' => 'Gaji Pokok',
                'type' => 'earning',
                'category' => 'basic_salary',
                'is_taxable' => true,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'fixed',
                'description' => 'Gaji pokok bulanan',
            ],
            [
                'code' => 'MEAL_ALLOWANCE',
                'name' => 'Tunjangan Makan',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'is_taxable' => true,
                'is_active' => true,
                'proration_type' => 'daily',
                'calculation_type' => 'daily_rate',
                'rate_per_day' => 30000,
                'description' => 'Tunjangan makan per hari kehadiran',
            ],
            [
                'code' => 'TRANSPORT_ALLOWANCE',
                'name' => 'Tunjangan Transport',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'is_taxable' => true,
                'is_active' => true,
                'proration_type' => 'daily',
                'calculation_type' => 'daily_rate',
                'rate_per_day' => 25000,
                'description' => 'Tunjangan transport per hari kehadiran',
            ],
            [
                'code' => 'POSITION_ALLOWANCE',
                'name' => 'Tunjangan Jabatan',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'is_taxable' => true,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'fixed',
                'description' => 'Tunjangan jabatan per bulan',
            ],
            [
                'code' => 'OVERTIME',
                'name' => 'Upah Lembur',
                'type' => 'earning',
                'category' => 'variable_allowance',
                'is_taxable' => true,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'formula',
                'formula_expression' => 'OT_HOURS * OT_RATE * OT_MULT',
                'description' => 'Upah lembur berdasarkan jam',
            ],

            // ========================================
            // DEDUCTIONS
            // ========================================
            [
                'code' => 'LATE_DEDUCTION',
                'name' => 'Potongan Keterlambatan',
                'type' => 'deduction',
                'category' => 'other_deduction',
                'is_taxable' => false,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'formula',
                'formula_expression' => 'LATE_MINUTES * LATE_RATE',
                'description' => 'Potongan per menit keterlambatan',
            ],
            [
                'code' => 'ABSENT_DEDUCTION',
                'name' => 'Potongan Alpha',
                'type' => 'deduction',
                'category' => 'other_deduction',
                'is_taxable' => false,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'formula',
                'formula_expression' => 'ABSENT_DAYS * DAILY_RATE',
                'description' => 'Potongan per hari alpha',
            ],

            // ========================================
            // BPJS EMPLOYEE (Potongan Karyawan)
            // ========================================
            [
                'code' => 'BPJS_JHT_EMP',
                'name' => 'BPJS JHT (Karyawan)',
                'type' => 'deduction',
                'category' => 'statutory',
                'is_taxable' => false,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'percentage',
                'percentage_value' => 0.02, // 2%
                'description' => 'BPJS Jaminan Hari Tua - bagian karyawan',
            ],
            [
                'code' => 'BPJS_JP_EMP',
                'name' => 'BPJS JP (Karyawan)',
                'type' => 'deduction',
                'category' => 'statutory',
                'is_taxable' => false,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'percentage',
                'percentage_value' => 0.01, // 1%
                'description' => 'BPJS Jaminan Pensiun - bagian karyawan',
            ],
            [
                'code' => 'BPJS_KES_EMP',
                'name' => 'BPJS Kesehatan (Karyawan)',
                'type' => 'deduction',
                'category' => 'statutory',
                'is_taxable' => false,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'percentage',
                'percentage_value' => 0.01, // 1%
                'description' => 'BPJS Kesehatan - bagian karyawan',
            ],

            // Note: BPJS Employer contributions (JHT_ER, JP_ER, JKK_ER, JKM_ER, KES_ER)
            // are info-only and not tracked in payroll_components.
            // They don't affect employee net pay.

            // ========================================
            // TAX
            // ========================================
            [
                'code' => 'TAX_PPH21',
                'name' => 'PPh 21',
                'type' => 'deduction',
                'category' => 'statutory',
                'is_taxable' => false,
                'is_active' => true,
                'proration_type' => 'none',
                'calculation_type' => 'ter', // TER method
                'description' => 'Pajak Penghasilan Pasal 21',
            ],
        ];

        foreach ($components as $comp) {
            PayrollComponent::firstOrCreate(['code' => $comp['code']], $comp);
        }

        $this->command->info('✅ Seed_01: Payroll components seeded (' . count($components) . ' components)');
    }
}
