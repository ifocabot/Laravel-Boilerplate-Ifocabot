<?php

namespace App\Enums;

/**
 * Attendance Summary Lifecycle Status
 * 
 * Explicit state machine for attendance summary lifecycle.
 * Ensures proper audit trail and prevents unauthorized edits.
 */
enum AttendanceSummaryStatus: string
{
    case PENDING = 'pending';         // Day not complete yet
    case CALCULATED = 'calculated';   // Auto-calculated at end of day
    case REVIEWED = 'reviewed';       // HR has reviewed
    case LOCKED = 'locked';           // Payroll cutoff, no more edits
    case PAYROLLED = 'payrolled';     // Included in payroll slip

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CALCULATED => 'Terhitung',
            self::REVIEWED => 'Direview',
            self::LOCKED => 'Terkunci',
            self::PAYROLLED => 'Terbayar',
        };
    }

    /**
     * Get badge color class
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-700',
            self::CALCULATED => 'bg-blue-100 text-blue-700',
            self::REVIEWED => 'bg-cyan-100 text-cyan-700',
            self::LOCKED => 'bg-gray-100 text-gray-700',
            self::PAYROLLED => 'bg-green-100 text-green-700',
        };
    }

    /**
     * Check if edits are allowed in this status
     */
    public function canEdit(): bool
    {
        return match ($this) {
            self::PENDING, self::CALCULATED => true,
            self::REVIEWED, self::LOCKED, self::PAYROLLED => false,
        };
    }

    /**
     * Check if adjustments can be made (vs direct edit)
     */
    public function requiresAdjustment(): bool
    {
        return match ($this) {
            self::PENDING, self::CALCULATED => false, // Can edit directly
            self::REVIEWED => true,  // Needs adjustment record
            self::LOCKED, self::PAYROLLED => true, // Needs payroll adjustment
        };
    }

    /**
     * Get allowed transitions from this status
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::CALCULATED],
            self::CALCULATED => [self::REVIEWED, self::LOCKED],
            self::REVIEWED => [self::LOCKED, self::CALCULATED], // Can un-review
            self::LOCKED => [self::PAYROLLED, self::REVIEWED], // Can unlock with reason
            self::PAYROLLED => [], // Final state
        };
    }

    /**
     * Check if transition to target status is allowed
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }
}
