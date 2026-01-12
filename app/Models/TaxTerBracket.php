<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxTerBracket extends Model
{
    protected $fillable = [
        'category',
        'min_income',
        'max_income',
        'rate',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'min_income' => 'decimal:2',
        'max_income' => 'decimal:2',
        'rate' => 'decimal:4',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get effective rate for category and income
     */
    public static function getRate(string $category, float $monthlyIncome, $asOfDate = null): float
    {
        $date = $asOfDate ?? now();

        $bracket = self::where('category', $category)
            ->where('min_income', '<=', $monthlyIncome)
            ->where(function ($q) use ($monthlyIncome) {
                $q->whereNull('max_income')
                    ->orWhere('max_income', '>=', $monthlyIncome);
            })
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('min_income', 'desc')
            ->first();

        return (float) ($bracket?->rate ?? 0);
    }

    /**
     * Get all active brackets for a category
     */
    public static function getActiveBrackets(string $category, $asOfDate = null): \Illuminate\Support\Collection
    {
        $date = $asOfDate ?? now();

        return self::where('category', $category)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('min_income')
            ->get();
    }
}
