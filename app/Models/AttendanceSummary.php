<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class AttendanceSummary extends Model
{
    protected $table = 'attendance_summaries'; // ✅ Correct
    public $timestamps = true; // ✅ Should be true (default)

    protected $fillable = [
        'employee_id',
        'date',
        'shift_id',
        'overtime_request_id',
        'leave_request_id', // For leave integration
        'status',
        'total_work_minutes',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'approved_overtime_minutes',
        'notes',
        'system_notes',
        'is_locked_for_payroll', // ✅ NEW
        'locked_at', // ✅ NEW
        'locked_by', // ✅ NEW
    ];

    protected $casts = [
        'date' => 'date',
        'total_work_minutes' => 'integer',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'approved_overtime_minutes' => 'integer',
        'is_locked_for_payroll' => 'boolean', // ✅ NEW
        'locked_at' => 'datetime', // ✅ NEW
    ];

    // ✅ NEW Relationship
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    // ✅ NEW Accessor
    protected function isLocked(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->is_locked_for_payroll
        );
    }

    /**
     * ✅ UPDATED: Sync approved overtime with payroll lock check
     */
    public function syncApprovedOvertime(OvertimeRequest $overtimeRequest): void
    {
        // ⚠️ Check if locked for payroll
        if ($this->is_locked_for_payroll) {
            \Log::warning('Cannot sync overtime to locked summary', [
                'summary_id' => $this->id,
                'employee_id' => $this->employee_id,
                'date' => $this->date->format('Y-m-d'),
                'overtime_request_id' => $overtimeRequest->id,
                'locked_at' => $this->locked_at,
                'locked_by' => $this->locked_by,
            ]);

            throw new \Exception(
                'Attendance summary sudah dikunci untuk payroll. ' .
                'Overtime approval tidak dapat di-sync. ' .
                'Hubungi HR/Payroll team untuk adjustment manual.'
            );
        }

        if ($overtimeRequest->status !== 'approved') {
            // If rejected or cancelled, clear approved overtime
            $this->approved_overtime_minutes = 0;
            $this->overtime_request_id = null;
        } else {
            // Sync approved duration from overtime request
            $this->approved_overtime_minutes = $overtimeRequest->approved_duration_minutes;
            $this->overtime_request_id = $overtimeRequest->id;
        }

        $this->save();

        \Log::info('Approved overtime synced to attendance summary', [
            'summary_id' => $this->id,
            'overtime_request_id' => $overtimeRequest->id,
            'approved_minutes' => $this->approved_overtime_minutes,
            'is_retroactive' => $this->created_at->diffInDays($overtimeRequest->approved_at) > 0,
            'days_late' => $this->created_at->diffInDays($overtimeRequest->approved_at),
        ]);
    }

    /**
     * ✅ NEW: Lock summary for payroll processing
     */
    public function lockForPayroll($userId): void
    {
        $this->is_locked_for_payroll = true;
        $this->locked_at = now();
        $this->locked_by = $userId;
        $this->save();

        \Log::info('Attendance summary locked for payroll', [
            'summary_id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date->format('Y-m-d'),
            'locked_by' => $userId,
        ]);
    }

    /**
     * ✅ NEW: Unlock summary (for corrections)
     */
    public function unlockForPayroll($userId, $reason): void
    {
        $this->is_locked_for_payroll = false;
        $this->locked_at = null;
        $this->locked_by = null;
        $this->save();

        \Log::info('Attendance summary unlocked', [
            'summary_id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date->format('Y-m-d'),
            'unlocked_by' => $userId,
            'reason' => $reason,
        ]);
    }

    /**
     * ✅ NEW: Lock all summaries for a date range (for payroll)
     */
    public static function lockForPayrollPeriod($startDate, $endDate, $userId): int
    {
        $count = self::whereBetween('date', [$startDate, $endDate])
            ->where('is_locked_for_payroll', false)
            ->update([
                'is_locked_for_payroll' => true,
                'locked_at' => now(),
                'locked_by' => $userId,
            ]);

        \Log::info('Locked summaries for payroll period', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'count' => $count,
            'locked_by' => $userId,
        ]);

        return $count;
    }

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

    public function overtimeRequest(): BelongsTo
    {
        return $this->belongsTo(OvertimeRequest::class);
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
     * Get total work hours (decimal)
     */
    protected function totalWorkHours(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->total_work_minutes / 60, 2)
        );
    }

    /**
     * Get formatted total work duration
     */
    protected function formattedTotalWork(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->total_work_minutes <= 0) {
                    return '-';
                }

                $hours = floor($this->total_work_minutes / 60);
                $minutes = $this->total_work_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get formatted late duration
     */
    protected function formattedLate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->late_minutes <= 0) {
                    return '-';
                }

                if ($this->late_minutes < 60) {
                    return "{$this->late_minutes}m";
                }

                $hours = floor($this->late_minutes / 60);
                $minutes = $this->late_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get formatted system detected overtime
     */
    protected function formattedOvertime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->overtime_minutes <= 0) {
                    return '-';
                }

                $hours = floor($this->overtime_minutes / 60);
                $minutes = $this->overtime_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get formatted approved overtime (FOR PAYROLL)
     */
    protected function formattedApprovedOvertime(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->approved_overtime_minutes <= 0) {
                    return '-';
                }

                $hours = floor($this->approved_overtime_minutes / 60);
                $minutes = $this->approved_overtime_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get overtime hours (decimal) for payroll
     */
    protected function overtimeHours(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->approved_overtime_minutes / 60, 2)
        );
    }

    /**
     * Get status label in Indonesian
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->status) {
                    'present' => 'Hadir',
                    'late' => 'Terlambat',
                    'absent' => 'Tidak Hadir',
                    'leave' => 'Cuti',
                    'sick' => 'Sakit',
                    'permission' => 'Izin',
                    'wfh' => 'Work From Home',
                    'business_trip' => 'Dinas Luar',
                    'alpha' => 'Alpha',
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
                    'present' => 'bg-green-100 text-green-700',
                    'late' => 'bg-orange-100 text-orange-700',
                    'absent' => 'bg-red-100 text-red-700',
                    'leave' => 'bg-blue-100 text-blue-700',
                    'sick' => 'bg-purple-100 text-purple-700',
                    'permission' => 'bg-yellow-100 text-yellow-700',
                    'wfh' => 'bg-indigo-100 text-indigo-700',
                    'business_trip' => 'bg-cyan-100 text-cyan-700',
                    'alpha' => 'bg-gray-100 text-gray-700',
                    default => 'bg-gray-100 text-gray-700'
                };
            }
        );
    }

    /**
     * Check if eligible for payroll
     */
    protected function isEligibleForPayroll(): Attribute
    {
        return Attribute::make(
            get: fn() => in_array($this->status, ['present', 'late', 'wfh', 'business_trip'])
        );
    }

    /**
     * Check if deduction needed
     */
    protected function needsDeduction(): Attribute
    {
        return Attribute::make(
            get: fn() => in_array($this->status, ['absent', 'alpha']) || $this->late_minutes > 0
        );
    }

    /**
     * Check if has approved overtime
     */
    protected function hasApprovedOvertime(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->approved_overtime_minutes > 0
        );
    }

    /**
     * ========================================
     * OVERTIME SYNC METHODS
     * ========================================
     */


    /**
     * Calculate from attendance log
     */
    public function calculateFromLog(AttendanceLog $log): void
    {
        $this->shift_id = $log->shift_id;
        $this->total_work_minutes = $log->work_duration_minutes;
        $this->late_minutes = $log->late_duration_minutes;

        // Determine status
        if (!$log->has_clocked_in) {
            $this->status = 'absent';
        } elseif ($log->is_late) {
            $this->status = 'late';
        } else {
            $this->status = 'present';
        }

        // Calculate overtime (system detected)
        if ($log->shift && $log->work_duration_minutes > 0) {
            $requiredMinutes = $log->shift->work_hours_required;
            $overtimeMinutes = max(0, $log->work_duration_minutes - $requiredMinutes);
            $this->overtime_minutes = $overtimeMinutes;
        }

        // ✅ IMPORTANT: Check if there's ALREADY an APPROVED overtime request
        // This handles both:
        // 1. Overtime approved BEFORE summary generation (normal flow)
        // 2. Overtime approved AFTER summary generation (retroactive)
        $approvedRequest = OvertimeRequest::where('employee_id', $this->employee_id)
            ->where('date', $this->date)
            ->where('status', 'approved')
            ->first();

        if ($approvedRequest) {
            // Sync approved overtime from request
            $this->approved_overtime_minutes = $approvedRequest->approved_duration_minutes;
            $this->overtime_request_id = $approvedRequest->id;

            // Update actual duration in overtime request
            $approvedRequest->actual_duration_minutes = $this->overtime_minutes;
            $approvedRequest->save();
        } else {
            // No approved request = no approved overtime for payroll
            $this->approved_overtime_minutes = 0;
            $this->overtime_request_id = null;
        }

        // Auto-generate system notes
        $notes = [];
        if ($log->is_late) {
            $notes[] = "Terlambat {$log->late_duration_minutes} menit";
        }
        if ($log->is_early_out) {
            $notes[] = "Pulang lebih awal";
        }
        if ($this->overtime_minutes > 0) {
            $notes[] = "Overtime terdeteksi {$this->formatted_overtime}";

            if ($this->approved_overtime_minutes > 0) {
                $notes[] = "Overtime disetujui {$this->formatted_approved_overtime}";
            } else {
                $notes[] = "Overtime belum disetujui";
            }
        }

        $this->system_notes = !empty($notes) ? implode('. ', $notes) : null;

        $this->save();
    }

    /**
     * Mark as leave/sick/permission
     */
    public function markAsLeave(string $type, string $notes = null): void
    {
        $validTypes = ['leave', 'sick', 'permission', 'wfh', 'business_trip'];

        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Invalid leave type: {$type}");
        }

        $this->status = $type;
        $this->notes = $notes;
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

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'late', 'wfh', 'business_trip']);
    }

    public function scopeAbsent($query)
    {
        return $query->whereIn('status', ['absent', 'alpha']);
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeHasApprovedOvertime($query)
    {
        return $query->where('approved_overtime_minutes', '>', 0);
    }

    /**
     * ========================================
     * STATIC METHODS
     * ========================================
     */

    /**
     * Generate summary from attendance log
     */
    public static function generateFromLog(AttendanceLog $log): self
    {
        $summary = self::firstOrCreate(
            [
                'employee_id' => $log->employee_id,
                'date' => $log->date,
            ],
            [
                'shift_id' => $log->shift_id,
            ]
        );

        $summary->calculateFromLog($log);

        return $summary;
    }

    /**
     * ✅ Sync from overtime request (called when OT approved/rejected)
     */
    public static function syncFromOvertimeRequest(OvertimeRequest $overtimeRequest): void
    {
        $summary = self::firstOrCreate(
            [
                'employee_id' => $overtimeRequest->employee_id,
                'date' => $overtimeRequest->date,
            ],
            [
                'status' => 'present',
                'overtime_minutes' => 0,
            ]
        );

        $summary->syncApprovedOvertime($overtimeRequest);
    }

    /**
     * Generate summaries for all logs in date range
     */
    public static function generateForDateRange($startDate, $endDate): int
    {
        $logs = AttendanceLog::whereBetween('date', [$startDate, $endDate])
            ->with('shift')
            ->get();

        $count = 0;
        foreach ($logs as $log) {
            self::generateFromLog($log);
            $count++;
        }

        return $count;
    }

    /**
     * Get payroll summary for employee in date range
     */
    public static function getPayrollSummary($employeeId, $startDate, $endDate): array
    {
        $summaries = self::forEmployee($employeeId)
            ->forDateRange($startDate, $endDate)
            ->get();

        return [
            'total_days' => $summaries->count(),
            'present_days' => $summaries->whereIn('status', ['present', 'late', 'wfh', 'business_trip'])->count(),
            'absent_days' => $summaries->whereIn('status', ['absent', 'alpha'])->count(),
            'late_days' => $summaries->where('status', 'late')->count(),
            'leave_days' => $summaries->where('status', 'leave')->count(),
            'sick_days' => $summaries->where('status', 'sick')->count(),
            'permission_days' => $summaries->where('status', 'permission')->count(),
            'wfh_days' => $summaries->where('status', 'wfh')->count(),
            'business_trip_days' => $summaries->where('status', 'business_trip')->count(),
            'total_work_hours' => $summaries->sum('total_work_minutes') / 60,
            'total_late_minutes' => $summaries->sum('late_minutes'),
            'total_overtime_hours' => $summaries->sum('approved_overtime_minutes') / 60, // ✅ Uses APPROVED overtime
            'overtime_pay_eligible_hours' => $summaries->sum('approved_overtime_minutes') / 60, // ✅ For payroll
        ];
    }

    /**
     * Get monthly statistics
     */
    public static function getMonthlyStats($year, $month): array
    {
        $summaries = self::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        return [
            'total_records' => $summaries->count(),
            'total_present' => $summaries->whereIn('status', ['present', 'late', 'wfh', 'business_trip'])->count(),
            'total_absent' => $summaries->whereIn('status', ['absent', 'alpha'])->count(),
            'total_late' => $summaries->where('status', 'late')->count(),
            'total_leave' => $summaries->where('status', 'leave')->count(),
            'total_work_hours' => round($summaries->sum('total_work_minutes') / 60, 2),
            'total_overtime_hours' => round($summaries->sum('approved_overtime_minutes') / 60, 2), // ✅ APPROVED only
        ];
    }
}