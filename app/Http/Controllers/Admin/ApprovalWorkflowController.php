<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowStep;
use App\Models\Level;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalWorkflowController extends Controller
{
    /**
     * Display a listing of workflows
     */
    public function index(Request $request)
    {
        $query = ApprovalWorkflow::withCount('steps');

        if ($request->filled('type')) {
            $query->forType($request->type);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $workflows = $query->orderBy('name')->paginate(20);

        // Stats
        $totalWorkflows = ApprovalWorkflow::count();
        $activeWorkflows = ApprovalWorkflow::active()->count();
        $workflowTypes = ApprovalWorkflow::distinct()->pluck('type');

        return view('admin.approval-workflows.index', compact(
            'workflows',
            'totalWorkflows',
            'activeWorkflows',
            'workflowTypes'
        ));
    }

    /**
     * Show the form for creating a new workflow
     */
    public function create()
    {
        $levels = Level::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.approval-workflows.create', compact('levels', 'users'));
    }

    /**
     * Store a newly created workflow
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'steps' => 'required|array|min:1',
            'steps.*.approver_type' => 'required|in:direct_supervisor,position_level,specific_user,next_level_up,second_level_up,relative_level,role,department_head,cost_center_owner',
            'steps.*.approver_value' => 'nullable|string|max:100',
            'steps.*.is_required' => 'nullable|boolean',
            'steps.*.can_skip_if_same' => 'nullable|boolean',
            'steps.*.on_resolution_fail' => 'nullable|in:fail_request,skip_step',
            'steps.*.failure_message' => 'nullable|string|max:255',
            'steps.*.conditions' => 'nullable|string', // JSON string
        ]);

        DB::beginTransaction();

        try {
            $workflow = ApprovalWorkflow::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            // Create steps
            foreach ($validated['steps'] as $index => $stepData) {
                // Parse conditions from JSON string
                $conditions = null;
                if (!empty($stepData['conditions'])) {
                    $parsed = json_decode($stepData['conditions'], true);
                    // Filter out empty conditions
                    $conditions = array_values(array_filter($parsed ?? [], fn($c) => !empty($c['field'])));
                    $conditions = empty($conditions) ? null : $conditions;
                }

                ApprovalWorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_order' => $index + 1,
                    'approver_type' => $stepData['approver_type'],
                    'approver_value' => $stepData['approver_value'] ?? null,
                    'is_required' => $stepData['is_required'] ?? true,
                    'can_skip_if_same' => $stepData['can_skip_if_same'] ?? true,
                    'on_resolution_fail' => $stepData['on_resolution_fail'] ?? 'fail_request',
                    'failure_message' => $stepData['failure_message'] ?? null,
                    'conditions' => $conditions,
                ]);
            }

            DB::commit();

            Log::info('Workflow created', ['workflow_id' => $workflow->id]);

            return redirect()
                ->route('admin.approval-workflows.index')
                ->with('success', 'Workflow berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Workflow creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified workflow
     */
    public function show(string $id)
    {
        $workflow = ApprovalWorkflow::with('steps')->findOrFail($id);

        return view('admin.approval-workflows.show', compact('workflow'));
    }

    /**
     * Show the form for editing the workflow
     */
    public function edit(string $id)
    {
        $workflow = ApprovalWorkflow::with('steps')->findOrFail($id);
        $levels = Level::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        // Prepare steps data for JavaScript
        $stepsData = $workflow->steps->map(function ($s) {
            return [
                'approver_type' => $s->approver_type,
                'approver_value' => $s->approver_value ?? '',
                'can_skip_if_same' => $s->can_skip_if_same,
                'on_resolution_fail' => $s->on_resolution_fail ?? 'fail_request',
                'failure_message' => $s->failure_message ?? '',
                'conditions' => $s->conditions ?? [],
            ];
        })->values();

        return view('admin.approval-workflows.edit', compact('workflow', 'levels', 'users', 'stepsData'));
    }

    /**
     * Update the specified workflow
     */
    public function update(Request $request, string $id)
    {
        $workflow = ApprovalWorkflow::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'steps' => 'required|array|min:1',
            'steps.*.approver_type' => 'required|in:direct_supervisor,position_level,specific_user,next_level_up,second_level_up,relative_level,role,department_head,cost_center_owner',
            'steps.*.approver_value' => 'nullable|string|max:100',
            'steps.*.is_required' => 'nullable|boolean',
            'steps.*.can_skip_if_same' => 'nullable|boolean',
            'steps.*.on_resolution_fail' => 'nullable|in:fail_request,skip_step',
            'steps.*.failure_message' => 'nullable|string|max:255',
            'steps.*.conditions' => 'nullable|string', // JSON string
        ]);

        DB::beginTransaction();

        try {
            $workflow->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            // Delete existing steps and recreate
            $workflow->steps()->delete();

            foreach ($validated['steps'] as $index => $stepData) {
                // Parse conditions from JSON string
                $conditions = null;
                if (!empty($stepData['conditions'])) {
                    $parsed = json_decode($stepData['conditions'], true);
                    // Filter out empty conditions
                    $conditions = array_values(array_filter($parsed ?? [], fn($c) => !empty($c['field'])));
                    $conditions = empty($conditions) ? null : $conditions;
                }

                ApprovalWorkflowStep::create([
                    'workflow_id' => $workflow->id,
                    'step_order' => $index + 1,
                    'approver_type' => $stepData['approver_type'],
                    'approver_value' => $stepData['approver_value'] ?? null,
                    'is_required' => $stepData['is_required'] ?? true,
                    'can_skip_if_same' => $stepData['can_skip_if_same'] ?? true,
                    'on_resolution_fail' => $stepData['on_resolution_fail'] ?? 'fail_request',
                    'failure_message' => $stepData['failure_message'] ?? null,
                    'conditions' => $conditions,
                ]);
            }

            DB::commit();

            Log::info('Workflow updated', ['workflow_id' => $workflow->id]);

            return redirect()
                ->route('admin.approval-workflows.index')
                ->with('success', 'Workflow berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Workflow update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified workflow
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $workflow = ApprovalWorkflow::findOrFail($id);
            $workflowName = $workflow->name;

            // Check if workflow has active requests
            if ($workflow->approvalRequests()->pending()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak dapat menghapus workflow yang memiliki request aktif.');
            }

            $workflow->delete();

            DB::commit();

            Log::info('Workflow deleted', ['workflow_id' => $id, 'name' => $workflowName]);

            return redirect()
                ->route('admin.approval-workflows.index')
                ->with('success', "Workflow \"{$workflowName}\" berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Workflow deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
