<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'date',
        'is_day_off',
        'is_holiday',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_day_off' => 'boolean',
        'is_holiday' => 'boolean',
    ];

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

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
     * Get schedule type label
     */
    protected function scheduleTypeLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_holiday) {
                    return 'Libur Nasional';
                }
                if ($this->is_day_off) {
                    return 'Off/Libur';
                }
                return $this->shift ? $this->shift->name : 'Tidak Ada Shift';
            }
        );
    }

    /**
     * Get schedule type badge class
     */
    protected function scheduleTypeBadgeClass(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->is_holiday) {
                    return 'bg-red-100 text-red-700';
                }
                if ($this->is_day_off) {
                    return 'bg-gray-100 text-gray-700';
                }
                return 'bg-indigo-100 text-indigo-700';
            }
        );
    }

    /**
     * Check if working day
     */
    protected function isWorkingDay(): Attribute
    {
        return Attribute::make(
            get: fn() => !$this->is_day_off && !$this->is_holiday && $this->shift_id
        );
    }

    /**
     * Check if weekend
     */
    protected function isWeekend(): Attribute
    {
        return Attribute::make(
            get: fn() => in_array($this->date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])
        );
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeWorkingDays($query)
    {
        return $query->where('is_day_off', false)
            ->where('is_holiday', false)
            ->whereNotNull('shift_id');
    }

    public function scopeDayOffs($query)
    {
        return $query->where('is_day_off', true);
    }

    public function scopeHolidays($query)
    {
        return $query->where('is_holiday', true);
    }

    public function scopeWithShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    /**
     * ========================================
     * STATIC METHODS
     * ========================================
     */

    /**
     * Get or create schedule for employee on date
     */
    public static function getOrCreateSchedule($employeeId, $date, $shiftId = null)
    {
        return self::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date,
            ],
            [
                'shift_id' => $shiftId,
                'is_day_off' => false,
                'is_holiday' => false,
            ]
        );
    }

    /**
     * Generate schedules for month
     */
    public static function generateMonthSchedules($employeeId, $year, $month, $shiftId, $options = [])
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $schedules = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            // Skip if already exists
            $exists = self::where('employee_id', $employeeId)
                ->where('date', $currentDate)
                ->exists();

            if (!$exists) {
                $isWeekend = in_array($currentDate->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);

                $schedules[] = [
                    'employee_id' => $employeeId,
                    'shift_id' => $isWeekend && !($options['include_weekend'] ?? false) ? null : $shiftId,
                    'date' => $currentDate->format('Y-m-d'),
                    'is_day_off' => $isWeekend && !($options['include_weekend'] ?? false),
                    'is_holiday' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $currentDate->addDay();
        }

        if (!empty($schedules)) {
            self::insert($schedules);
        }

        return count($schedules);
    }

    /**
     * Copy schedule pattern
     */
    public static function copySchedulePattern($sourceEmployeeId, $targetEmployeeId, $startDate, $endDate)
    {
        $sourceSchedules = self::where('employee_id', $sourceEmployeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $copied = 0;
        foreach ($sourceSchedules as $source) {
            $exists = self::where('employee_id', $targetEmployeeId)
                ->where('date', $source->date)
                ->exists();

            if (!$exists) {
                self::create([
                    'employee_id' => $targetEmployeeId,
                    'shift_id' => $source->shift_id,
                    'date' => $source->date,
                    'is_day_off' => $source->is_day_off,
                    'is_holiday' => $source->is_holiday,
                    'notes' => 'Copied from ' . $source->employee->full_name,
                ]);
                $copied++;
            }
        }

        return $copied;
    }

    /**
     * Mark date as holiday for all employees
     */
    public static function markAsHoliday($date, $notes = null)
    {
        return self::where('date', $date)
            ->update([
                'is_holiday' => true,
                'notes' => $notes ?? 'Hari Libur Nasional',
            ]);
    }

    /**
     * Swap shifts between two employees
     */
    public static function swapShifts($employeeId1, $employeeId2, $date, $notes = null)
    {
        $schedule1 = self::where('employee_id', $employeeId1)->where('date', $date)->first();
        $schedule2 = self::where('employee_id', $employeeId2)->where('date', $date)->first();

        if (!$schedule1 || !$schedule2) {
            return false;
        }

        $tempShift = $schedule1->shift_id;

        $schedule1->update([
            'shift_id' => $schedule2->shift_id,
            'notes' => $notes ?? "Tukar shift dengan {$schedule2->employee->full_name}",
        ]);

        $schedule2->update([
            'shift_id' => $tempShift,
            'notes' => $notes ?? "Tukar shift dengan {$schedule1->employee->full_name}",
        ]);

        return true;
    }

    /**
     * Get schedule summary for month
     */
    public static function getMonthSummary($employeeId, $year, $month)
    {
        $schedules = self::where('employee_id', $employeeId)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return [
            'total_days' => $schedules->count(),
            'working_days' => $schedules->where('is_working_day', true)->count(),
            'day_offs' => $schedules->where('is_day_off', true)->count(),
            'holidays' => $schedules->where('is_holiday', true)->count(),
            'shifts_breakdown' => $schedules->whereNotNull('shift_id')
                ->groupBy('shift_id')
                ->map(function ($group) {
                    return [
                        'shift' => $group->first()->shift,
                        'count' => $group->count(),
                    ];
                })
                ->values(),
        ];
    }

    /**
     * Check schedule conflicts
     */
    public static function checkConflicts($employeeId, $date, $shiftId)
    {
        $existingSchedule = self::where('employee_id', $employeeId)
            ->where('date', $date)
            ->first();

        if (!$existingSchedule) {
            return null;
        }

        $conflicts = [];

        if ($existingSchedule->shift_id && $existingSchedule->shift_id != $shiftId) {
            $conflicts[] = "Sudah ada shift {$existingSchedule->shift->name} pada tanggal ini";
        }

        if ($existingSchedule->is_day_off) {
            $conflicts[] = "Tanggal ini adalah hari libur karyawan";
        }

        if ($existingSchedule->is_holiday) {
            $conflicts[] = "Tanggal ini adalah hari libur nasional";
        }

        return empty($conflicts) ? null : $conflicts;
    }
}