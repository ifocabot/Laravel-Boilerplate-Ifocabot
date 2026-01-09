<?php

namespace App\Services\Payroll;

/**
 * Tax Calculator Service
 * 
 * Calculates PPh 21 using TER (Tarif Efektif Rata-rata) method
 * Reference: PP 58/2023 - Effective January 2024
 */
class TaxCalculator
{
    // PTKP Values 2024
    private const PTKP = [
        'TK/0' => 54000000,   // Single, no dependents
        'TK/1' => 58500000,   // Single, 1 dependent
        'TK/2' => 63000000,   // Single, 2 dependents
        'TK/3' => 67500000,   // Single, 3 dependents
        'K/0' => 58500000,    // Married, no dependents
        'K/1' => 63000000,    // Married, 1 dependent
        'K/2' => 67500000,    // Married, 2 dependents
        'K/3' => 72000000,    // Married, 3 dependents
        'K/I/0' => 112500000, // Married, spouse works, no dependents
        'K/I/1' => 117000000, // Married, spouse works, 1 dependent
        'K/I/2' => 121500000, // Married, spouse works, 2 dependents
        'K/I/3' => 126000000, // Married, spouse works, 3 dependents
    ];

    // TER (Tarif Efektif Rata-rata) Rates - Simplified monthly calculation
    // Category A: TK/0, TK/1, K/0
    // Category B: TK/2, TK/3, K/1, K/2
    // Category C: K/3, K/I/0, K/I/1, K/I/2, K/I/3
    private const TER_RATES = [
        'A' => [
            ['min' => 0, 'max' => 5400000, 'rate' => 0],
            ['min' => 5400001, 'max' => 5650000, 'rate' => 0.0025],
            ['min' => 5650001, 'max' => 5950000, 'rate' => 0.005],
            ['min' => 5950001, 'max' => 6300000, 'rate' => 0.0075],
            ['min' => 6300001, 'max' => 6750000, 'rate' => 0.01],
            ['min' => 6750001, 'max' => 7500000, 'rate' => 0.0125],
            ['min' => 7500001, 'max' => 8550000, 'rate' => 0.015],
            ['min' => 8550001, 'max' => 9650000, 'rate' => 0.0175],
            ['min' => 9650001, 'max' => 10050000, 'rate' => 0.02],
            ['min' => 10050001, 'max' => 10350000, 'rate' => 0.0225],
            ['min' => 10350001, 'max' => 10700000, 'rate' => 0.025],
            ['min' => 10700001, 'max' => 11050000, 'rate' => 0.03],
            ['min' => 11050001, 'max' => 11600000, 'rate' => 0.035],
            ['min' => 11600001, 'max' => 12500000, 'rate' => 0.04],
            ['min' => 12500001, 'max' => 13750000, 'rate' => 0.05],
            ['min' => 13750001, 'max' => 15100000, 'rate' => 0.06],
            ['min' => 15100001, 'max' => 16950000, 'rate' => 0.07],
            ['min' => 16950001, 'max' => 19750000, 'rate' => 0.08],
            ['min' => 19750001, 'max' => 24150000, 'rate' => 0.09],
            ['min' => 24150001, 'max' => 26450000, 'rate' => 0.10],
            ['min' => 26450001, 'max' => 28000000, 'rate' => 0.11],
            ['min' => 28000001, 'max' => 30050000, 'rate' => 0.12],
            ['min' => 30050001, 'max' => 32400000, 'rate' => 0.13],
            ['min' => 32400001, 'max' => 35400000, 'rate' => 0.14],
            ['min' => 35400001, 'max' => 39100000, 'rate' => 0.15],
            ['min' => 39100001, 'max' => 43850000, 'rate' => 0.16],
            ['min' => 43850001, 'max' => 47800000, 'rate' => 0.17],
            ['min' => 47800001, 'max' => 51400000, 'rate' => 0.18],
            ['min' => 51400001, 'max' => 56300000, 'rate' => 0.19],
            ['min' => 56300001, 'max' => 62200000, 'rate' => 0.20],
            ['min' => 62200001, 'max' => 68600000, 'rate' => 0.21],
            ['min' => 68600001, 'max' => 77500000, 'rate' => 0.22],
            ['min' => 77500001, 'max' => 89000000, 'rate' => 0.23],
            ['min' => 89000001, 'max' => 103000000, 'rate' => 0.24],
            ['min' => 103000001, 'max' => 125000000, 'rate' => 0.25],
            ['min' => 125000001, 'max' => 157000000, 'rate' => 0.26],
            ['min' => 157000001, 'max' => 206000000, 'rate' => 0.27],
            ['min' => 206000001, 'max' => 337000000, 'rate' => 0.28],
            ['min' => 337000001, 'max' => 454000000, 'rate' => 0.29],
            ['min' => 454000001, 'max' => 550000000, 'rate' => 0.30],
            ['min' => 550000001, 'max' => 695000000, 'rate' => 0.31],
            ['min' => 695000001, 'max' => 910000000, 'rate' => 0.32],
            ['min' => 910000001, 'max' => 1400000000, 'rate' => 0.33],
            ['min' => 1400000001, 'max' => PHP_INT_MAX, 'rate' => 0.34],
        ],
        // Category B & C use similar structure but with different thresholds
        // For simplicity, using Category A as base
        'B' => [], // Will use A with adjustment
        'C' => [], // Will use A with adjustment
    ];

