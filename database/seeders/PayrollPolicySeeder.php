<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollPolicy;

class PayrollPolicySeeder extends Seeder
{
    /**
     * Seed default company-wide policies
     */
    public function run(): void
    {
        $effectiveFrom = '2024-01-01';

        $policies = [
            [
                'key' => 'late.penalty_per_minute',
                'value' => '1000',
                'description' => 'Potongan per menit keterlambatan (Rp)',
            ],
            [
                'key' => 'overtime.multiplier',
                'value' => '1.5',
                'description' => 'Multiplier upah lembur (1.5x = 150%)',
            ],
            [
                'key' => 'overtime.hourly_rate',
                'value' => '10000',
                'description' => 'Fixed rate lembur per jam (Rp), null = hitung dari gaji',
            ],
            [
                'key' => 'work.standard_monthly_hours',
                'value' => '173',
                'description' => 'Jam kerja standar per bulan untuk hitung hourly rate',
            ],
            [
                'key' => 'bpjs.jkk_risk_class',
                'value' => 'low',
                'description' => 'Default JKK risk class: very_low, low, medium, high, very_high',
            ],
        ];

        foreach ($policies as $policy) {
            PayrollPolicy::firstOrCreate(
                [
                    'key' => $policy['key'],
                    'scope_type' => 'company',
                    'scope_id' => null,
                    'effective_from' => $effectiveFrom,
                ],
                [
                    'value' => $policy['value'],
                    'description' => $policy['description'],
                ]
            );
        }
    }
}
