<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AttendanceAdjustment;
use App\Models\LeaveRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceLedgerTest extends TestCase
{
    /**
     * Test AttendanceAdjustment model exists
     */
    public function test_attendance_adjustment_model_exists(): void
    {
        $this->assertTrue(
            class_exists(AttendanceAdjustment::class),
            'AttendanceAdjustment model should exist'
        );
    }

    /**
     * Test adjustment type constants exist
     */
    public function test_adjustment_type_constants_exist(): void
    {
        $this->assertEquals('leave', AttendanceAdjustment::TYPE_LEAVE);
        $this->assertEquals('sick', AttendanceAdjustment::TYPE_SICK);
        $this->assertEquals('overtime_add', AttendanceAdjustment::TYPE_OVERTIME_ADD);
        $this->assertEquals('manual_override', AttendanceAdjustment::TYPE_MANUAL_OVERRIDE);
    }

    /**
     * Test createForLeave static method exists
     */
    public function test_create_for_leave_method_exists(): void
    {
        $this->assertTrue(
            method_exists(AttendanceAdjustment::class, 'createForLeave'),
            'createForLeave method should exist'
        );
    }

    /**
     * Test getActiveForDate static method exists
     */
    public function test_get_active_for_date_method_exists(): void
    {
        $this->assertTrue(
            method_exists(AttendanceAdjustment::class, 'getActiveForDate'),
            'getActiveForDate method should exist'
        );
    }

    /**
     * Test LeaveRequest has mapLeaveTypeToStatus method
     */
    public function test_leave_request_has_map_leave_type_to_status(): void
    {
        $reflection = new \ReflectionClass(LeaveRequest::class);
        $this->assertTrue(
            $reflection->hasMethod('mapLeaveTypeToStatus'),
            'LeaveRequest should have mapLeaveTypeToStatus method'
        );
    }
}