    /**
     * Calculate monthly PPh 21 using TER method
     * 
     * @param float $monthlyGross - Monthly gross income (bruto)
     * @param string $ptkpStatus - PTKP status (TK/0, K/1, etc.)
     * @param bool $hasNpwp - Has NPWP (if false, +20% surcharge)
     * @return array
     */
    public function calculatePph21(float $monthlyGross, string $ptkpStatus = 'TK/0', bool $hasNpwp = true): array
    {
        // Determine TER category
        $category = $this->getTerCategory($ptkpStatus);

        // Get TER rate based on monthly gross
        $terRate = $this->getTerRate($monthlyGross, $category);

        // Calculate tax
        $taxAmount = round($monthlyGross * $terRate, 0);

        // Apply non-NPWP surcharge (20% higher)
        if (!$hasNpwp && $taxAmount > 0) {
            $taxAmount = round($taxAmount * 1.2, 0);
        }

        return [
            'gross_income' => $monthlyGross,
            'ptkp_status' => $ptkpStatus,
            'ptkp_amount' => self::PTKP[$ptkpStatus] ?? self::PTKP['TK/0'],
            'ter_category' => $category,
            'ter_rate' => $terRate,
            'has_npwp' => $hasNpwp,
            'tax_amount' => $taxAmount,
        ];
    }

    /**
     * Get TER category based on PTKP status
     */
    private function getTerCategory(string $ptkpStatus): string
    {
        return match ($ptkpStatus) {
            'TK/0', 'TK/1', 'K/0' => 'A',
            'TK/2', 'TK/3', 'K/1', 'K/2' => 'B',
            default => 'C',
        };
    }

    /**
     * Get TER rate based on monthly gross income
     */
    private function getTerRate(float $monthlyGross, string $category): float
    {
        $rates = self::TER_RATES['A']; // Using A as base for all categories

        // Adjust thresholds for Category B & C (simplified approach)
        $adjustment = match ($category) {
            'B' => 1.1,  // 10% higher threshold
            'C' => 1.2,  // 20% higher threshold
            default => 1.0,
        };

        foreach ($rates as $bracket) {
            $adjustedMin = $bracket['min'] * $adjustment;
            $adjustedMax = $bracket['max'] * $adjustment;

            if ($monthlyGross >= $adjustedMin && $monthlyGross <= $adjustedMax) {
                return $bracket['rate'];
            }
        }

        // Default: highest rate
        return 0.34;
    }

    /**
     * Legacy method for backward compatibility
     */
    public function calculate(float $taxableIncome, string $taxStatus = 'TK/0'): float
    {
        $result = $this->calculatePph21($taxableIncome, $taxStatus, true);
        return $result['tax_amount'];
    }

    /**
     * Get deduction item for slip
     */
    public function getDeductionItem(array $taxResult): array
    {
        return [
            'code' => 'TAX_PPH21',
            'name' => 'PPh 21 (TER ' . ($taxResult['ter_rate'] * 100) . '%)',
            'category' => 'statutory',
            'type' => 'deduction',
            'amount' => $taxResult['tax_amount'],
        ];
    }
}
