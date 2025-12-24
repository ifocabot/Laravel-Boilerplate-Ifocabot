<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OvertimeRequestController extends Controller
{
    /**
     * Display overtime requests list
     */
    public function index(Request $request)
    {
        $status = $request->input('status');
        $employeeId = $request->input('employee_id');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $query = OvertimeRequest::with(['employee.currentCareer.department', 'approver'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($status) {
            $query->where('status', $status);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $requests = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Get employees for filter
        $employees = Employee::where('status', 'active')->orderBy('full_name')->get();

        // Statistics
        $stats = OvertimeRequest::getStatistics(
            Carbon::create($year, $month, 1)->startOfMonth(),
            Carbon::create($year, $month, 1)->endOfMonth()
        );

        return view('admin.hris.attendance.overtime.index', compact(
            'requests',
            'employees',
            'stats',
            'year',
            'month'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $employees = Employee::where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('admin.hris.attendance.overtime.create', compact('employees'));
    }

    /**
     * Store overtime request
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date|after_or_equal:today',
                'start_at' => 'required|date_format:H:i',
                'end_at' => 'required|date_format:H:i|after:start_at',
                'reason' => 'required|string|max:1000',
                'work_description' => 'nullable|string|max:2000',
            ]);

            DB::beginTransaction();

            // Create request using model method (with validation)
            $overtimeRequest = OvertimeRequest::createRequest($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request overtime berhasil dibuat',
                'data' => $overtimeRequest,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Create Overtime Request Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show overtime request detail
     */
    public function show($id)
    {
        $request = OvertimeRequest::with([
            'employee.currentCareer.department',
            'employee.currentCareer.position',
            'approver',
            'cancelledBy'
        ])->findOrFail($id);

        return view('admin.hris.attendance.overtime.show', compact('request'));
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $overtimeRequest = OvertimeRequest::findOrFail($id);

        if (!$overtimeRequest->is_pending) {
            return redirect()
                ->route('hris.attendance.overtime.show', $id)
                ->with('error', 'Hanya request pending yang bisa diedit');
        }

        $employees = Employee::where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('admin.hris.attendance.overtime.edit', compact('overtimeRequest', 'employees'));
    }

    /**
     * Update overtime request
     */
    public function update(Request $request, $id)
    {
        try {
            $overtimeRequest = OvertimeRequest::findOrFail($id);

            if (!$overtimeRequest->is_pending) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya request pending yang bisa diupdate',
                ], 422);
            }

            $validated = $request->validate([
                'date' => 'required|date|after_or_equal:today',
                'start_at' => 'required|date_format:H:i',
                'end_at' => 'required|date_format:H:i|after:start_at',
                'reason' => 'required|string|max:1000',
                'work_description' => 'nullable|string|max:2000',
            ]);

            DB::beginTransaction();

            // Update using model method (with validation)
            $overtimeRequest->updateRequest($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request overtime berhasil diupdate',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Update Overtime Request Error', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve overtime request
     * ✅ AUTO-SYNC to attendance_summaries
     */
    public function approve(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'approved_minutes' => 'nullable|integer|min:0',
                'notes' => 'nullable|string|max:500',
            ]);

            $overtimeRequest = OvertimeRequest::findOrFail($id);

            if (!$overtimeRequest->is_pending) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request sudah diproses sebelumnya',
                ], 422);
            }

            DB::beginTransaction();

            $approvedMinutes = $validated['approved_minutes'] ?? $overtimeRequest->duration_minutes;

            // ✅ Approve will AUTO-SYNC to attendance_summaries
            $overtimeRequest->approve(
                Auth::id(),
                $approvedMinutes,
                $validated['notes'] ?? null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request overtime berhasil disetujui dan sinkronisasi ke attendance summary',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Approve Overtime Request Error', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject overtime request
     * ✅ AUTO-SYNC to attendance_summaries
     */
    public function reject(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $overtimeRequest = OvertimeRequest::findOrFail($id);

            if (!$overtimeRequest->is_pending) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request sudah diproses sebelumnya',
                ], 422);
            }

            DB::beginTransaction();

            // ✅ Reject will AUTO-SYNC to attendance_summaries (clear approved overtime)
            $overtimeRequest->reject(Auth::id(), $validated['reason']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request overtime ditolak dan sinkronisasi ke attendance summary',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Reject Overtime Request Error', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel overtime request
     * ✅ AUTO-SYNC to attendance_summaries
     */
    public function cancel(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $overtimeRequest = OvertimeRequest::findOrFail($id);

            if (!$overtimeRequest->can_be_cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request tidak dapat dibatalkan',
                ], 422);
            }

            DB::beginTransaction();

            // ✅ Cancel will AUTO-SYNC to attendance_summaries (clear approved overtime)
            $overtimeRequest->cancel(Auth::id(), $validated['reason']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request overtime dibatalkan dan sinkronisasi ke attendance summary',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Cancel Overtime Request Error', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk approve
     * ✅ AUTO-SYNC to attendance_summaries for each request
     */
    public function bulkApprove(Request $request)
    {
        try {
            $validated = $request->validate([
                'request_ids' => 'required|array',
                'request_ids.*' => 'exists:overtime_requests,id',
            ]);

            DB::beginTransaction();

            $approved = 0;
            $errors = [];

            foreach ($validated['request_ids'] as $id) {
                try {
                    $overtimeRequest = OvertimeRequest::find($id);

                    if ($overtimeRequest && $overtimeRequest->is_pending) {
                        // ✅ Approve will AUTO-SYNC to attendance_summaries
                        $overtimeRequest->approve(Auth::id());
                        $approved++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Request ID {$id}: " . $e->getMessage();
                    Log::error("Bulk Approve Error for ID {$id}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            $message = "{$approved} request overtime berhasil disetujui";
            if (!empty($errors)) {
                $message .= ". Beberapa request gagal: " . implode(', ', $errors);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'approved_count' => $approved,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk Approve Overtime Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete overtime request
     */
    public function destroy($id)
    {
        try {
            $overtimeRequest = OvertimeRequest::findOrFail($id);

            if (!$overtimeRequest->is_pending) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya request pending yang bisa dihapus',
                ], 422);
            }

            DB::beginTransaction();

            $overtimeRequest->delete();

            DB::commit();

            Log::info('Overtime request deleted', [
                'id' => $id,
                'deleted_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Request overtime berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Delete Overtime Request Error', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approval dashboard
     */
    public function approvals(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = OvertimeRequest::with(['employee.currentCareer.department', 'approver']);

        if ($status === 'pending') {
            $query->pending();
        } elseif ($status === 'approved') {
            $query->approved();
        } elseif ($status === 'rejected') {
            $query->rejected();
        }

        $requests = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends(['status' => $status]);

        $stats = [
            'pending' => OvertimeRequest::pending()->count(),
            'approved' => OvertimeRequest::approved()->count(),
            'rejected' => OvertimeRequest::rejected()->count(),
            'total_pending_hours' => OvertimeRequest::pending()->sum('duration_minutes') / 60,
        ];

        return view('admin.hris.attendance.overtime.approvals', compact('requests', 'stats', 'status'));
    }
}