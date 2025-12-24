<?php

namespace App\Models;

use App\Notifications\RetroactiveOvertimeApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'start_at',
        'end_at',
        'duration_minutes',
        'reason',
        'work_description',
        'status',
        'approver_id',
        'approved_at',
        'rejected_at',
        'approval_notes',
        'rejection_note',
        'actual_duration_minutes',
        'approved_duration_minutes',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'actual_duration_minutes' => 'integer',
        'approved_duration_minutes' => 'integer',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get related attendance summaries
     */
    public function attendanceSummaries(): HasMany
    {
        return $this->hasMany(AttendanceSummary::class);
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
     * Get formatted time range
     */
    protected function timeRange(): Attribute
    {
        return Attribute::make(
            get: fn() => Carbon::parse($this->start_at)->format('H:i') . ' - ' . Carbon::parse($this->end_at)->format('H:i')
        );
    }

    /**
     * Get formatted duration (requested)
     */
    protected function formattedDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->duration_minutes <= 0) {
                    return '-';
                }

                $hours = floor($this->duration_minutes / 60);
                $minutes = $this->duration_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get duration in hours (decimal)
     */
    protected function durationHours(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->duration_minutes / 60, 2)
        );
    }

    /**
     * Get formatted actual duration
     */
    protected function formattedActualDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->actual_duration_minutes <= 0) {
                    return '-';
                }

                $hours = floor($this->actual_duration_minutes / 60);
                $minutes = $this->actual_duration_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get formatted approved duration (FOR PAYROLL)
     */
    protected function formattedApprovedDuration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->approved_duration_minutes <= 0) {
                    return '-';
                }

                $hours = floor($this->approved_duration_minutes / 60);
                $minutes = $this->approved_duration_minutes % 60;

                return $minutes > 0 ? "{$hours}j {$minutes}m" : "{$hours}j";
            }
        );
    }

    /**
     * Get approved hours (decimal) for payroll
     */
    protected function approvedHours(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->approved_duration_minutes / 60, 2)
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
                    'pending' => 'Menunggu Approval',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'cancelled' => 'Dibatalkan',
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
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'approved' => 'bg-green-100 text-green-700',
                    'rejected' => 'bg-red-100 text-red-700',
                    'cancelled' => 'bg-gray-100 text-gray-700',
                    default => 'bg-gray-100 text-gray-700'
                };
            }
        );
    }

    /**
     * Check if request is pending
     */
    protected function isPending(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'pending'
        );
    }

    /**
     * Check if request is approved
     */
    protected function isApproved(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'approved'
        );
    }

    /**
     * Check if request is rejected
     */
    protected function isRejected(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'rejected'
        );
    }

    /**
     * Check if request is cancelled
     */
    protected function isCancelled(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'cancelled'
        );
    }

    /**
     * Check if can be cancelled
     */
    protected function canBeCancelled(): Attribute
    {
        return Attribute::make(
            get: fn() => in_array($this->status, ['pending', 'approved'])
        );
    }

    /**
     * ========================================
     * APPROVAL METHODS (WITH AUTO-SYNC)
     * ========================================
     */

    /**
     * Approve overtime request
     * ✅ AUTO-SYNC to attendance_summaries (even if summary already exists)
     */
    /**
     * Approve overtime request
     * ✅ AUTO-SYNC with retroactive detection
     */
    public function approve($approverId, $approvedMinutes = null, $notes = null): void
    {
        $this->status = 'approved';
        $this->approver_id = $approverId;
        $this->approved_at = now();
        $this->approved_duration_minutes = $approvedMinutes ?? $this->duration_minutes;
        $this->approval_notes = $notes;
        $this->save();

        // ✅ Check if this is retroactive (approved after the work date)
        $isRetroactive = $this->date->isBefore(now()->startOfDay());
        $daysLate = $isRetroactive ? $this->date->diffInDays(now()) : 0;

        try {
            // ✅ AUTO-SYNC to attendance_summaries
            AttendanceSummary::syncFromOvertimeRequest($this);

            // ✅ Send notification if retroactive
            if ($isRetroactive && $daysLate > 2) {
                $this->notifyRetroactiveApproval($daysLate);
            }

            \Log::info('Overtime approved and synced', [
                'overtime_request_id' => $this->id,
                'employee_id' => $this->employee_id,
                'date' => $this->date->format('Y-m-d'),
                'approved_minutes' => $this->approved_duration_minutes,
                'approver_id' => $approverId,
                'is_retroactive' => $isRetroactive,
                'days_late' => $daysLate,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to sync overtime to summary', [
                'overtime_request_id' => $this->id,
                'error' => $e->getMessage(),
                'is_retroactive' => $isRetroactive,
            ]);

            // ✅ If locked, notify HR
            if (strpos($e->getMessage(), 'dikunci untuk payroll') !== false) {
                $this->notifyPayrollLocked();
            }

            throw $e;
        }
    }

    /**
     * ✅ NEW: Notify when retroactive approval
     */
    private function notifyRetroactiveApproval($daysLate): void
    {
        // Send notification to HR/Payroll team
        \Notification::send(
            User::role('hr')->get(),
            new \App\Notifications\RetroactiveOvertimeApproval($this, $daysLate)
        );

        \Log::info('Retroactive overtime approval notification sent', [
            'overtime_request_id' => $this->id,
            'days_late' => $daysLate,
        ]);
    }

    /**
     * ✅ NEW: Notify when trying to update locked summary
     */
    private function notifyPayrollLocked(): void
    {
        // Send notification to HR/Payroll team
        \Notification::send(
            User::role('hr')->get(),
            new \App\Notifications\OvertimeApprovalPayrollLocked($this)
        );
    }

    /**
     * Reject overtime request
     * ✅ AUTO-SYNC to attendance_summaries (clear approved overtime)
     */
    public function reject($approverId, $reason): void
    {
        $this->status = 'rejected';
        $this->approver_id = $approverId;
        $this->rejected_at = now();
        $this->rejection_note = $reason;
        $this->approved_duration_minutes = 0; // Clear approved minutes
        $this->save();

        // ✅ AUTO-SYNC to attendance_summaries (set to 0)
        AttendanceSummary::syncFromOvertimeRequest($this);

        \Log::info('Overtime rejected and synced to attendance summary', [
            'overtime_request_id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date->format('Y-m-d'),
            'approver_id' => $approverId,
            'reason' => $reason,
        ]);
    }

    /**
     * Cancel overtime request
     * ✅ AUTO-SYNC to attendance_summaries (clear approved overtime)
     */
    public function cancel($userId, $reason): void
    {
        if (!$this->can_be_cancelled) {
            throw new \Exception('Request tidak dapat dibatalkan');
        }

        $this->status = 'cancelled';
        $this->cancelled_by = $userId;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->approved_duration_minutes = 0; // Clear approved minutes
        $this->save();

        // ✅ AUTO-SYNC to attendance_summaries (set to 0)
        AttendanceSummary::syncFromOvertimeRequest($this);

        \Log::info('Overtime cancelled and synced to attendance summary', [
            'overtime_request_id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date->format('Y-m-d'),
            'cancelled_by' => $userId,
            'reason' => $reason,
        ]);
    }

    /**
     * Update actual duration from attendance (after work done)
     */
    public function updateActualDuration($minutes): void
    {
        $this->actual_duration_minutes = max(0, $minutes);

        // If approved but no approved_duration set yet, use actual (up to requested max)
        if ($this->is_approved && $this->approved_duration_minutes === 0) {
            $this->approved_duration_minutes = min($this->actual_duration_minutes, $this->duration_minutes);
        }

        $this->save();

        \Log::info('Actual overtime duration updated', [
            'overtime_request_id' => $this->id,
            'actual_minutes' => $this->actual_duration_minutes,
        ]);
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    /**
     * ========================================
     * STATIC METHODS
     * ========================================
     */

    /**
     * Get pending requests count
     */
    public static function getPendingCount(): int
    {
        return self::pending()->count();
    }

    /**
     * Get pending requests for specific approver/manager
     */
    public static function getPendingForApprover($approverId)
    {
        // TODO: Implement proper manager-employee relationship
        // For now, return all pending requests
        return self::pending()
            ->with(['employee.currentCareer.department'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calculate total approved overtime hours for employee in period
     */
    public static function getTotalApprovedHours($employeeId, $startDate, $endDate): float
    {
        return self::forEmployee($employeeId)
            ->forDateRange($startDate, $endDate)
            ->approved()
            ->sum('approved_duration_minutes') / 60;
    }

    /**
     * Get statistics for period
     */
    public static function getStatistics($startDate, $endDate): array
    {
        $requests = self::forDateRange($startDate, $endDate)->get();

        return [
            'total_requests' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
            'cancelled' => $requests->where('status', 'cancelled')->count(),
            'total_requested_hours' => round($requests->sum('duration_minutes') / 60, 2),
            'total_approved_hours' => round($requests->where('status', 'approved')->sum('approved_duration_minutes') / 60, 2),
            'total_actual_hours' => round($requests->sum('actual_duration_minutes') / 60, 2),
        ];
    }

    /**
     * Check for duplicate request
     */
    public static function hasDuplicateRequest($employeeId, $date, $excludeId = null): bool
    {
        $query = self::where('employee_id', $employeeId)
            ->where('date', $date)
            ->whereIn('status', ['pending', 'approved']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Create overtime request with validation
     */
    public static function createRequest(array $data): self
    {
        // Validate no duplicate
        if (self::hasDuplicateRequest($data['employee_id'], $data['date'])) {
            throw new \Exception('Sudah ada request overtime untuk tanggal ini');
        }

        // Calculate duration
        $start = Carbon::parse($data['date'] . ' ' . $data['start_at']);
        $end = Carbon::parse($data['date'] . ' ' . $data['end_at']);

        // Handle overnight
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $durationMinutes = $start->diffInMinutes($end);

        // Create request
        $request = self::create([
            'employee_id' => $data['employee_id'],
            'date' => $data['date'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'duration_minutes' => $durationMinutes,
            'reason' => $data['reason'],
            'work_description' => $data['work_description'] ?? null,
            'status' => 'pending',
        ]);

        \Log::info('Overtime request created', [
            'overtime_request_id' => $request->id,
            'employee_id' => $request->employee_id,
            'date' => $request->date->format('Y-m-d'),
            'duration_minutes' => $durationMinutes,
        ]);

        return $request;
    }

    /**
     * Update overtime request with validation
     */
    public function updateRequest(array $data): void
    {
        if (!$this->is_pending) {
            throw new \Exception('Hanya request pending yang bisa diupdate');
        }

        // Validate no duplicate (exclude current request)
        if (self::hasDuplicateRequest($this->employee_id, $data['date'], $this->id)) {
            throw new \Exception('Sudah ada request overtime untuk tanggal ini');
        }

        // Calculate new duration
        $start = Carbon::parse($data['date'] . ' ' . $data['start_at']);
        $end = Carbon::parse($data['date'] . ' ' . $data['end_at']);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $durationMinutes = $start->diffInMinutes($end);

        // Update
        $this->update([
            'date' => $data['date'],
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'duration_minutes' => $durationMinutes,
            'reason' => $data['reason'],
            'work_description' => $data['work_description'] ?? null,
        ]);

        \Log::info('Overtime request updated', [
            'overtime_request_id' => $this->id,
            'duration_minutes' => $durationMinutes,
        ]);
    }
}
