<?php

namespace App\Models;

use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequest extends Model
{
    use HasApprovalWorkflow;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'attachment_path',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * ========================================
     * APPROVAL WORKFLOW
     * ========================================
     */

    public function getWorkflowType(): string
    {
        return 'leave';
    }

    public function getRequesterId(): int
    {
        return $this->employee_id;
    }

    /**
     * Get approval context for condition evaluation and resolver
     */
    public function getApprovalContext(): array
    {
        $employee = $this->employee;
        $currentCareer = $employee?->current_career;

        return [
            'requester_user_id' => $employee?->user_id,
            'requester_level_id' => $currentCareer?->level_id,
            'requester_level' => $currentCareer?->level?->approval_order,
            'department_id' => $currentCareer?->department_id,
            'leave_type_id' => $this->leave_type_id,
            'leave_type_name' => $this->leaveType?->name,
            'days_requested' => $this->total_days,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
        ];
    }

    /**
     * ⭐ Callback when workflow is fully approved
     * This triggers balance deduction and attendance sync
     */
    public function onWorkflowApproved(\App\Models\ApprovalRequest $request): void
    {
        // Skip if already approved
        if ($this->status === self::STATUS_APPROVED) {
            return;
        }

        // Get last approver from the request
        $lastStep = $request->steps()->whereNotNull('actioned_at')->orderBy('step_order', 'desc')->first();
        $approverId = $lastStep?->approver_id ?? auth()->id();

        // Deduct from balance
        $balance = EmployeeLeaveBalance::getOrCreate(
            $this->employee_id,
            $this->leave_type_id,
            $this->start_date->year
        );

        if ($balance->hasSufficientBalance($this->total_days)) {
            $balance->deduct($this->total_days);
        }

        // Update status
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        // ⭐ Sync to attendance - KUNCI INTEGRASI
        $this->syncToAttendance();

        \Log::info('Leave request approved via workflow', [
            'leave_request_id' => $this->id,
            'employee_id' => $this->employee_id,
            'dates' => $this->formatted_date_range,
            'attendance_synced' => true,
        ]);
    }

    /**
     * ⭐ Callback when workflow is rejected
     */
    public function onWorkflowRejected(\App\Models\ApprovalRequest $request, ?string $reason = null): void
    {
        $lastStep = $request->steps()->whereNotNull('actioned_at')->orderBy('step_order', 'desc')->first();
        $approverId = $lastStep?->approver_id ?? auth()->id();

        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason ?? $lastStep?->notes,
        ]);

        \Log::info('Leave request rejected via workflow', [
            'leave_request_id' => $this->id,
            'reason' => $reason,
        ]);
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

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($inner) use ($startDate, $endDate) {
                    $inner->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    public function getFormattedDateRangeAttribute(): string
    {
        if ($this->start_date->eq($this->end_date)) {
            return $this->start_date->format('d M Y');
        }
        return $this->start_date->format('d M') . ' - ' . $this->end_date->format('d M Y');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-700',
            self::STATUS_APPROVED => 'bg-green-100 text-green-700',
            self::STATUS_REJECTED => 'bg-red-100 text-red-700',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Calculate total days between dates (excluding weekends if needed)
     */
    public static function calculateTotalDays(Carbon $startDate, Carbon $endDate, bool $excludeWeekends = false): int
    {
        if ($excludeWeekends) {
            $days = 0;
            $period = CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                if (!$date->isWeekend()) {
                    $days++;
                }
            }
            return $days;
        }

        return $startDate->diffInDays($endDate) + 1;
    }

    /**
     * Get all dates in the leave period
     */
    public function getLeaveDates(): array
    {
        $dates = [];
        $period = CarbonPeriod::create($this->start_date, $this->end_date);

        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }

    /**
     * Approve the leave request
     */
    public function approve(int $approverId): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        // Deduct from balance
        $balance = EmployeeLeaveBalance::getOrCreate(
            $this->employee_id,
            $this->leave_type_id,
            $this->start_date->year
        );

        if (!$balance->hasSufficientBalance($this->total_days)) {
            return false;
        }

        $balance->deduct($this->total_days);

        // Update status
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        // Update attendance summaries
        $this->syncToAttendance();

        return true;
    }

    /**
     * Reject the leave request
     */
    public function reject(int $approverId, ?string $reason = null): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Cancel the leave request
     */
    public function cancel(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED])) {
            return false;
        }

        // If was approved, restore balance
        if ($this->status === self::STATUS_APPROVED) {
            $balance = EmployeeLeaveBalance::forEmployee($this->employee_id)
                ->forType($this->leave_type_id)
                ->forYear($this->start_date->year)
                ->first();

            $balance?->restore($this->total_days);

            // Remove from attendance
            $this->removeFromAttendance();
        }

        $this->update(['status' => self::STATUS_CANCELLED]);

        return true;
    }

    /**
     * Sync approved leave to attendance summaries AND employee schedules
     */
    public function syncToAttendance(): void
    {
        $leaveDates = $this->getLeaveDates();

        foreach ($leaveDates as $date) {
            // Sync to AttendanceSummary
            AttendanceSummary::updateOrCreate(
                [
                    'employee_id' => $this->employee_id,
                    'date' => $date,
                ],
                [
                    'status' => 'leave',
                    'leave_request_id' => $this->id,
                    'notes' => $this->leaveType->name . ': ' . ($this->reason ?? 'Cuti'),
                ]
            );

            // ⭐ Sync to EmployeeSchedule - mark as leave
            EmployeeSchedule::updateOrCreate(
                [
                    'employee_id' => $this->employee_id,
                    'date' => $date,
                ],
                [
                    'is_leave' => true,
                    'leave_request_id' => $this->id,
                    'shift_id' => null, // Clear shift when on leave
                    'is_day_off' => false,
                    'is_holiday' => false,
                    'notes' => 'Cuti: ' . $this->leaveType->name,
                ]
            );
        }

        \Log::info('Leave synced to schedules', [
            'leave_request_id' => $this->id,
            'employee_id' => $this->employee_id,
            'dates' => is_array($leaveDates) ? $leaveDates : $leaveDates->toArray(),
        ]);
    }

    /**
     * Remove from attendance and schedule when cancelled
     */
    public function removeFromAttendance(): void
    {
        // Remove from AttendanceSummary
        AttendanceSummary::where('leave_request_id', $this->id)
            ->update([
                'status' => 'absent',
                'leave_request_id' => null,
                'notes' => null,
            ]);

        // ⭐ Remove from EmployeeSchedule - reset leave status
        EmployeeSchedule::where('leave_request_id', $this->id)
            ->update([
                'is_leave' => false,
                'leave_request_id' => null,
                'notes' => null,
            ]);
    }

    /**
     * Check for overlapping leaves
     */
    public function hasOverlap(): bool
    {
        return self::approved()
            ->forEmployee($this->employee_id)
            ->where('id', '!=', $this->id ?? 0)
            ->inDateRange($this->start_date, $this->end_date)
            ->exists();
    }
}
