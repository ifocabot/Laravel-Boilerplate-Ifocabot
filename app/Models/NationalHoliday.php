<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class NationalHoliday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'is_recurring',
        'description',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    /**
     * Get formatted date (Indonesian)
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->translatedFormat('d F Y')
        );
    }

    /**
     * Get day name (Indonesian)
     */
    protected function dayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->translatedFormat('l')
        );
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Check if holiday falls in given month
     */
    public function isInMonth(int $year, int $month): bool
    {
        return $this->date->year === $year && $this->date->month === $month;
    }

    /**
     * Get all active holidays in a specific month
     */
    public static function getHolidaysInMonth(int $year, int $month)
    {
        return self::active()
            ->forMonth($year, $month)
            ->orderBy('date')
            ->get();
    }

    /**
     * Copy recurring holidays to a new year
     */
    public static function copyRecurringToYear(int $targetYear): int
    {
        $recurringHolidays = self::recurring()->active()->get();
        $copied = 0;

        foreach ($recurringHolidays as $holiday) {
            // Create new date with same month/day but different year
            $newDate = Carbon::create(
                $targetYear,
                $holiday->date->month,
                $holiday->date->day
            );

            // Check if already exists
            $exists = self::where('date', $newDate)->exists();

            if (!$exists) {
                self::create([
                    'name' => $holiday->name,
                    'date' => $newDate,
                    'is_recurring' => true,
                    'description' => $holiday->description,
                    'is_active' => true,
                ]);
                $copied++;
            }
        }

        return $copied;
    }

    /**
     * Check if a specific date is a national holiday
     */
    public static function isHoliday(Carbon $date): bool
    {
        return self::active()
            ->where('date', $date->format('Y-m-d'))
            ->exists();
    }
}
