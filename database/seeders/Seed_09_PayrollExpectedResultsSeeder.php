<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeder 09: Expected Results (JSON Fixtures)
 * 
 * Creates expected payroll results for golden tests.
 * Results are stored as JSON for easy comparison.
 */
class Seed_09_PayrollExpectedResultsSeeder extends Seeder
{
    public function run(): void
    {
        // Create fixtures directory
        $fixturesPath = base_path('tests/fixtures/payroll');
        if (!File::isDirectory($fixturesPath)) {
            File::makeDirectory($fixturesPath, 0755, true);
        }

        // ========================================
        // Expected results for January 2026
        // ========================================
        $expectedResults = [
            'period' => '2026-01',
            'config' => [
                'late_penalty_per_minute' => 1000,
                'overtime_hourly_rate' => 10000,
                'overtime_multiplier' => 1.5,
                'standard_monthly_hours' => 173,
                'work_days' => 22,
            ],
            'employees' => [
                // Employee A - Full month, perfect
                'EMP-A-001' => [
                    'scenario' => 'Full month, perfect attendance',
                    'basic_salary' => 5000000,
                    'present_days' => 22,
                    'late_days' => 0,
                    'alpha_days' => 0,
                    'overtime_hours' => 0,
                    'expected' => [
                        'gross_salary' => 5000000 + (22 * 30000) + (22 * 25000), // Basic + meal + transport
                        'late_deduction' => 0,
                        'absent_deduction' => 0,
                        'overtime_earning' => 0,
                        // BPJS employee: JHT 2%, JP 1%, KES 1% = 4%
                        'bpjs_employee' => 5000000 * 0.04,
                        // Tax will vary based on gross
                        'net_range' => [4500000, 5500000],
                    ],
                ],

                // Employee B - Join mid-month
                'EMP-B-002' => [
                    'scenario' => 'Join mid-month (Jan 16)',
                    'basic_salary' => 5000000,
                    'present_days' => 12, // Approximately half month
                    'proration_factor' => 12 / 22,
                    'expected' => [
                        'gross_salary_range' => [2500000, 3500000], // Prorated
                        'late_deduction' => 0,
                        'absent_deduction' => 0,
                    ],
                ],

                // Employee C - Alpha & Late
                'EMP-C-003' => [
                    'scenario' => 'Alpha 2 days, Late 3 days (30 min each)',
                    'basic_salary' => 5000000,
                    'present_days' => 17, // 22 - 2 alpha - 3 late counted as present
                    'late_days' => 3,
                    'late_minutes_total' => 90, // 3 * 30
                    'alpha_days' => 2,
                    'expected' => [
                        'late_deduction' => 90 * 1000, // 90,000
                        'absent_deduction' => 2 * (5000000 / 22), // ~454,545
                        'allowance_days' => 20, // 22 - 2 alpha
                    ],
                ],

                // Employee D - Overnight shift
                'EMP-D-004' => [
                    'scenario' => 'Overnight shift (22:00 - 06:00)',
                    'basic_salary' => 6000000,
                    'present_days' => 22,
                    'late_minutes_total' => 5 * 22, // 5 min late each day
                    'expected' => [
                        'gross_salary' => 6000000 + (22 * 30000) + (22 * 25000),
                        'late_deduction' => 110 * 1000, // 110,000
                    ],
                ],

                // Employee E - OT unapproved (will come in Feb)
                'EMP-E-005' => [
                    'scenario' => 'Overtime not yet approved',
                    'basic_salary' => 5000000,
                    'present_days' => 22,
                    'overtime_hours_unapproved' => 4,
                    'expected' => [
                        'overtime_earning' => 0, // Not approved yet
                        'gross_salary' => 5000000 + (22 * 30000) + (22 * 25000),
                    ],
                ],

                // Employee F - Late (will be waived in Feb)
                'EMP-F-006' => [
                    'scenario' => 'Late 60 min, will be waived later',
                    'basic_salary' => 5000000,
                    'present_days' => 22,
                    'late_minutes_total' => 60,
                    'expected' => [
                        'late_deduction' => 60 * 1000, // 60,000 (will be refunded in Feb)
                    ],
                ],

                // Employee G - Excess deduction
                'EMP-G-007' => [
                    'scenario' => 'Low salary, high late = excess deduction',
                    'basic_salary' => 2000000,
                    'present_days' => 22,
                    'late_minutes_total' => 11 * 45, // ~495 min
                    'expected' => [
                        'late_deduction' => 495 * 1000, // 495,000
                        'gross_salary' => 2000000 + (22 * 30000) + (22 * 25000), // 3,210,000
                        // If deductions exceed gross, net = 0 and excess tracked
                        'net_salary_min' => 0,
                        'has_excess_deduction' => false, // May or may not trigger
                    ],
                ],
            ],

            'adjustments_feb' => [
                'EMP-E-005' => [
                    'type' => 'overtime_correction',
                    'amount' => 4 * 10000 * 1.5, // 60,000
                    'description' => 'Jan OT approved in Feb',
                ],
                'EMP-F-006' => [
                    'type' => 'late_waive',
                    'amount' => 60 * 1000, // 60,000 refund
                    'description' => 'Jan late waived in Feb',
                ],
            ],
        ];

        // Write JSON file
        $jsonPath = $fixturesPath . '/expected_2026_01.json';
        File::put($jsonPath, json_encode($expectedResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->command->info("✅ Seed_09: Expected results written to {$jsonPath}");
    }
}
