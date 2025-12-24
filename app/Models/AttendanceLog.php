<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Location;
use Carbon\Carbon;

class AttendanceLog extends Model
{
    protected $fillable = [
        'employee_id',
        'schedule_id',
        'shift_id',
        'date',
        'clock_in_time',
        'clock_in_lat',
        'clock_in_long',
        'clock_in_device',
        'clock_in_photo',
        'clock_in_notes',
        'clock_out_time',
        'clock_out_lat',
        'clock_out_long',
        'clock_out_device',
        'clock_out_photo',
        'clock_out_notes',
        'is_late',
        'is_early_out',
        'late_duration_minutes',
        'work_duration_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'clock_in_lat' => 'decimal:8',
        'clock_in_long' => 'decimal:8',
        'clock_out_lat' => 'decimal:8',
        'clock_out_long' => 'decimal:8',
        'is_late' => 'boolean',
        'is_early_out' => 'boolean',
        'late_duration_minutes' => 'integer',
        'work_duration_minutes' => 'integer',
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

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(EmployeeSchedule::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    /**
     * Get formatted date
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->translatedFormat('d F Y')
        );
    }

    /**
     * Get day name
     */
    protected function dayName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->translatedFormat('l')
        );
    }

    /**
     * Get formatted clock in time
     */
    protected function formattedClockIn(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->clock_in_time ? $this->clock_in_time->format('H:i') : '-'
        );
    }

    /**
     * Get formatted clock out time
     */
    protected function formattedClockOut(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->clock_out_time ? $this->clock_out_time->format('H:i') : '-'
        );
    }

    /**
     * Get formatted work duration
     */
    protected function formattedWorkDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->work_duration_minutes <= 0) {
                    return '-';
                }

                $hours = floor($this->work_duration_minutes / 60);
                $minutes = $this->work_duration_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get work duration in hours (decimal)
     */
    protected function workHours(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->work_duration_minutes / 60, 2)
        );
    }

    /**
     * Check if has clocked in
     */
    protected function hasClockedIn(): Attribute
    {
        return Attribute::make(
            get: fn() => !is_null($this->clock_in_time)
        );
    }

    /**
     * Check if has clocked out
     */
    protected function hasClockedOut(): Attribute
    {
        return Attribute::make(
            get: fn() => !is_null($this->clock_out_time)
        );
    }

    /**
     * Check if attendance is complete
     */
    protected function isComplete(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->has_clocked_in && $this->has_clocked_out
        );
    }

    /**
     * Get attendance status
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->has_clocked_in) {
                    return 'not_clocked_in';
                }
                if (!$this->has_clocked_out) {
                    return 'working';
                }
                return 'complete';
            }
        );
    }

    /**
     * Get status label
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'not_clocked_in' => 'Belum Clock In',
                    'working' => 'Sedang Bekerja',
                    'complete' => 'Selesai',
                    default => '-'
                };
            }
        );
    }

    /**
     * Get status badge class
     */
    protected function statusBadgeClass(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'not_clocked_in' => 'bg-red-100 text-red-700',
                    'working' => 'bg-blue-100 text-blue-700',
                    'complete' => 'bg-green-100 text-green-700',
                    default => 'bg-gray-100 text-gray-700'
                };
            }
        );
    }

    /**
     * Get clock in photo URL
     */
    protected function clockInPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->clock_in_photo ? asset('storage/' . $this->clock_in_photo) : null
        );
    }

    /**
     * Get clock out photo URL
     */
    protected function clockOutPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->clock_out_photo ? asset('storage/' . $this->clock_out_photo) : null
        );
    }

    /**
     * Get clock in location link (Google Maps)
     */
    protected function clockInLocationLink(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->clock_in_lat && $this->clock_in_long
            ? "https://www.google.com/maps?q={$this->clock_in_lat},{$this->clock_in_long}"
            : null
        );
    }

    /**
     * Get clock out location link (Google Maps)
     */
    protected function clockOutLocationLink(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->clock_out_lat && $this->clock_out_long
            ? "https://www.google.com/maps?q={$this->clock_out_lat},{$this->clock_out_long}"
            : null
        );
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Calculate work duration when clocking out
     */
    public function calculateWorkDuration(): int
    {
        if (!$this->clock_in_time || !$this->clock_out_time) {
            return 0;
        }

        $totalMinutes = $this->clock_in_time->diffInMinutes($this->clock_out_time);

        // Subtract break time if shift has break
        if ($this->shift && $this->shift->break_start && $this->shift->break_end) {
            $totalMinutes -= $this->shift->break_duration;
        }

        return max(0, $totalMinutes);
    }

    /**
     * Check if clock in is late
     */
    public function checkIfLate(): bool
    {
        if (!$this->clock_in_time || !$this->shift) {
            return false;
        }

        return $this->shift->isLate($this->clock_in_time);
    }

    /**
     * Calculate late duration
     */
    public function calculateLateDuration(): int
    {
        if (!$this->is_late || !$this->clock_in_time || !$this->shift) {
            return 0;
        }

        return $this->shift->getLateDuration($this->clock_in_time);
    }

    /**
     * Check if clocked out early
     */
    public function checkIfEarlyOut(): bool
    {
        if (!$this->clock_out_time || !$this->shift) {
            return false;
        }

        $expectedOut = $this->shift->getExpectedClockOut($this->clock_in_time);
        return $this->clock_out_time->lessThan($expectedOut);
    }

    /**
     * Process clock in
     */
    public function processClockIn(array $data): void
    {
        $this->clock_in_time = now();
        $this->clock_in_lat = $data['latitude'] ?? null;
        $this->clock_in_long = $data['longitude'] ?? null;
        $this->clock_in_device = $data['device'] ?? null;
        $this->clock_in_notes = $data['notes'] ?? null;

        // Check if late
        if ($this->shift) {
            $this->is_late = $this->checkIfLate();
            $this->late_duration_minutes = $this->calculateLateDuration();
        }

        $this->save();
    }

    /**
     * Process clock out
     */
    public function processClockOut(array $data): void
    {
        $this->clock_out_time = now();
        $this->clock_out_lat = $data['latitude'] ?? null;
        $this->clock_out_long = $data['longitude'] ?? null;
        $this->clock_out_device = $data['device'] ?? null;
        $this->clock_out_notes = $data['notes'] ?? null;

        // Calculate work duration
        $this->work_duration_minutes = $this->calculateWorkDuration();

        // Check if early out
        $this->is_early_out = $this->checkIfEarlyOut();

        $this->save();
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

    public function scopeToday($query)
    {
        return $query->where('date', today());
    }

    public function scopeClockedIn($query)
    {
        return $query->whereNotNull('clock_in_time');
    }

    public function scopeClockedOut($query)
    {
        return $query->whereNotNull('clock_out_time');
    }

    public function scopeComplete($query)
    {
        return $query->whereNotNull('clock_in_time')
            ->whereNotNull('clock_out_time');
    }

    public function scopeIncomplete($query)
    {
        return $query->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    public function scopeEarlyOut($query)
    {
        return $query->where('is_early_out', true);
    }

    /**
     * ========================================
     * STATIC METHODS
     * ========================================
     */

    /**
     * Get or create attendance log for today
     */
    public static function getOrCreateForToday($employeeId)
    {
        $schedule = EmployeeSchedule::where('employee_id', $employeeId)
            ->where('date', today())
            ->first();

        return self::firstOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => today(),
            ],
            [
                'schedule_id' => $schedule?->id,
                'shift_id' => $schedule?->shift_id,
            ]
        );
    }

    /**
     * Get attendance summary for period
     */
    public static function getSummary($employeeId, $startDate, $endDate)
    {
        $logs = self::forEmployee($employeeId)
            ->forDateRange($startDate, $endDate)
            ->get();

        return [
            'total_days' => $logs->count(),
            'present_days' => $logs->where('has_clocked_in', true)->count(),
            'complete_days' => $logs->where('is_complete', true)->count(),
            'late_count' => $logs->where('is_late', true)->count(),
            'early_out_count' => $logs->where('is_early_out', true)->count(),
            'total_work_hours' => $logs->sum('work_duration_minutes') / 60,
            'average_work_hours' => $logs->avg('work_duration_minutes') / 60,
        ];
    }

    /**
     * Get today's active employees (currently working)
     */
    public static function getCurrentlyWorking()
    {
        return self::today()
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->with(['employee', 'shift'])
            ->get();
    }

    public static function getClockInLocation($employeeId, $date)
    {
        $log = self::where('employee_id', $employeeId)
            ->where('date', $date)
            ->first();

        return $log?->clock_in_lat ? Location::getAddressFromCoordinates($log->clock_in_lat, $log->clock_in_long) : null;
    }

    public static function getClockOutLocation($employeeId, $date)
    {
        $log = self::where('employee_id', $employeeId)
            ->where('date', $date)
            ->first();

        return $log?->clock_out_lat ? Location::getAddressFromCoordinates($log->clock_out_lat, $log->clock_out_long) : null;
    }
}