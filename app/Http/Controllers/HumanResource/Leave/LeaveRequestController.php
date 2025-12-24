<?php

namespace App\Http\Controllers\HumanResource\Leave;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeLeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of leave requests (for current employee)
     */
    public function index(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai karyawan.');
        }

        $query = LeaveRequest::forEmployee($employee->id)
            ->with(['leaveType', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get leave balances
        $currentYear = now()->year;
        $balances = EmployeeLeaveBalance::forEmployee($employee->id)
            ->forYear($currentYear)
            ->with('leaveType')
            ->get();

        return view('admin.hris.leave.requests.index', compact(
            'leaveRequests',
            'balances',
            'employee'
        ));
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai karyawan.');
        }

        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        // Get balances for current year
        $currentYear = now()->year;
        $balances = [];
        foreach ($leaveTypes as $type) {
            $balance = EmployeeLeaveBalance::getOrCreate($employee->id, $type->id, $currentYear);
            $balances[$type->id] = $balance;
        }

        return view('admin.hris.leave.requests.create', compact(
            'leaveTypes',
            'balances',
            'employee'
        ));
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request)
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai karyawan.');
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        try {
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $totalDays = LeaveRequest::calculateTotalDays($startDate, $endDate);

            $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

            // Check max consecutive days
            if ($leaveType->max_consecutive_days && $totalDays > $leaveType->max_consecutive_days) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Maksimal {$leaveType->max_consecutive_days} hari berturut-turut untuk {$leaveType->name}.");
            }

            // Check balance
            $balance = EmployeeLeaveBalance::getOrCreate($employee->id, $leaveType->id, $startDate->year);
            if (!$balance->hasSufficientBalance($totalDays)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Sisa cuti tidak mencukupi. Tersedia: {$balance->remaining} hari.");
            }

            // Check overlap
            $hasOverlap = LeaveRequest::approved()
                ->forEmployee($employee->id)
                ->inDateRange($startDate, $endDate)
                ->exists();

            if ($hasOverlap) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Tanggal cuti bertabrakan dengan cuti yang sudah disetujui.');
            }

            // Handle attachment
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            }

            // Check if attachment required
            if ($leaveType->requires_attachment && !$attachmentPath) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "{$leaveType->name} memerlukan lampiran.");
            }

            // Create leave request
            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'reason' => $validated['reason'] ?? null,
                'attachment_path' => $attachmentPath,
                'status' => LeaveRequest::STATUS_PENDING,
            ]);

            // Submit to approval workflow
            try {
                $approvalRequest = $leaveRequest->submitForApproval();
                Log::info('Leave request submitted to workflow', [
                    'leave_id' => $leaveRequest->id,
                    'approval_request_id' => $approvalRequest->id,
                    'steps_created' => $approvalRequest->steps()->count(),
                ]);
            } catch (\Exception $e) {
                Log::error('Approval workflow submission failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'leave_id' => $leaveRequest->id,
                ]);
            }

            Log::info('Leave request created', [
                'id' => $leaveRequest->id,
                'employee_id' => $employee->id,
                'type' => $leaveType->code,
                'days' => $totalDays,
            ]);

            return redirect()
                ->route('hris.leave.requests.index')
                ->with('success', 'Pengajuan cuti berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Leave request creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified leave request
     */
    public function show(string $id)
    {
        $leaveRequest = LeaveRequest::with(['employee', 'leaveType', 'approver', 'approvalRequest.steps'])
            ->findOrFail($id);

        return view('admin.hris.leave.requests.show', compact('leaveRequest'));
    }

    /**
     * Cancel a leave request
     */
    public function cancel(string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $employee = Auth::user()->employee;

        // Only the requester can cancel their own request
        if ($leaveRequest->employee_id !== $employee?->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat membatalkan pengajuan ini.');
        }

        if ($leaveRequest->cancel()) {
            Log::info('Leave request cancelled', ['id' => $id]);
            return redirect()
                ->route('hris.leave.requests.index')
                ->with('success', 'Pengajuan cuti berhasil dibatalkan.');
        }

        return redirect()->back()->with('error', 'Tidak dapat membatalkan pengajuan ini.');
    }

    /**
     * Approve a leave request (for approvers)
     */
    public function approve(Request $request, string $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->approve(Auth::id())) {
            Log::info('Leave request approved', [
                'id' => $id,
                'approved_by' => Auth::id(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'Pengajuan cuti berhasil disetujui.');
        }

        return redirect()->back()->with('error', 'Gagal menyetujui pengajuan cuti.');
    }

    /**
     * Reject a leave request (for approvers)
     */
    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->reject(Auth::id(), $validated['rejection_reason'])) {
            Log::info('Leave request rejected', [
                'id' => $id,
                'rejected_by' => Auth::id(),
            ]);

            return redirect()
                ->back()
                ->with('success', 'Pengajuan cuti berhasil ditolak.');
        }

        return redirect()->back()->with('error', 'Gagal menolak pengajuan cuti.');
    }

    /**
     * Admin: List all leave requests with approval dashboard
     */
    public function adminIndex(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'approver']);

        // Default to pending if no filter specified
        $status = $request->input('status', 'pending');
        if ($status) {
            $query->where('status', $status);
        }

        if ($request->filled('employee_id')) {
            $query->forEmployee($request->employee_id);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(20);
        $employees = Employee::orderBy('full_name')->get();

        // Stats
        $approvedThisMonth = LeaveRequest::approved()
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->count();

        $rejectedThisMonth = LeaveRequest::rejected()
            ->whereMonth('approved_at', now()->month)
            ->whereYear('approved_at', now()->year)
            ->count();

        return view('admin.hris.leave.requests.admin-index', compact(
            'leaveRequests',
            'employees',
            'approvedThisMonth',
            'rejectedThisMonth'
        ));
    }
}
