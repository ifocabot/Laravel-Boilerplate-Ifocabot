<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowService
{
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
        $workflow = ApprovalWorkflow::getActiveForType($workflowType);

        if (!$workflow) {
            throw new \Exception("No active workflow found for type: {$workflowType}");
        }

        $requester = Employee::findOrFail($requesterId);

        return DB::transaction(function () use ($workflow, $requestable, $requester) {
            // Create approval request
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'requestable_type' => get_class($requestable),
                'requestable_id' => $requestable->id,
                'requester_id' => $requester->id,
                'current_step' => 1,
                'status' => ApprovalRequest::STATUS_PENDING,
                'submitted_at' => now(),
            ]);

            // Create approval steps based on workflow
            $this->createApprovalSteps($approvalRequest, $workflow, $requester);

            Log::info('Approval request submitted', [
                'request_id' => $approvalRequest->id,
                'workflow_type' => $workflow->type,
                'requester_id' => $requester->id,
            ]);

            return $approvalRequest;
        });
    }

    /**
     * Create approval steps for the request
     */
    protected function createApprovalSteps(ApprovalRequest $request, ApprovalWorkflow $workflow, Employee $requester): void
    {
        $previousApprover = null;

        foreach ($workflow->getStepsOrdered() as $step) {
            $approver = $step->resolveApprover($requester);

            // Skip if can_skip_if_same and same approver as previous
            if (
                $step->can_skip_if_same &&
                $previousApprover &&
                $approver &&
                $previousApprover->id === $approver->id
            ) {
                continue;
            }

            ApprovalRequestStep::create([
                'approval_request_id' => $request->id,
                'step_order' => $step->step_order,
                'approver_id' => $approver?->id,
                'status' => ApprovalRequestStep::STATUS_PENDING,
            ]);

            $previousApprover = $approver;
        }
    }

    /**
     * Process approval action (approve or reject)
     */
    public function processApproval(ApprovalRequest $request, int $userId, string $action, ?string $notes = null): bool
    {
        if (!in_array($action, ['approve', 'reject'])) {
            throw new \InvalidArgumentException("Invalid action: {$action}");
        }

        return DB::transaction(function () use ($request, $userId, $action, $notes) {
            if ($action === 'approve') {
                $result = $request->approveStep($userId, $notes);
            } else {
                $result = $request->rejectStep($userId, $notes);
            }

            if ($result) {
                Log::info("Approval {$action}d", [
                    'request_id' => $request->id,
                    'user_id' => $userId,
                    'action' => $action,
                ]);
            }

            return $result;
        });
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
}
