<?php

namespace App\Enums;

/**
 * Attendance Event Types
 * 
 * All possible events that affect attendance state.
 * Used for audit trail and deterministic rebuild.
 */
enum AttendanceEventType: string
{
    // Clock events
    case CLOCK_IN = 'clock_in';
    case CLOCK_OUT = 'clock_out';
    case CLOCK_IN_CORRECTED = 'clock_in_corrected';
    case CLOCK_OUT_CORRECTED = 'clock_out_corrected';

    // Overtime events
    case OVERTIME_REQUESTED = 'overtime_requested';
    case OVERTIME_APPROVED = 'overtime_approved';
    case OVERTIME_REJECTED = 'overtime_rejected';
    case OVERTIME_CANCELLED = 'overtime_cancelled';

    // Leave events
    case LEAVE_APPROVED = 'leave_approved';
    case LEAVE_CANCELLED = 'leave_cancelled';

    // Adjustment events
    case LATE_WAIVED = 'late_waived';
    case EARLY_LEAVE_WAIVED = 'early_leave_waived';
    case STATUS_OVERRIDE = 'status_override';
    case MANUAL_CORRECTION = 'manual_correction';

    // Lifecycle events
    case SUMMARY_CALCULATED = 'summary_calculated';
    case SUMMARY_REVIEWED = 'summary_reviewed';
    case SUMMARY_LOCKED = 'summary_locked';
    case SUMMARY_UNLOCKED = 'summary_unlocked';

    /**
     * Get human-readable label (Indonesian)
     */
    public function label(): string
    {
        return match ($this) {
            self::CLOCK_IN => 'Clock In',
            self::CLOCK_OUT => 'Clock Out',
            self::CLOCK_IN_CORRECTED => 'Koreksi Clock In',
            self::CLOCK_OUT_CORRECTED => 'Koreksi Clock Out',
            self::OVERTIME_REQUESTED => 'Pengajuan Lembur',
            self::OVERTIME_APPROVED => 'Lembur Disetujui',
            self::OVERTIME_REJECTED => 'Lembur Ditolak',
            self::OVERTIME_CANCELLED => 'Lembur Dibatalkan',
            self::LEAVE_APPROVED => 'Cuti Disetujui',
            self::LEAVE_CANCELLED => 'Cuti Dibatalkan',
            self::LATE_WAIVED => 'Keterlambatan Dihapuskan',
            self::EARLY_LEAVE_WAIVED => 'Pulang Cepat Dihapuskan',
            self::STATUS_OVERRIDE => 'Status Diubah Manual',
            self::MANUAL_CORRECTION => 'Koreksi Manual',
            self::SUMMARY_CALCULATED => 'Summary Dikalkulasi',
            self::SUMMARY_REVIEWED => 'Summary Direview',
            self::SUMMARY_LOCKED => 'Summary Dikunci',
            self::SUMMARY_UNLOCKED => 'Summary Dibuka',
        };
    }

    /**
     * Get icon for timeline display
     */
    public function icon(): string
    {
        return match ($this) {
            self::CLOCK_IN, self::CLOCK_IN_CORRECTED => 'arrow-right-to-bracket',
            self::CLOCK_OUT, self::CLOCK_OUT_CORRECTED => 'arrow-right-from-bracket',
            self::OVERTIME_REQUESTED => 'clock-rotate-left',
            self::OVERTIME_APPROVED => 'clock-rotate-left',
            self::OVERTIME_REJECTED, self::OVERTIME_CANCELLED => 'xmark',
            self::LEAVE_APPROVED => 'calendar-check',
            self::LEAVE_CANCELLED => 'calendar-xmark',
            self::LATE_WAIVED, self::EARLY_LEAVE_WAIVED => 'eraser',
            self::STATUS_OVERRIDE, self::MANUAL_CORRECTION => 'pen-to-square',
            self::SUMMARY_CALCULATED => 'calculator',
            self::SUMMARY_REVIEWED => 'eye',
            self::SUMMARY_LOCKED => 'lock',
            self::SUMMARY_UNLOCKED => 'lock-open',
        };
    }

    /**
     * Get color for timeline display
     */
    public function color(): string
    {
        return match ($this) {
            self::CLOCK_IN, self::CLOCK_IN_CORRECTED => 'green',
            self::CLOCK_OUT, self::CLOCK_OUT_CORRECTED => 'blue',
            self::OVERTIME_APPROVED => 'purple',
            self::OVERTIME_REJECTED, self::OVERTIME_CANCELLED => 'red',
            self::OVERTIME_REQUESTED => 'yellow',
            self::LEAVE_APPROVED => 'indigo',
            self::LEAVE_CANCELLED => 'red',
            self::LATE_WAIVED, self::EARLY_LEAVE_WAIVED => 'teal',
            self::STATUS_OVERRIDE, self::MANUAL_CORRECTION => 'orange',
            self::SUMMARY_CALCULATED => 'gray',
            self::SUMMARY_REVIEWED => 'cyan',
            self::SUMMARY_LOCKED => 'slate',
            self::SUMMARY_UNLOCKED => 'amber',
        };
    }

    /**
     * Check if this event affects summary calculation
     */
    public function affectsSummary(): bool
    {
        return match ($this) {
            self::SUMMARY_CALCULATED,
            self::SUMMARY_REVIEWED,
            self::SUMMARY_LOCKED,
            self::SUMMARY_UNLOCKED => false,
            default => true,
        };
    }
}
