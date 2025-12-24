<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    protected ApprovalWorkflowService $workflowService;

    public function __construct(ApprovalWorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display pending approvals for current user
     */
    public function pending()
    {
        $userId = Auth::id();
        $pendingApprovals = $this->workflowService->getPendingApprovalsForUser($userId);

        return view('user.approvals.pending', compact('pendingApprovals'));
    }

    /**
     * Approve a request
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $approvalRequest = ApprovalRequest::findOrFail($id);
        $userId = Auth::id();

        $result = $this->workflowService->processApproval(
            $approvalRequest,
            $userId,
            'approve',
            $validated['notes'] ?? null
        );

        if ($result) {
            return redirect()
                ->route('approvals.pending')
                ->with('success', 'Request berhasil disetujui.');
        }

        return redirect()
            ->back()
            ->with('error', 'Anda tidak memiliki akses untuk menyetujui request ini.');
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $approvalRequest = ApprovalRequest::findOrFail($id);
        $userId = Auth::id();

        $result = $this->workflowService->processApproval(
            $approvalRequest,
            $userId,
            'reject',
            $validated['notes']
        );

        if ($result) {
            return redirect()
                ->route('approvals.pending')
                ->with('success', 'Request berhasil ditolak.');
        }

        return redirect()
            ->back()
            ->with('error', 'Anda tidak memiliki akses untuk menolak request ini.');
    }

    /**
     * Display approval history
     */
    public function history()
    {
        $userId = Auth::id();
        $history = $this->workflowService->getApprovalHistoryForUser($userId);

        return view('user.approvals.history', compact('history'));
    }

    /**
     * Show approval request detail
     */
    public function show($id)
    {
        $approvalRequest = ApprovalRequest::with([
            'workflow',
            'requester',
            'steps.approver',
            'requestable'
        ])->findOrFail($id);

        $canApprove = $this->workflowService->canUserApprove($approvalRequest, Auth::id());

        return view('user.approvals.show', compact('approvalRequest', 'canApprove'));
    }
}
