<?php

namespace App\Contracts;

use App\Models\Employee;
use App\Services\Approval\ApproverResolution;

/**
 * Interface for approval resolvers
 * 
 * Resolvers determine who should approve a step based on:
 * - The requester (employee)
 * - The approval context (amount, department, etc.)
 * - The approver_value configuration
 */
interface ApproverResolverInterface
{
    /**
     * Resolve the approver(s) for a workflow step
     * 
     * @param Employee $requester The employee making the request
     * @param array<string, mixed> $context Approval context from requestable
     * @param string|null $value The approver_value from workflow step config
     * @return ApproverResolution Resolution result with status, approver IDs, and metadata
     */
    public function resolve(Employee $requester, array $context, ?string $value): ApproverResolution;

    /**
     * Get the approver type identifier for this resolver
     * 
     * @return string e.g., 'direct_supervisor', 'role', 'relative_level'
     */
    public function getType(): string;
}
