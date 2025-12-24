<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'type',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'work_hours_required',
        'late_tolerance_minutes',
        'is_overnight',
        'is_active',
        'working_days',
        'description',
    ];

    protected $casts = [
        'is_overnight' => 'boolean',
        'is_active' => 'boolean',
        'work_hours_required' => 'integer',
        'late_tolerance_minutes' => 'integer',
        'working_days' => 'array',
    ];

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    /**
     * Get formatted start time (HH:mm)
     */
    protected function formattedStartTime(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->start_time)->format('H:i')
        );
    }

    /**
     * Get formatted end time (HH:mm)
     */
    protected function formattedEndTime(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->end_time)->format('H:i')
        );
    }

    /**
     * Get formatted time range
     */
    protected function timeRange(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->formatted_start_time . ' - ' . $this->formatted_end_time
        );
    }

    /**
     * Get work hours in hours (decimal)
     */
    protected function workHours(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->work_hours_required / 60
        );
    }

    /**
     * Get formatted work hours (e.g., "8 jam")
     */
    protected function formattedWorkHours(): Attribute
    {
        return Attribute::make(
            get: function () {
                $hours = floor($this->work_hours_required / 60);
                $minutes = $this->work_hours_required % 60;

                if ($minutes > 0) {
                    return "{$hours} jam {$minutes} menit";
                }

                return "{$hours} jam";
            }
        );
    }

    /**
     * Get break duration in minutes
     */
    protected function breakDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->break_start || !$this->break_end) {
                    return 0;
                }

                $breakStart = Carbon::parse($this->break_start);
                $breakEnd = Carbon::parse($this->break_end);

                return $breakStart->diffInMinutes($breakEnd);
            }
        );
    }

    /**
     * Get type label
     */
    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type === 'fixed' ? 'Jam Tetap' : 'Jam Fleksibel'
        );
    }

    /**
     * Get type badge class
     */
    protected function typeBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->type === 'fixed'
            ? 'bg-blue-100 text-blue-700'
            : 'bg-purple-100 text-purple-700'
        );
    }

    /**
     * Get status badge class
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->is_active
            ? 'bg-green-100 text-green-700'
            : 'bg-gray-100 text-gray-700'
        );
    }

    /**
     * Get working days label (e.g., "Sen - Jum", "Sel - Sab")
     */
    protected function workingDaysLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->working_days || empty($this->working_days)) {
                    return 'Tidak ada';
                }

                $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
                $workingDays = $this->working_days;
                sort($workingDays);

                // Check if consecutive days
                $isConsecutive = true;
                for ($i = 1; $i < count($workingDays); $i++) {
                    if ($workingDays[$i] !== $workingDays[$i - 1] + 1) {
                        // Check for wrap-around (Sat-Sun-Mon pattern)
                        if (!($workingDays[$i - 1] === 6 && $workingDays[$i] === 0)) {
                            $isConsecutive = false;
                            break;
                        }
                    }
                }

                if ($isConsecutive && count($workingDays) > 1) {
                    return $dayNames[$workingDays[0]] . ' - ' . $dayNames[$workingDays[count($workingDays) - 1]];
                }

                // Not consecutive, list all days
                return implode(', ', array_map(fn($day) => $dayNames[$day], $workingDays));
            }
        );
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Calculate actual work duration (excluding break)
     */
    public function calculateWorkDuration(Carbon $clockIn, Carbon $clockOut): int
    {
        $totalMinutes = $clockIn->diffInMinutes($clockOut);

        // Subtract break duration if within work hours
        if ($this->break_start && $this->break_end) {
            $breakStart = Carbon::parse($clockIn->format('Y-m-d') . ' ' . $this->break_start);
            $breakEnd = Carbon::parse($clockIn->format('Y-m-d') . ' ' . $this->break_end);

            // Check if break overlaps with work hours
            if ($clockIn <= $breakEnd && $clockOut >= $breakStart) {
                $totalMinutes -= $this->break_duration;
            }
        }

        return max(0, $totalMinutes);
    }

    /**
     * Check if clock-in time is late
     */
    public function isLate(Carbon $clockInTime): bool
    {
        if ($this->type === 'flexible') {
            return false; // Flexible shift doesn't have late concept
        }

        $expectedStart = Carbon::parse($clockInTime->format('Y-m-d') . ' ' . $this->start_time);
        $lateThreshold = $expectedStart->copy()->addMinutes($this->late_tolerance_minutes);

        return $clockInTime->greaterThan($lateThreshold);
    }

    /**
     * Get late duration in minutes
     */
    public function getLateDuration(Carbon $clockInTime): int
    {
        if ($this->type === 'flexible' || !$this->isLate($clockInTime)) {
            return 0;
        }

        $expectedStart = Carbon::parse($clockInTime->format('Y-m-d') . ' ' . $this->start_time);
        $lateThreshold = $expectedStart->copy()->addMinutes($this->late_tolerance_minutes);

        return $clockInTime->diffInMinutes($lateThreshold);
    }

    /**
     * Check if shift is currently active (within shift hours)
     */
    public function isActiveNow(): bool
    {
        $now = now();
        $currentTime = $now->format('H:i:s');

        if ($this->is_overnight) {
            // Overnight shift: 22:00 - 06:00
            return $currentTime >= $this->start_time || $currentTime <= $this->end_time;
        }

        // Normal shift: 08:00 - 17:00
        return $currentTime >= $this->start_time && $currentTime <= $this->end_time;
    }

    /**
     * Get expected clock-out time for a given clock-in
     */
    public function getExpectedClockOut(Carbon $clockIn): Carbon
    {
        $expectedOut = Carbon::parse($clockIn->format('Y-m-d') . ' ' . $this->end_time);

        if ($this->is_overnight && $expectedOut <= $clockIn) {
            $expectedOut->addDay();
        }

        return $expectedOut;
    }

    /**
     * Check if work hours requirement is met
     */
    public function isWorkHoursMet(int $workedMinutes): bool
    {
        return $workedMinutes >= $this->work_hours_required;
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

    public function scopeFixed($query)
    {
        return $query->where('type', 'fixed');
    }

    public function scopeFlexible($query)
    {
        return $query->where('type', 'flexible');
    }

    public function scopeOvernight($query)
    {
        return $query->where('is_overnight', true);
    }

    /**
     * ========================================
     * STATIC METHODS
     * ========================================
     */

    /**
     * Generate unique shift code
     */
    public static function generateCode(): string
    {
        $lastShift = self::latest('id')->first();
        $sequence = $lastShift ? ((int) substr($lastShift->code, 2) + 1) : 1;

        return 'SH' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get default shift (usually regular day shift)
     */
    public static function getDefault()
    {
        return self::where('code', 'SH001')->first()
            ?? self::active()->first();
    }

    /**
     * Check if a given date is a working day for this shift
     */
    public function isWorkingDay(Carbon $date): bool
    {
        if (!$this->working_days || empty($this->working_days)) {
            // Default to Mon-Fri if not set
            return in_array($date->dayOfWeek, [1, 2, 3, 4, 5]);
        }

        return in_array($date->dayOfWeek, $this->working_days);
    }

    /**
     * Get all working days in a given month
     */
    public function getWorkingDaysInMonth(int $year, int $month): \Illuminate\Support\Collection
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $workingDates = collect();
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            if ($this->isWorkingDay($currentDate)) {
                $workingDates->push($currentDate->copy());
            }
            $currentDate->addDay();
        }

        return $workingDates;
    }

    /**
     * Get default working days (Mon-Fri)
     */
    public static function getDefaultWorkingDays(): array
    {
        return [1, 2, 3, 4, 5]; // Monday to Friday
    }
}