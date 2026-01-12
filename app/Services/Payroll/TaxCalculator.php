<?php

namespace App\Services\Payroll;

use App\Models\TaxTerBracket;
use App\Models\TaxPtkpRate;

/**
 * Tax Calculator Service
 * 
 * Calculates PPh 21 using TER (Tarif Efektif Rata-rata) method
 * Reference: PP 58/2023 - Effective January 2024
 * 
 * ENTERPRISE: All rates are now data-driven from database tables:
 * - tax_ter_brackets: TER rates per category (A/B/C) with effective dates
 * - tax_ptkp_rates: PTKP amounts per status with effective dates
 */
class TaxCalculator
{
    private ?string $asOfDate = null;

    /**
     * Set effective date for tax calculations
     * Used when calculating for a specific payroll period
     */
    public function asOf(string|\DateTimeInterface $date): self
    {
        $this->asOfDate = $date instanceof \DateTimeInterface
            ? $date->format('Y-m-d')
            : $date;
        return $this;
    }

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
        // Determine TER category from PTKP status
        $category = TaxPtkpRate::getTerCategory($ptkpStatus);

        // Get TER rate from database (data-driven, not hardcoded)
        $terRate = TaxTerBracket::getRate($category, $monthlyGross, $this->asOfDate);

        // Calculate tax
        $taxAmount = round($monthlyGross * $terRate, 0);

        // Apply non-NPWP surcharge (20% higher)
        if (!$hasNpwp && $taxAmount > 0) {
            $taxAmount = round($taxAmount * 1.2, 0);
        }

        // Get PTKP amount for reference
        $ptkpAmount = TaxPtkpRate::getAmount($ptkpStatus, $this->asOfDate);

        return [
            'gross_income' => $monthlyGross,
            'ptkp_status' => $ptkpStatus,
            'ptkp_amount' => $ptkpAmount,
            'ter_category' => $category,
            'ter_rate' => $terRate,
            'has_npwp' => $hasNpwp,
            'tax_amount' => $taxAmount,
            'method' => 'TER',
            'effective_date' => $this->asOfDate ?? now()->format('Y-m-d'),
        ];
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
        $ratePercent = round($taxResult['ter_rate'] * 100, 2);

        return [
            'code' => 'TAX_PPH21',
            'name' => "PPh 21 (TER {$ratePercent}%)",
            'category' => 'statutory',
            'type' => 'deduction',
            'amount' => $taxResult['tax_amount'],
            'meta' => [
                'method' => 'TER',
                'ter_category' => $taxResult['ter_category'],
                'ter_rate' => $taxResult['ter_rate'],
                'ptkp_status' => $taxResult['ptkp_status'],
                'ptkp_amount' => $taxResult['ptkp_amount'],
                'has_npwp' => $taxResult['has_npwp'],
            ],
        ];
    }

    /**
     * Calculate annual tax using progressive rates (for year-end reconciliation)
     * This is optional - used for SPT reconciliation
     */
    public function calculateAnnualTax(float $annualTaxableIncome): array
    {
        // Progressive rates per UU PPh
        $brackets = [
            ['max' => 60000000, 'rate' => 0.05],
            ['max' => 250000000, 'rate' => 0.15],
            ['max' => 500000000, 'rate' => 0.25],
            ['max' => 5000000000, 'rate' => 0.30],
            ['max' => PHP_INT_MAX, 'rate' => 0.35],
        ];

        $taxAmount = 0;
        $remaining = $annualTaxableIncome;
        $breakdown = [];
        $prevMax = 0;

        foreach ($brackets as $bracket) {
            $bracketWidth = $bracket['max'] - $prevMax;
            $taxableInBracket = min($remaining, $bracketWidth);

            if ($taxableInBracket > 0) {
                $taxInBracket = $taxableInBracket * $bracket['rate'];
                $taxAmount += $taxInBracket;
                $breakdown[] = [
                    'from' => $prevMax,
                    'to' => min($bracket['max'], $prevMax + $taxableInBracket),
                    'rate' => $bracket['rate'],
                    'tax' => round($taxInBracket, 0),
                ];
                $remaining -= $taxableInBracket;
            }

            if ($remaining <= 0)
                break;
            $prevMax = $bracket['max'];
        }

        return [
            'annual_taxable_income' => $annualTaxableIncome,
            'annual_tax' => round($taxAmount, 0),
            'breakdown' => $breakdown,
        ];
    }
}
