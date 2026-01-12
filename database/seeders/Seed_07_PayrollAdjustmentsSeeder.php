<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollAdjustment;

/**
 * Seeder 07: Payroll Adjustments (Retroactive)
 * 
 * Creates adjustments for late approvals and waivers
 * that will be applied in future periods.
 */
class Seed_07_PayrollAdjustmentsSeeder extends Seeder
{
    public function run(): void
    {
        $janPeriod = PayrollPeriod::where('period_code', '2026-01')->first();
        $febPeriod = PayrollPeriod::where('period_code', '2026-02')->first();

        if (!$janPeriod || !$febPeriod) {
            $this->command->error('Periods not found!');
            return;
        }

        $employees = Employee::whereIn('nik', [
            'EMP-E-005',
            'EMP-F-006',
        ])->get()->keyBy('nik');

        // Employee E - Overtime approved late
        $empE = $employees['EMP-E-005'] ?? null;
        if ($empE) {
            PayrollAdjustment::updateOrCreate(
                [
                    'employee_id' => $empE->id,
                    'payroll_period_id' => $febPeriod->id,
                    'type' => PayrollAdjustment::TYPE_OVERTIME,
                    'source_date' => '2026-01-06',
                ],
                [
                    'employee_id' => $empE->id,
                    'payroll_period_id' => $febPeriod->id,
                    'source_period_id' => $janPeriod->id,
                    'type' => PayrollAdjustment::TYPE_OVERTIME,
                    'source_date' => '2026-01-06',
                    'amount_minutes' => 120,
                    'amount_money' => 30000,
                    'status' => PayrollAdjustment::STATUS_APPROVED,
                    'reason' => 'Overtime Jan 6 approved late in Feb',
                    'notes' => 'Auto-created by seeder',
                ]
            );

            PayrollAdjustment::updateOrCreate(
                [
                    'employee_id' => $empE->id,
                    'payroll_period_id' => $febPeriod->id,
                    'type' => PayrollAdjustment::TYPE_OVERTIME,
                    'source_date' => '2026-01-13',
                ],
                [
                    'employee_id' => $empE->id,
                    'payroll_period_id' => $febPeriod->id,
                    'source_period_id' => $janPeriod->id,
                    'type' => PayrollAdjustment::TYPE_OVERTIME,
                    'source_date' => '2026-01-13',
                    'amount_minutes' => 120,
                    'amount_money' => 30000,
                    'status' => PayrollAdjustment::STATUS_APPROVED,
                    'reason' => 'Overtime Jan 13 approved late in Feb',
                    'notes' => 'Auto-created by seeder',
                ]
            );

            $this->command->info("  - EMP-E: 2 overtime adjustments");
        }

        // Employee F - Late waived after lock
        $empF = $employees['EMP-F-006'] ?? null;
        if ($empF) {
            PayrollAdjustment::updateOrCreate(
                [
                    'employee_id' => $empF->id,
                    'payroll_period_id' => $febPeriod->id,
                    'type' => PayrollAdjustment::TYPE_LATE_CORRECTION,
                    'source_date' => '2026-01-02',
                ],
                [
                    'employee_id' => $empF->id,
                    'payroll_period_id' => $febPeriod->id,
                    'source_period_id' => $janPeriod->id,
                    'type' => PayrollAdjustment::TYPE_LATE_CORRECTION,
                    'source_date' => '2026-01-02',
                    'amount_minutes' => 60,
                    'amount_money' => 60000,
                    'status' => PayrollAdjustment::STATUS_APPROVED,
                    'reason' => 'Late on Jan 2 waived due to traffic emergency',
                    'notes' => 'Auto-created by seeder',
                ]
            );

            $this->command->info("  - EMP-F: 1 late waive adjustment");
        }

        $this->command->info('✅ Seed_07: Payroll adjustments seeded');
    }
}
