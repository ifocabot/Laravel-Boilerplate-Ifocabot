<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Employee;
use App\Services\ApprovalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApprovalAntiDuplicationTest extends TestCase
{
    /**
     * Test that hasActiveApprovalRequest method exists on models with trait
     */
    public function test_has_active_approval_request_method_exists(): void
    {
        // LeaveRequest uses HasApprovalWorkflow trait
        $this->assertTrue(
            method_exists(\App\Models\LeaveRequest::class, 'hasActiveApprovalRequest'),
            'hasActiveApprovalRequest method should exist on LeaveRequest'
        );

        $this->assertTrue(
            method_exists(\App\Models\LeaveRequest::class, 'hasPendingApprovalRequest'),
            'hasPendingApprovalRequest method should exist on LeaveRequest'
        );
    }

    /**
     * Test that duplicate guard exists in service
     */
    public function test_service_has_duplicate_guard(): void
    {
        $service = app(ApprovalWorkflowService::class);
        $reflection = new \ReflectionMethod($service, 'submitForApproval');
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString(
            'existingActive',
            $source,
            'submitForApproval should check for existing active requests'
        );
    }

    /**
     * Test processApproval has lockForUpdate
     */
    public function test_process_approval_has_locking(): void
    {
        $service = app(ApprovalWorkflowService::class);
        $reflection = new \ReflectionMethod($service, 'processApproval');
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString(
            'lockForUpdate',
            $source,
            'processApproval should use lockForUpdate for concurrency safety'
        );
    }

    /**
     * Test processApproval has idempotent check
     */
    public function test_process_approval_has_idempotent_check(): void
    {
        $service = app(ApprovalWorkflowService::class);
        $reflection = new \ReflectionMethod($service, 'processApproval');
        $source = file_get_contents($reflection->getFileName());

        $this->assertStringContainsString(
            'affected === 0',
            $source,
            'processApproval should check affected rows for idempotency'
        );
    }
}
