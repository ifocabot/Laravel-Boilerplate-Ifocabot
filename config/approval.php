<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Approval Workflow Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the ERP-ready approval workflow system.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Approver Resolvers
    |--------------------------------------------------------------------------
    |
    | Map of approver_type to resolver class. Add custom resolvers here.
    |
    */
    'resolvers' => [
        'direct_supervisor' => \App\Services\Approval\Resolvers\DirectSupervisorResolver::class,
        'relative_level' => \App\Services\Approval\Resolvers\RelativeLevelResolver::class,
        'role' => \App\Services\Approval\Resolvers\RoleResolver::class,
        'specific_user' => \App\Services\Approval\Resolvers\SpecificUserResolver::class,
        'department_head' => \App\Services\Approval\Resolvers\DepartmentHeadResolver::class,
        'cost_center_owner' => \App\Services\Approval\Resolvers\CostCenterOwnerResolver::class,

        // Legacy types (mapped to new resolvers for backward compatibility)
        'position_level' => \App\Services\Approval\Resolvers\RelativeLevelResolver::class,
        'next_level_up' => \App\Services\Approval\Resolvers\RelativeLevelResolver::class,
        'second_level_up' => \App\Services\Approval\Resolvers\RelativeLevelResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Failure Codes
    |--------------------------------------------------------------------------
    |
    | Standard failure codes for system analytics and error handling.
    |
    */
    'failure_codes' => [
        'NO_APPROVER_RESOLVED' => 'No approver could be determined',
        'INVALID_CONDITION' => 'Step condition evaluation failed',
        'CONCURRENCY_CONFLICT' => 'Concurrent modification detected',
        'WORKFLOW_NOT_FOUND' => 'No active workflow for this type',
        'CONTEXT_MISSING' => 'Required context data is missing',
        'RESOLVER_NOT_FOUND' => 'No resolver registered for approver type',
        'NO_PENDING_STEPS' => 'No pending steps after step creation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Skip Reasons
    |--------------------------------------------------------------------------
    |
    | Standard skip reasons for step skipping.
    |
    */
    'skip_reasons' => [
        'condition_not_met' => 'Step conditions not satisfied',
        'same_approver' => 'Same approver as previous step (auto-skip)',
        'approver_not_found' => 'Could not resolve approver (optional step)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Types
    |--------------------------------------------------------------------------
    |
    | Standard event types for audit logging.
    |
    */
    'event_types' => [
        'created',
        'step_created',
        'step_skipped',
        'step_resolved',
        'approved',
        'rejected',
        'failed_to_resolve',
        'escalated',
        'concurrency_conflict',
        'cancelled',
    ],
];
