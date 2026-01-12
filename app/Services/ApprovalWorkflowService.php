<?php

namespace App\Services;

use App\Contracts\Approvable;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\ApprovalEvent;
use App\Models\Employee;
use App\Services\Approval\ApproverResolverRegistry;
use App\Services\Approval\ApproverResolution;
use App\Services\Approval\ConditionEvaluator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService
{
    protected ApproverResolverRegistry $resolverRegistry;
    protected ConditionEvaluator $conditionEvaluator;

    public function __construct(
        ApproverResolverRegistry $resolverRegistry,
        ConditionEvaluator $conditionEvaluator
    ) {
        $this->resolverRegistry = $resolverRegistry;
        $this->conditionEvaluator = $conditionEvaluator;
    }

    /**
     * Submit a requestable for approval
     * 
     * @param Model $requestable The model needing approval (LeaveRequest, OvertimeRequest, etc)
     * @param string $workflowType The type of workflow to use
     * @param int $requesterId The employee ID of the requester
     * @return ApprovalRequest
     * @throws \Exception
     */
    public function submitForApproval(Model $requestable, string $workflowType, int $requesterId): ApprovalRequest
    {
        // ⭐ Guard: Block duplicate pending/needs_configuration requests
        $existingActive = ApprovalRequest::where('requestable_type', get_class($requestable))
            ->where('requestable_id', $requestable->id)
            ->whereIn('status', [
                ApprovalRequest::STATUS_PENDING,
                ApprovalRequest::STATUS_NEEDS_CONFIGURATION,
            ])
            ->first();

        if ($existingActive) {
            throw new \Exception(
                "Cannot submit: Approval request already exists with status '{$existingActive->status}'. " .
                "Cancel the existing request first."
            );
        }

        $workflow = ApprovalWorkflow::getActiveForType($workflowType);

        if (!$workflow) {
            throw new \Exception("No active workflow found for type: {$workflowType}");
        }

        $requester = Employee::findOrFail($requesterId);

        // Get context from requestable if it implements Approvable
        $context = [];
        if ($requestable instanceof Approvable) {
            $context = $requestable->getApprovalContext();
        } elseif (method_exists($requestable, 'getApprovalContext')) {
            $context = $requestable->getApprovalContext();
        }

        return DB::transaction(function () use ($workflow, $requestable, $requester, $context) {
            // Create approval request with context snapshot
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
                'requester_id' => $requester->id,
                'current_step' => 1,
                'status' => ApprovalRequest::STATUS_PENDING,
                'context' => $context,
                'submitted_at' => now(),
            ]);

            // Log creation event
            $this->logEvent($approvalRequest, ApprovalEvent::TYPE_CREATED, [
                'workflow_type' => $workflow->type,
                'context' => $context,
            ]);

            // Create approval steps based on workflow
            $result = $this->createApprovalSteps($approvalRequest, $workflow, $requester, $context);

            if (!$result['success']) {
                // Mark request as needs_configuration
                $approvalRequest->markNeedsConfiguration(
                    $result['failure_code'],
                    $result['failure_reason']
                );

                $this->logEvent($approvalRequest, ApprovalEvent::TYPE_FAILED_TO_RESOLVE, [
                    'failure_code' => $result['failure_code'],
                    'failure_reason' => $result['failure_reason'],
                ]);
            } else {
                // Check if we have any pending steps
                $pendingStepsCount = $approvalRequest->steps()->pending()->count();

                if ($pendingStepsCount === 0) {
                    // No pending steps = all were skipped
                    $approvalRequest->markNeedsConfiguration(
                        ApprovalRequest::FAILURE_NO_PENDING_STEPS,
                        'Tidak ada step approval yang aktif'
                    );

                    $this->logEvent($approvalRequest, ApprovalEvent::TYPE_FAILED_TO_RESOLVE, [
                        'failure_code' => ApprovalRequest::FAILURE_NO_PENDING_STEPS,
                    ]);
                }
            }

            Log::info('Approval request submitted', [
                'request_id' => $approvalRequest->id,
                'workflow_type' => $workflow->type,
                'requester_id' => $requester->id,
                'status' => $approvalRequest->status,
            ]);

            return $approvalRequest;
        });
    }

    /**
     * Create approval steps for the request
     * 
     * Creates ALL steps (even skipped ones) for audit trail.
     * Returns success status and failure info if applicable.
     */
    protected function createApprovalSteps(
        ApprovalRequest $request,
        ApprovalWorkflow $workflow,
        Employee $requester,
        array $context
    ): array {
        $previousApproverId = null;
        $hasFailure = false;
        $failureInfo = ['failure_code' => null, 'failure_reason' => null];

        foreach ($workflow->getStepsOrdered() as $step) {
            // Check for existing step (idempotency)
            $existingStep = ApprovalRequestStep::where('approval_request_id', $request->id)
                ->where('step_order', $step->step_order)
                ->first();

            if ($existingStep) {
                Log::info('Step already exists, skipping creation', [
                    'request_id' => $request->id,
                    'step_order' => $step->step_order,
                ]);
                continue;
            }

            // Evaluate conditions
            if (!$this->conditionEvaluator->matches($step->conditions, $context)) {
                // Create skipped step
                $this->createSkippedStep(
                    $request,
                    $step,
                    ApprovalRequestStep::SKIP_CONDITION_NOT_MET,
                    'Kondisi step tidak terpenuhi'
                );
                continue;
            }

            // Resolve approver
            $resolution = $this->resolverRegistry->resolve(
                $step->approver_type,
                $requester,
                $context,
                $step->approver_value
            );

            if ($resolution->isFailed()) {
                // Handle resolution failure
                if ($step->shouldSkipOnFail()) {
                    $this->createSkippedStep(
                        $request,
                        $step,
                        ApprovalRequestStep::SKIP_APPROVER_NOT_FOUND,
                        $resolution->reason ?? 'Tidak dapat menemukan approver'
                    );
                } else {
                    // Required step failed - this is a blocking failure
                    $hasFailure = true;
                    $failureInfo = [
                        'failure_code' => ApprovalRequest::FAILURE_NO_APPROVER,
                        'failure_reason' => $step->getFormattedFailureMessage($context),
                    ];

                    $this->logEvent($request, ApprovalEvent::TYPE_FAILED_TO_RESOLVE, [
                        'step_order' => $step->step_order,
                        'approver_type' => $step->approver_type,
                        'approver_value' => $step->approver_value,
                        'reason' => $resolution->reason,
                    ], null, $step->id);

                    // Still create the step but with null approver
                    $this->createPendingStep($request, $step, null, $resolution);
                }
                continue;
            }

            if ($resolution->isSkipped()) {
                $this->createSkippedStep(
                    $request,
                    $step,
                    $resolution->reason ?? ApprovalRequestStep::SKIP_APPROVER_NOT_FOUND,
                    $resolution->reason
                );
                continue;
            }

            // Got an approver
            $approverId = $resolution->getFirstApprover();

            // Check for same approver as previous (auto-skip)
            if ($step->can_skip_if_same && $previousApproverId && $approverId === $previousApproverId) {
                $this->createSkippedStep(
                    $request,
                    $step,
                    ApprovalRequestStep::SKIP_SAME_APPROVER,
                    'Approver sama dengan step sebelumnya'
                );
                continue;
            }

            // Create the pending step
            $this->createPendingStep($request, $step, $approverId, $resolution);
            $previousApproverId = $approverId;
        }

        return [
            'success' => !$hasFailure,
            'failure_code' => $failureInfo['failure_code'],
            'failure_reason' => $failureInfo['failure_reason'],
        ];
    }

    /**
     * Create a pending step with approver
     */
    protected function createPendingStep(
        ApprovalRequest $request,
        ApprovalWorkflowStep $workflowStep,
        ?int $approverId,
        ApproverResolution $resolution
    ): ApprovalRequestStep {
        $step = ApprovalRequestStep::create([
            'approval_request_id' => $request->id,
            'workflow_step_id' => $workflowStep->id,
            'step_order' => $workflowStep->step_order,
            'approver_type' => $workflowStep->approver_type,
            'approver_value' => $workflowStep->approver_value,
            'conditions_snapshot' => $workflowStep->conditions,
            'approver_id' => $approverId,
            'resolver_type' => $this->resolverRegistry->get($workflowStep->approver_type)?->getType(),
            'resolved_at' => $approverId ? now() : null,
            'status' => ApprovalRequestStep::STATUS_PENDING,
        ]);

        $this->logEvent($request, ApprovalEvent::TYPE_STEP_CREATED, [
            'step_order' => $workflowStep->step_order,
            'approver_id' => $approverId,
            'approver_type' => $workflowStep->approver_type,
            'resolver_meta' => $resolution->meta,
        ], $step->id, $workflowStep->id);

        return $step;
    }

    /**
     * Create a skipped step
     */
    protected function createSkippedStep(
        ApprovalRequest $request,
        ApprovalWorkflowStep $workflowStep,
        string $skipReason,
        ?string $notes = null
    ): ApprovalRequestStep {
        $step = ApprovalRequestStep::create([
            'approval_request_id' => $request->id,
            'workflow_step_id' => $workflowStep->id,
            'step_order' => $workflowStep->step_order,
            'approver_type' => $workflowStep->approver_type,
            'approver_value' => $workflowStep->approver_value,
            'conditions_snapshot' => $workflowStep->conditions,
            'approver_id' => null,
            'skip_reason' => $skipReason,
            'status' => ApprovalRequestStep::STATUS_SKIPPED,
            'notes' => $notes,
            'actioned_at' => now(),
        ]);

        $this->logEvent($request, ApprovalEvent::TYPE_STEP_SKIPPED, [
            'step_order' => $workflowStep->step_order,
            'skip_reason' => $skipReason,
            'notes' => $notes,
        ], $step->id, $workflowStep->id);

        return $step;
    }

    /**
     * Process approval action with concurrency safety
     */
    public function processApproval(ApprovalRequest $request, int $userId, string $action, ?string $notes = null): array
    {
        if (!in_array($action, ['approve', 'reject'])) {
            throw new \InvalidArgumentException("Invalid action: {$action}");
        }

        return DB::transaction(function () use ($request, $userId, $action, $notes) {
            // Lock the request and current step
            $request = ApprovalRequest::where('id', $request->id)
                ->lockForUpdate()
                ->first();

            if (!$request || $request->status !== ApprovalRequest::STATUS_PENDING) {
                return [
                    'success' => false,
                    'error' => 'Request is not pending',
                    'code' => 'INVALID_STATUS',
                ];
            }

            // Get and lock the current step with guard
            $currentStep = ApprovalRequestStep::where('approval_request_id', $request->id)
                ->where('step_order', $request->current_step)
                ->where('status', ApprovalRequestStep::STATUS_PENDING)
                ->where('approver_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$currentStep) {
                $this->logEvent($request, ApprovalEvent::TYPE_CONCURRENCY_CONFLICT, [
                    'action' => $action,
                    'user_id' => $userId,
                    'current_step' => $request->current_step,
                ]);

                return [
                    'success' => false,
                    'error' => 'Cannot action this step - already processed or not your turn',
                    'code' => 'CONCURRENCY_CONFLICT',
                ];
            }

            // Perform the action with guard update
            $fromStatus = $currentStep->status;
            $toStatus = $action === 'approve'
                ? ApprovalRequestStep::STATUS_APPROVED
                : ApprovalRequestStep::STATUS_REJECTED;

            $affected = ApprovalRequestStep::where('id', $currentStep->id)
                ->where('status', ApprovalRequestStep::STATUS_PENDING)
                ->update([
                    'status' => $toStatus,
                    'notes' => $notes,
                    'actioned_at' => now(),
                ]);

            if ($affected === 0) {
                $this->logEvent($request, ApprovalEvent::TYPE_CONCURRENCY_CONFLICT, [
                    'action' => $action,
                    'step_id' => $currentStep->id,
                ]);

                return [
                    'success' => false,
                    'error' => 'Concurrency conflict - step was modified',
                    'code' => 'CONCURRENCY_CONFLICT',
                ];
            }

            // Log the action
            $eventType = $action === 'approve'
                ? ApprovalEvent::TYPE_APPROVED
                : ApprovalEvent::TYPE_REJECTED;

            $this->logEvent($request, $eventType, [
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'notes' => $notes,
            ], $currentStep->id);

            // Handle post-action logic
            if ($action === 'approve') {
                $request->moveToNextStep();
            } else {
                $request->update([
                    'status' => ApprovalRequest::STATUS_REJECTED,
                    'completed_at' => now(),
                ]);

                // Trigger callback
                $requestable = $request->requestable;
                if ($requestable && method_exists($requestable, 'onWorkflowRejected')) {
                    $requestable->onWorkflowRejected($request, $notes);
                }
            }

            Log::info("Approval {$action}d", [
                'request_id' => $request->id,
                'user_id' => $userId,
                'action' => $action,
            ]);

            return [
                'success' => true,
                'action' => $action,
                'new_status' => $request->fresh()->status,
            ];
        });
    }

    /**
     * Log an audit event
     */
    protected function logEvent(
        ApprovalRequest $request,
        string $eventType,
        array $payload = [],
        ?int $stepId = null,
        ?int $workflowStepId = null
    ): ApprovalEvent {
        return ApprovalEvent::log(
            $request->id,
            $eventType,
            auth()->id(),
            $payload,
            $stepId,
            $workflowStepId
        );
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovalsForUser(int $userId): Collection
    {
        return ApprovalRequest::pending()
            ->forApprover($userId)
            ->with(['workflow', 'requester', 'steps', 'requestable'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Get approval history for a user (as approver)
     */
    public function getApprovalHistoryForUser(int $userId, int $limit = 50): Collection
    {
        return ApprovalRequestStep::where('approver_id', $userId)
            ->whereIn('status', [ApprovalRequestStep::STATUS_APPROVED, ApprovalRequestStep::STATUS_REJECTED])
            ->with(['approvalRequest.workflow', 'approvalRequest.requester'])
            ->orderBy('actioned_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all approval requests for a requester
     */
    public function getRequestsForRequester(int $requesterId): Collection
    {
        return ApprovalRequest::forRequester($requesterId)
            ->with(['workflow', 'steps', 'requestable'])
            ->orderBy('submitted_at', 'desc')
            ->get();
    }

    /**
     * Check if user can approve a specific request
     */
    public function canUserApprove(ApprovalRequest $request, int $userId): bool
    {
        return $request->canBeActionedBy($userId);
    }

    /**
     * Get audit log for a request
     */
    public function getAuditLog(ApprovalRequest $request): Collection
    {
        return $request->events()
            ->with(['actor', 'approvalRequestStep', 'workflowStep'])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
