<?php

namespace App\Models;

use App\Traits\HasApprovalWorkflow;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\AttendanceAdjustment;

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
    public const STATUS_NEEDS_HR_REVIEW = 'needs_hr_review';

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
     * 
     * FIXED: Now properly handles insufficient balance by setting status to NEEDS_HR_REVIEW
     */
    public function onWorkflowApproved(\App\Models\ApprovalRequest $request): void
    {
        // Skip if already approved or needs review
        if (in_array($this->status, [self::STATUS_APPROVED, self::STATUS_NEEDS_HR_REVIEW])) {
            return;
        }

        // Get last approver from the request
        $lastStep = $request->steps()->whereNotNull('actioned_at')->orderBy('step_order', 'desc')->first();
        $approverId = $lastStep?->approver_id ?? auth()->id();

        // Get balance
        $balance = EmployeeLeaveBalance::getOrCreate(
            $this->employee_id,
            $this->leave_type_id,
            $this->start_date->year
        );

        // Get total days (from days relation or legacy total_days field)
        $totalDays = $this->getTotalDays();

        // ⭐ DOMAIN CHECK: Saldo HARUS cukup
        if (!$balance->hasSufficientBalance($totalDays)) {
            // ❌ Saldo kurang → NEEDS_HR_REVIEW, TIDAK sync attendance
            $this->update([
                'status' => self::STATUS_NEEDS_HR_REVIEW,
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => 'Saldo cuti tidak mencukupi saat finalisasi approval. Butuh: ' . $totalDays . ' hari, tersedia: ' . $balance->remaining . ' hari.',
            ]);

            \Log::warning('Leave approval blocked: insufficient balance', [
                'leave_request_id' => $this->id,
                'required' => $totalDays,
                'available' => $balance->remaining,
            ]);
            return; // ⭐ STOP - jangan sync ke attendance
        }

        // ✅ Saldo cukup → Deduct via ledger + APPROVED
        $balance->deductWithLedger($totalDays, $this->id, $approverId);

        // Approve all days (if using per-day model)
        $this->days()->update(['status' => LeaveRequestDay::STATUS_APPROVED]);

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        // ⭐ Sync HANYA kalau approved sukses
        $this->syncToAttendance();

        \Log::info('Leave request approved via workflow', [
            'leave_request_id' => $this->id,
            'employee_id' => $this->employee_id,
            'days_deducted' => $totalDays,
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
     * Per-day leave records
     */
    public function days(): HasMany
    {
        return $this->hasMany(LeaveRequestDay::class);
    }

    /**
     * Get total days from days relation or fallback to total_days field
     */
    public function getTotalDays(): float
    {
        $daysSum = $this->days()->sum('day_value');
        return $daysSum > 0 ? (float) $daysSum : (float) ($this->total_days ?? 0);
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
            self::STATUS_NEEDS_HR_REVIEW => 'Perlu Review HR',
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
            self::STATUS_NEEDS_HR_REVIEW => 'bg-orange-100 text-orange-700',
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
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_NEEDS_HR_REVIEW])) {
            return false;
        }

        // If was approved, restore balance with ledger
        if ($this->status === self::STATUS_APPROVED) {
            $balance = EmployeeLeaveBalance::forEmployee($this->employee_id)
                ->forType($this->leave_type_id)
                ->forYear($this->start_date->year)
                ->first();

            if ($balance) {
                $balance->restoreWithLedger($this->getTotalDays(), $this->id, auth()->id());
            }

            // Remove from attendance
            $this->removeFromAttendance();
        }

        // Cancel all days
        $this->days()->update(['status' => LeaveRequestDay::STATUS_CANCELLED]);

        $this->update(['status' => self::STATUS_CANCELLED]);

        return true;
    }

    /**
     * ⭐ Sync approved leave to attendance via ledger + recalculate
     * 
     * Flow:
     * 1. Mark schedule (source of truth)
     * 2. Create adjustment ledger entry
     * 3. Trigger recalculate (derives status from sources)
     */
    public function syncToAttendance(): void
    {
        $leaveDates = $this->getLeaveDates();
        $service = app(\App\Services\Attendance\AttendanceSummaryService::class);
        $statusOverride = $this->mapLeaveTypeToStatus();

        foreach ($leaveDates as $date) {
            // 1. Mark schedule (source of truth for planning)
            EmployeeSchedule::updateOrCreate(
                [
                    'employee_id' => $this->employee_id,
                    'date' => $date,
                ],
                [
                    'is_leave' => true,
                    'leave_request_id' => $this->id,
                    'shift_id' => null,
                    'is_day_off' => false,
                    'is_holiday' => false,
                    'notes' => 'Cuti: ' . $this->leaveType->name,
                ]
            );

            // 2. Create adjustment ledger entry (regen-safe)
            AttendanceAdjustment::createForLeave(
                $this->employee_id,
                $date,
                $statusOverride,
                $this->id,
                auth()->id()
            );

            // 3. Trigger recalculate (derives status from all sources)
            $service->recalculate($this->employee_id, $date);
        }

        \Log::info('Leave synced via adjustment ledger', [
            'leave_request_id' => $this->id,
            'employee_id' => $this->employee_id,
            'dates_count' => count($leaveDates),
            'status_override' => $statusOverride,
        ]);
    }

    /**
     * Map leave type to attendance status code
     */
    protected function mapLeaveTypeToStatus(): string
    {
        $code = $this->leaveType?->code ?? '';

        return match (strtolower($code)) {
            'sick', 'sakit' => 'sick',
            'permission', 'izin' => 'permission',
            'wfh' => 'wfh',
            'business_trip', 'dinas' => 'business_trip',
            default => 'leave',
        };
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
