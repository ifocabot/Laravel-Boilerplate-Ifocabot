<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollComponent;

class PayrollComponentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating/Updating Payroll Components with Variable Calculations...');

        $components = [
            // ============================================
            // EARNINGS
            // ============================================

            // Basic Salary
            [
                'code' => 'BASIC_SALARY',
                'name' => 'Gaji Pokok',
                'type' => 'earning',
                'category' => 'basic_salary', // ✅ FIXED
                'calculation_type' => 'fixed',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Gaji pokok bulanan tetap sesuai kontrak kerja',
                'description' => 'Gaji pokok karyawan per bulan',
                'is_taxable' => true,
                'is_bpjs_base' => true,
                'is_active' => true,
                'display_order' => 1,
                'show_on_slip' => true,
            ],

            // Meal Allowance - DAILY RATE
            [
                'code' => 'MEAL',
                'name' => 'Uang Makan',
                'type' => 'earning',
                'category' => 'variable_allowance', // ✅ FIXED (was 'allowance')
                'calculation_type' => 'daily_rate',
                'rate_per_day' => 50000.00,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Rp 50,000 per hari × jumlah hari hadir (termasuk WFH, dinas)',
                'description' => 'Tunjangan makan per hari kerja',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 10,
                'show_on_slip' => true,
            ],

            // Transport Allowance - DAILY RATE
            [
                'code' => 'TRANSPORT',
                'name' => 'Uang Transport',
                'type' => 'earning',
                'category' => 'variable_allowance', // ✅ FIXED (was 'allowance')
                'calculation_type' => 'daily_rate',
                'rate_per_day' => 30000.00,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Rp 30,000 per hari × jumlah hari hadir (tidak termasuk WFH)',
                'description' => 'Tunjangan transport per hari kerja',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 11,
                'show_on_slip' => true,
            ],

            // Position Allowance - PERCENTAGE
            [
                'code' => 'POSITION',
                'name' => 'Tunjangan Jabatan',
                'type' => 'earning',
                'category' => 'fixed_allowance', // ✅ FIXED (was 'allowance')
                'calculation_type' => 'percentage',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => 20.00,
                'calculation_notes' => '20% dari gaji pokok (untuk posisi manager ke atas)',
                'description' => 'Tunjangan jabatan berdasarkan persentase gaji pokok',
                'is_taxable' => true,
                'is_bpjs_base' => true,
                'is_active' => true,
                'display_order' => 5,
                'show_on_slip' => true,
            ],

            // Overtime Pay - HOURLY RATE
            [
                'code' => 'OVERTIME',
                'name' => 'Uang Lembur',
                'type' => 'earning',
                'category' => 'variable_allowance', // ✅ FIXED (was 'overtime')
                'calculation_type' => 'hourly_rate',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Formula: (Gaji Pokok / 173 jam) × 1.5 × jumlah jam lembur yang disetujui',
                'description' => 'Uang lembur per jam (1.5x upah per jam sesuai Depnaker)',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 15,
                'show_on_slip' => true,
            ],

            // Health Allowance - FIXED
            [
                'code' => 'HEALTH',
                'name' => 'Tunjangan Kesehatan',
                'type' => 'earning',
                'category' => 'fixed_allowance', // ✅ FIXED
                'calculation_type' => 'fixed',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Tunjangan kesehatan tetap per bulan (di luar BPJS)',
                'description' => 'Tunjangan kesehatan bulanan',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 6,
                'show_on_slip' => true,
            ],

            // Communication Allowance - FIXED
            [
                'code' => 'COMM',
                'name' => 'Tunjangan Komunikasi',
                'type' => 'earning',
                'category' => 'fixed_allowance', // ✅ FIXED
                'calculation_type' => 'fixed',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Tunjangan pulsa/internet tetap per bulan',
                'description' => 'Tunjangan pulsa dan internet bulanan',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 7,
                'show_on_slip' => true,
            ],

            // Performance Bonus - PERCENTAGE
            [
                'code' => 'PERFORMANCE',
                'name' => 'Bonus Kinerja',
                'type' => 'earning',
                'category' => 'variable_allowance', // ✅ FIXED (was 'bonus')
                'calculation_type' => 'percentage',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => 10.00,
                'calculation_notes' => '10% dari gaji pokok (diberikan berdasarkan penilaian kinerja)',
                'description' => 'Bonus kinerja bulanan/tahunan',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'is_active' => false,
                'display_order' => 20,
                'show_on_slip' => true,
            ],

            // ============================================
            // DEDUCTIONS
            // ============================================

            // Late Deduction - DAILY RATE
            [
                'code' => 'LATE_DED',
                'name' => 'Potongan Keterlambatan',
                'type' => 'deduction',
                'category' => 'other_deduction', // ✅ FIXED (was 'deduction')
                'calculation_type' => 'daily_rate',
                'rate_per_day' => 25000.00,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Rp 25,000 per hari terlambat (lebih dari 15 menit)',
                'description' => 'Potongan untuk keterlambatan',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 50,
                'show_on_slip' => true,
            ],

            // Absence Deduction - DAILY RATE
            [
                'code' => 'ABSENT_DED',
                'name' => 'Potongan Ketidakhadiran',
                'type' => 'deduction',
                'category' => 'other_deduction', // ✅ FIXED
                'calculation_type' => 'daily_rate',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Formula: (Gaji Pokok / 22 hari) × jumlah hari tidak hadir (alpha)',
                'description' => 'Potongan untuk ketidakhadiran tanpa keterangan',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 51,
                'show_on_slip' => true,
            ],

            // BPJS Ketenagakerjaan - PERCENTAGE
            [
                'code' => 'BPJS_TK',
                'name' => 'BPJS Ketenagakerjaan',
                'type' => 'deduction',
                'category' => 'statutory', // ✅ FIXED (was 'tax_deduction')
                'calculation_type' => 'percentage',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => 2.00,
                'calculation_notes' => '2% dari gaji pokok (ditanggung karyawan, total 5.7% termasuk perusahaan)',
                'description' => 'Iuran BPJS Ketenagakerjaan (JKK, JKM, JHT)',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 30,
                'show_on_slip' => true,
            ],

            // BPJS Kesehatan - PERCENTAGE
            [
                'code' => 'BPJS_KES',
                'name' => 'BPJS Kesehatan',
                'type' => 'deduction',
                'category' => 'statutory', // ✅ FIXED
                'calculation_type' => 'percentage',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => 1.00,
                'calculation_notes' => '1% dari gaji pokok (ditanggung karyawan, total 5% termasuk perusahaan)',
                'description' => 'Iuran BPJS Kesehatan',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 31,
                'show_on_slip' => true,
            ],

            // PPh 21 - PERCENTAGE
            [
                'code' => 'PPH21',
                'name' => 'PPh 21',
                'type' => 'deduction',
                'category' => 'statutory', // ✅ FIXED
                'calculation_type' => 'percentage',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => 5.00,
                'calculation_notes' => '5% dari penghasilan bruto (simplified, perhitungan sebenarnya lebih kompleks)',
                'description' => 'Pajak Penghasilan Pasal 21',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => true,
                'display_order' => 32,
                'show_on_slip' => true,
            ],

            // Loan Installment - FIXED
            [
                'code' => 'LOAN',
                'name' => 'Cicilan Pinjaman',
                'type' => 'deduction',
                'category' => 'other_deduction', // ✅ FIXED
                'calculation_type' => 'fixed',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Cicilan pinjaman karyawan (amount akan di-set per employee)',
                'description' => 'Cicilan pinjaman karyawan',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => false,
                'display_order' => 60,
                'show_on_slip' => true,
            ],

            // Cooperative Savings - FIXED
            [
                'code' => 'COOP',
                'name' => 'Simpanan Koperasi',
                'type' => 'deduction',
                'category' => 'other_deduction', // ✅ FIXED
                'calculation_type' => 'fixed',
                'rate_per_day' => null,
                'rate_per_hour' => null,
                'percentage_value' => null,
                'calculation_notes' => 'Simpanan wajib/sukarela koperasi karyawan',
                'description' => 'Iuran koperasi karyawan',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'is_active' => false,
                'display_order' => 61,
                'show_on_slip' => true,
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($components as $componentData) {
            $component = PayrollComponent::updateOrCreate(
                ['code' => $componentData['code']],
                $componentData
            );

            if ($component->wasRecentlyCreated) {
                $created++;
                $this->command->info("  ✅ Created: {$component->name} ({$component->code})");
            } else {
                $updated++;
                $this->command->warn("  🔄 Updated: {$component->name} ({$component->code})");
            }
        }

        $this->command->newLine();
        $this->command->info("📊 Summary:");
        $this->command->table(
            ['Status', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Total', $created + $updated],
            ]
        );

        $this->command->newLine();
        $this->command->info("📋 Components by Calculation Type:");

        $byType = PayrollComponent::selectRaw('calculation_type, count(*) as count')
            ->groupBy('calculation_type')
            ->get();

        $this->command->table(
            ['Calculation Type', 'Count'],
            $byType->map(fn($item) => [
                ucfirst(str_replace('_', ' ', $item->calculation_type)),
                $item->count
            ])
        );

        $this->command->newLine();
        $this->command->info("🎯 Component Details:");

        $all = PayrollComponent::orderBy('display_order')->get();

        $rows = [];
        foreach ($all as $comp) {
            $calculation = match ($comp->calculation_type) {
                'fixed' => 'Fixed',
                'daily_rate' => $comp->rate_per_day ? 'Rp ' . number_format($comp->rate_per_day) . '/day' : 'Calculated',
                'hourly_rate' => $comp->rate_per_hour ? 'Rp ' . number_format($comp->rate_per_hour) . '/hour' : 'Calculated',
                'percentage' => $comp->percentage_value . '%',
                default => '-'
            };

            $rows[] = [
                $comp->code,
                $comp->name,
                ucfirst($comp->type),
                $comp->category,
                $calculation,
                $comp->is_active ? '✓' : '✗',
            ];
        }

        $this->command->table(
            ['Code', 'Name', 'Type', 'Category', 'Calculation', 'Active'],
            $rows
        );

        $this->command->newLine();
        $this->command->info('✨ Payroll components seeded successfully!');
    }
}