<?php

namespace App\Models;

use App\Enums\AttendanceEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

/**
 * Attendance Event
 * 
 * Append-only ledger entry for all attendance changes.
 * This is the source of truth for deterministic rebuild and audit.
 * 
 * IMMUTABILITY: Events should NEVER be updated or deleted.
 * To "undo" an event, create a new compensating event.
 */
class AttendanceEvent extends Model
{
    /**
     * Disable updated_at since events are immutable
     */
    public $timestamps = false;

    protected $fillable = [
        'employee_id',
        'date',
        'event_type',
        'payload',
        'source_type',
        'source_id',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'date' => 'date',
        'event_type' => AttendanceEventType::class,
        'payload' => 'array',
        'created_at' => 'datetime',
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

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ========================================
     * ACCESSORS
     * ========================================
     */

    protected function formattedTime(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->created_at->format('H:i:s')
        );
    }

    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->date->translatedFormat('d F Y')
        );
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->event_type->label()
        );
    }

    protected function typeIcon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->event_type->icon()
        );
    }

    protected function typeColor(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->event_type->color()
        );
    }

    /**
     * Get human-readable description of the event
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            get: function () {
                $payload = $this->payload ?? [];

                return match ($this->event_type) {
                    AttendanceEventType::CLOCK_IN =>
                    "Clock in pada " . ($payload['time'] ?? $this->created_at->format('H:i')),

                    AttendanceEventType::CLOCK_OUT =>
                    "Clock out pada " . ($payload['time'] ?? $this->created_at->format('H:i')),

                    AttendanceEventType::OVERTIME_APPROVED =>
                    "Lembur disetujui: " . ($payload['approved_minutes'] ?? 0) . " menit",

                    AttendanceEventType::LEAVE_APPROVED =>
                    "Cuti disetujui: " . ($payload['leave_type'] ?? 'Cuti'),

                    AttendanceEventType::LATE_WAIVED =>
                    "Keterlambatan " . ($payload['waived_minutes'] ?? 0) . " menit dihapuskan",

                    AttendanceEventType::STATUS_OVERRIDE =>
                    "Status diubah ke: " . ($payload['new_status'] ?? '-'),

                    AttendanceEventType::MANUAL_CORRECTION =>
                    "Koreksi: " . ($payload['reason'] ?? '-'),

                    default => $this->event_type->label(),
                };
            }
        );
    }

    /**
     * ========================================
     * SCOPES
     * ========================================
     */

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeOfType($query, AttendanceEventType $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeAffectingSummary($query)
    {
        // Exclude lifecycle-only events
        return $query->whereNotIn('event_type', [
            AttendanceEventType::SUMMARY_CALCULATED,
            AttendanceEventType::SUMMARY_REVIEWED,
            AttendanceEventType::SUMMARY_LOCKED,
            AttendanceEventType::SUMMARY_UNLOCKED,
        ]);
    }

    public function scopeChronological($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    /**
     * ========================================
     * STATIC FACTORY METHODS
     * ========================================
     */

    /**
     * Record a clock in event
     * 
     * NOTE: We intentionally do NOT store is_late/late_minutes here.
     * The RebuildService will calculate these from shift rules,
     * making the system resilient to rule changes.
     * 
     * @param int|null $createdBy User ID or null for system/cron actions
     */
    public static function recordClockIn(
        int $employeeId,
        Carbon $date,
        array $clockData,
        ?int $sourceId = null,
        ?int $createdBy = null
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'event_type' => AttendanceEventType::CLOCK_IN,
            'payload' => [
                'time' => $clockData['time'] ?? now()->format('H:i:s'),
                'latitude' => $clockData['latitude'] ?? null,
                'longitude' => $clockData['longitude'] ?? null,
                'device' => $clockData['device'] ?? null,
                'photo' => $clockData['photo'] ?? null,
                // NOTE: is_late & late_minutes removed - calculated by RebuildService
                'created_by_type' => $createdBy ? 'user' : 'system', // Audit: who created
            ],
            'source_type' => AttendanceLog::class,
            'source_id' => $sourceId,
            'created_by' => $createdBy ?? auth()->id(),
            'created_at' => now(),
        ]);
    }
    /**
     * Record a clock out event
     * 
     * NOTE: We do NOT store is_early_out here - RebuildService calculates from shift.
     * We DO store work_duration_minutes as it's a raw measurement.
     * 
     * @param int|null $createdBy User ID or null for system/cron actions
     */
    public static function recordClockOut(
        int $employeeId,
        Carbon $date,
        array $clockData,
        ?int $sourceId = null,
        ?int $createdBy = null
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'event_type' => AttendanceEventType::CLOCK_OUT,
            'payload' => [
                'time' => $clockData['time'] ?? now()->format('H:i:s'),
                'latitude' => $clockData['latitude'] ?? null,
                'longitude' => $clockData['longitude'] ?? null,
                'device' => $clockData['device'] ?? null,
                'photo' => $clockData['photo'] ?? null,
                'work_duration_minutes' => $clockData['work_duration_minutes'] ?? 0, // Raw measurement, keep it
                // NOTE: is_early_out removed - calculated by RebuildService from shift
                'created_by_type' => $createdBy ? 'user' : 'system',
            ],
            'source_type' => AttendanceLog::class,
            'source_id' => $sourceId,
            'created_by' => $createdBy ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    /**
     * Record overtime approval event
     */
    public static function recordOvertimeApproved(
        int $employeeId,
        Carbon $date,
        int $approvedMinutes,
        int $overtimeRequestId,
        ?int $approvedBy = null
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'event_type' => AttendanceEventType::OVERTIME_APPROVED,
            'payload' => [
                'approved_minutes' => $approvedMinutes,
                'approved_by' => $approvedBy ?? auth()->id(),
            ],
            'source_type' => OvertimeRequest::class,
            'source_id' => $overtimeRequestId,
            'created_by' => $approvedBy ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    /**
     * Record leave approval event
     */
    public static function recordLeaveApproved(
        int $employeeId,
        Carbon $date,
        string $leaveType,
        string $status,
        int $leaveRequestId,
        ?int $approvedBy = null
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'event_type' => AttendanceEventType::LEAVE_APPROVED,
            'payload' => [
                'leave_type' => $leaveType,
                'status_override' => $status, // leave, sick, permission, etc.
            ],
            'source_type' => LeaveRequest::class,
            'source_id' => $leaveRequestId,
            'created_by' => $approvedBy ?? auth()->id(),
            'created_at' => now(),
        ]);
    }

    /**
     * Record manual correction event
     */
    public static function recordManualCorrection(
        int $employeeId,
        Carbon $date,
        array $changes,
        string $reason,
        int $correctedBy
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'event_type' => AttendanceEventType::MANUAL_CORRECTION,
            'payload' => [
                'changes' => $changes,
                'reason' => $reason,
            ],
            'created_by' => $correctedBy,
            'created_at' => now(),
        ]);
    }

    /**
     * Record late waiver event
     */
    public static function recordLateWaived(
        int $employeeId,
        Carbon $date,
        int $waivedMinutes,
        string $reason,
        int $waivedBy
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'event_type' => AttendanceEventType::LATE_WAIVED,
            'payload' => [
                'waived_minutes' => $waivedMinutes,
                'reason' => $reason,
            ],
            'created_by' => $waivedBy,
            'created_at' => now(),
        ]);
    }

    /**
     * Record status override event
     */
    public static function recordStatusOverride(
        int $employeeId,
        Carbon $date,
        string $oldStatus,
        string $newStatus,
        string $reason,
        int $overriddenBy
    ): self {
        return self::create([
            'employee_id' => $employeeId,
            'date' => $date,
            'event_type' => AttendanceEventType::STATUS_OVERRIDE,
            'payload' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
            ],
            'created_by' => $overriddenBy,
            'created_at' => now(),
        ]);
    }

    /**
     * ========================================
     * AUDIT/TIMELINE METHODS
     * ========================================
     */

    /**
     * Get full timeline for employee on date
     */
    public static function getTimeline(int $employeeId, Carbon $date): \Illuminate\Support\Collection
    {
        return self::forEmployee($employeeId)
            ->forDate($date)
            ->chronological()
            ->with('createdBy')
            ->get();
    }

    /**
     * Get timeline for date range (for audit report)
     */
    public static function getTimelineForPeriod(
        int $employeeId,
        Carbon $startDate,
        Carbon $endDate
    ): \Illuminate\Support\Collection {
        return self::forEmployee($employeeId)
            ->forDateRange($startDate, $endDate)
            ->chronological()
            ->with('createdBy')
            ->get()
            ->groupBy(fn($e) => $e->date->format('Y-m-d'));
    }
}
