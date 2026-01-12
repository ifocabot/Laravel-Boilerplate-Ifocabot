<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollPeriod;

/**
 * Seeder 02: Payroll Periods
 * 
 * Create test periods for different scenarios.
 */
class Seed_02_PayrollPeriodsSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            // December 2025 - for backdated/adjustment testing
            [
                'period_code' => '2025-12',
                'period_name' => 'Payroll Desember 2025',
                'year' => 2025,
                'month' => 12,
                'start_date' => '2025-12-01',
                'end_date' => '2025-12-31',
                'payment_date' => '2026-01-05',
                'status' => 'paid',
                'attendance_locked' => true,
                'late_penalty_per_minute' => 1000,
                'standard_monthly_hours' => 173,
                'overtime_multiplier' => 1.5,
                'overtime_hourly_rate' => 10000,
            ],
            // January 2026 - main testing period
            [
                'period_code' => '2026-01',
                'period_name' => 'Payroll Januari 2026',
                'year' => 2026,
                'month' => 1,
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-31',
                'payment_date' => '2026-02-05',
                'status' => 'draft',
                'attendance_locked' => false,
                'late_penalty_per_minute' => 1000,
                'standard_monthly_hours' => 173,
                'overtime_multiplier' => 1.5,
                'overtime_hourly_rate' => 10000,
            ],
            // February 2026 - for adjustment apply testing
            [
                'period_code' => '2026-02',
                'period_name' => 'Payroll Februari 2026',
                'year' => 2026,
                'month' => 2,
                'start_date' => '2026-02-01',
                'end_date' => '2026-02-28',
                'payment_date' => '2026-03-05',
                'status' => 'draft',
                'attendance_locked' => false,
                'late_penalty_per_minute' => 1000,
                'standard_monthly_hours' => 173,
                'overtime_multiplier' => 1.5,
                'overtime_hourly_rate' => 10000,
            ],
            // March 2026 - future period
            [
                'period_code' => '2026-03',
                'period_name' => 'Payroll Maret 2026',
                'year' => 2026,
                'month' => 3,
                'start_date' => '2026-03-01',
                'end_date' => '2026-03-31',
                'payment_date' => '2026-04-05',
                'status' => 'draft',
                'attendance_locked' => false,
                'late_penalty_per_minute' => 1000,
                'standard_monthly_hours' => 173,
                'overtime_multiplier' => 2.0, // Different multiplier for testing
                'overtime_hourly_rate' => 15000,
            ],
        ];

        foreach ($periods as $period) {
            PayrollPeriod::firstOrCreate(
                ['period_code' => $period['period_code']],
                $period
            );
        }

        $this->command->info('✅ Seed_02: Payroll periods seeded (' . count($periods) . ' periods)');
    }
}
