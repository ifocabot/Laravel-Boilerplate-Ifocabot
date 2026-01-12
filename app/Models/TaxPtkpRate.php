<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxPtkpRate extends Model
{
    protected $fillable = [
        'status',
        'amount',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get PTKP amount for status
     */
    public static function getAmount(string $status, $asOfDate = null): float
    {
        $date = $asOfDate ?? now();

        $rate = self::where('status', $status)
            ->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date);
            })
            ->orderBy('effective_from', 'desc')
            ->first();

        // Default fallback if not found
        if (!$rate) {
            return match ($status) {
                'TK/0' => 54000000,
                'K/0' => 58500000,
                'K/1' => 63000000,
                'K/2' => 67500000,
                'K/3' => 72000000,
                default => 54000000,
            };
        }

        return (float) $rate->amount;
    }

    /**
     * Determine TER category from PTKP status
     */
    public static function getTerCategory(string $status): string
    {
        return match ($status) {
            'TK/0', 'TK/1' => 'A',
            'K/0', 'K/1', 'K/2' => 'B',
            'K/3' => 'C',
            default => 'A',
        };
    }
}
