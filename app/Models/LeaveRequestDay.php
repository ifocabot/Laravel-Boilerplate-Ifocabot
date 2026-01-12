<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestDay extends Model
{
    protected $fillable = [
        'leave_request_id',
        'date',
        'day_value',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'day_value' => 'decimal:1',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * ========================================
     * RELATIONSHIPS
     * ========================================
     */

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
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

    public function scopeForDate($query, string $date)
    {
        return $query->where('date', $date);
    }

    /**
     * ========================================
     * METHODS
     * ========================================
     */

    /**
     * Check if date overlaps with existing approved leave for employee
     */
    public static function hasOverlap(int $employeeId, string $date, ?int $excludeRequestId = null): bool
    {
        return self::whereHas('leaveRequest', function ($q) use ($employeeId, $excludeRequestId) {
            $q->where('employee_id', $employeeId)
                ->where('status', LeaveRequest::STATUS_APPROVED);

            if ($excludeRequestId) {
                $q->where('id', '!=', $excludeRequestId);
            }
        })
            ->where('date', $date)
            ->where('status', self::STATUS_APPROVED)
            ->exists();
    }

    /**
     * Check multiple dates for overlap - returns array of conflicting dates
     */
    public static function getOverlappingDates(int $employeeId, array $dates, ?int $excludeRequestId = null): array
    {
        return self::whereHas('leaveRequest', function ($q) use ($employeeId, $excludeRequestId) {
            $q->where('employee_id', $employeeId)
                ->where('status', LeaveRequest::STATUS_APPROVED);

            if ($excludeRequestId) {
                $q->where('id', '!=', $excludeRequestId);
            }
        })
            ->whereIn('date', $dates)
            ->where('status', self::STATUS_APPROVED)
            ->pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();
    }
}
