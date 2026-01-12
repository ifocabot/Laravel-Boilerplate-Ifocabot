<?php

namespace App\Enums;

/**
 * Attendance Status
 * 
 * Daily attendance status for employees.
 * Used in AttendanceSummary->status field.
 * 
 * IMPORTANT: This is the ONLY source of valid status values.
 * Do NOT use raw strings anywhere else in the codebase.
 */
enum AttendanceStatus: string
{
    case PRESENT = 'present';               // On time
    case LATE = 'late';                     // Present but late
    case ABSENT = 'absent';                 // No-show without approval (displayed as "Alpha" in UI)
    case LEAVE = 'leave';                   // Approved leave/cuti
    case SICK = 'sick';                     // Sick leave
    case PERMISSION = 'permission';         // Izin
    case HOLIDAY = 'holiday';               // National holiday
    case OFFDAY = 'offday';                 // Scheduled day off
    case WORK_FROM_HOME = 'wfh';            // Remote work
    case BUSINESS_TRIP = 'business_trip';   // Dinas luar

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::PRESENT => 'Hadir',
            self::LATE => 'Terlambat',
            self::ABSENT => 'Alpha',
            self::LEAVE => 'Cuti',
            self::SICK => 'Sakit',
            self::PERMISSION => 'Izin',
            self::HOLIDAY => 'Libur Nasional',
            self::OFFDAY => 'Hari Libur',
            self::WORK_FROM_HOME => 'WFH',
            self::BUSINESS_TRIP => 'Dinas Luar',
        };
    }

    /**
     * Get badge color class (Tailwind)
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::PRESENT => 'bg-green-100 text-green-700',
            self::LATE => 'bg-yellow-100 text-yellow-700',
            self::ABSENT => 'bg-red-100 text-red-700',
            self::LEAVE => 'bg-blue-100 text-blue-700',
            self::SICK => 'bg-purple-100 text-purple-700',
            self::PERMISSION => 'bg-cyan-100 text-cyan-700',
            self::HOLIDAY => 'bg-pink-100 text-pink-700',
            self::OFFDAY => 'bg-gray-100 text-gray-700',
            self::WORK_FROM_HOME => 'bg-indigo-100 text-indigo-700',
            self::BUSINESS_TRIP => 'bg-teal-100 text-teal-700',
        };
    }

    /**
     * Check if this status counts as "present" for payroll (paid day)
     */
    public function isPaidDay(): bool
    {
        return match ($this) {
            self::PRESENT, self::LATE, self::LEAVE, self::SICK,
            self::PERMISSION, self::HOLIDAY, self::WORK_FROM_HOME,
            self::BUSINESS_TRIP => true,
            self::ABSENT, self::OFFDAY => false,
        };
    }

    /**
     * Check if this status counts as physically present
     */
    public function isPhysicallyPresent(): bool
    {
        return match ($this) {
            self::PRESENT, self::LATE, self::WORK_FROM_HOME, self::BUSINESS_TRIP => true,
            default => false,
        };
    }

    /**
     * Check if this is a non-working day
     */
    public function isNonWorkingDay(): bool
    {
        return match ($this) {
            self::HOLIDAY, self::OFFDAY => true,
            default => false,
        };
    }

    /**
     * Get status from string (for backward compatibility)
     */
    public static function fromString(string $status): self
    {
        return self::tryFrom($status) ?? self::ABSENT;
    }
}
