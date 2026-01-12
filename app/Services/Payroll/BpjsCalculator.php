<?php

namespace App\Services\Payroll;

/**
 * BPJS Calculator Service
 * 
 * Calculates all BPJS components with proper rates and ceilings
 * Reference: Peraturan BPJS 2024
 */
class BpjsCalculator
{
    // BPJS Ketenagakerjaan Rates
    private const JHT_COMPANY = 0.037;     // 3.7%
    private const JHT_EMPLOYEE = 0.02;     // 2%
    private const JP_COMPANY = 0.02;       // 2%
    private const JP_EMPLOYEE = 0.01;      // 1%
    private const JKK_COMPANY = 0.0024;    // 0.24% - low risk (can vary)
    private const JKM_COMPANY = 0.003;     // 0.3%

    // BPJS Kesehatan Rates
    private const KES_COMPANY = 0.04;      // 4%
    private const KES_EMPLOYEE = 0.01;     // 1%

    // Ceilings (2024)
    private const JP_CEILING = 10042300;   // Ceiling for JP (Jaminan Pensiun)
    private const KES_CEILING = 12000000;  // Ceiling for BPJS Kesehatan

    /**
     * Calculate all BPJS components
     * 
     * @param float $baseSalary - Gross salary as BPJS base
     * @param string|null $jkkRiskClass - Risk class: 'very_low'|'low'|'medium'|'high'|'very_high'
     * @return array
     */
    public function calculate(float $baseSalary, ?string $jkkRiskClass = 'low'): array
    {
        // JHT - no ceiling
        $jht = [
            'company' => round($baseSalary * self::JHT_COMPANY, 0),
            'employee' => round($baseSalary * self::JHT_EMPLOYEE, 0),
        ];

        // JP - with ceiling
        $jpBase = min($baseSalary, self::JP_CEILING);
        $jp = [
            'company' => round($jpBase * self::JP_COMPANY, 0),
            'employee' => round($jpBase * self::JP_EMPLOYEE, 0),
        ];

        // JKK - company only, varies by risk class
        $jkkRate = $this->getJkkRate($jkkRiskClass);
        $jkk = [
            'company' => round($baseSalary * $jkkRate, 0),
            'employee' => 0,
        ];

        // JKM - company only
        $jkm = [
            'company' => round($baseSalary * self::JKM_COMPANY, 0),
            'employee' => 0,
        ];

        // BPJS Kesehatan - with ceiling
        $kesBase = min($baseSalary, self::KES_CEILING);
        $kes = [
            'company' => round($kesBase * self::KES_COMPANY, 0),
            'employee' => round($kesBase * self::KES_EMPLOYEE, 0),
        ];

        return [
            'jht' => $jht,
            'jp' => $jp,
            'jkk' => $jkk,
            'jkm' => $jkm,
            'kes' => $kes,
            // Summary for slip
            'total_company' => $jht['company'] + $jp['company'] + $jkk['company'] + $jkm['company'] + $kes['company'],
            'total_employee' => $jht['employee'] + $jp['employee'] + $kes['employee'],
            // Legacy format (for backward compatibility)
            'bpjs_tk_company' => $jht['company'] + $jp['company'] + $jkk['company'] + $jkm['company'],
            'bpjs_tk_employee' => $jht['employee'] + $jp['employee'],
            'bpjs_kes_company' => $kes['company'],
            'bpjs_kes_employee' => $kes['employee'],
        ];
    }

    /**
     * Get JKK rate by risk class
     * Supports both string ('low', 'medium') and integer (1-5) inputs
     */
    private function getJkkRate(string|int|null $riskClass): float
    {
        // Normalize risk class (handle integer input)
        $normalized = $this->normalizeRiskClass($riskClass);

        return match ($normalized) {
            'very_low' => 0.0024,  // 0.24%
            'low' => 0.0054,       // 0.54%
            'medium' => 0.0089,    // 0.89%
            'high' => 0.0127,      // 1.27%
            'very_high' => 0.0174, // 1.74%
            default => 0.0024,     // Default: very low risk
        };
    }

    /**
     * Normalize risk class to string format
     * Handles: integer 1-5, string names, null
     */
    private function normalizeRiskClass(string|int|null $riskClass): string
    {
        if ($riskClass === null) {
            return 'very_low';
        }

        // If already valid string, return as-is
        if (is_string($riskClass) && in_array($riskClass, ['very_low', 'low', 'medium', 'high', 'very_high'])) {
            return $riskClass;
        }

        // Convert integer to string
        return match ((int) $riskClass) {
            1 => 'very_low',
            2 => 'low',
            3 => 'medium',
            4 => 'high',
            5 => 'very_high',
            default => 'very_low',
        };
    }

    /**
     * Get breakdown for slip display
     */
    public function getDeductionItems(array $bpjs): array
    {
        $items = [];

        if ($bpjs['jht']['employee'] > 0) {
            $items[] = [
                'code' => 'BPJS_JHT',
                'name' => 'BPJS JHT (2%)',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $bpjs['jht']['employee'],
            ];
        }

        if ($bpjs['jp']['employee'] > 0) {
            $items[] = [
                'code' => 'BPJS_JP',
                'name' => 'BPJS JP (1%)',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $bpjs['jp']['employee'],
            ];
        }

        if ($bpjs['kes']['employee'] > 0) {
            $items[] = [
                'code' => 'BPJS_KES',
                'name' => 'BPJS Kesehatan (1%)',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $bpjs['kes']['employee'],
            ];
        }

        return $items;
    }
}
