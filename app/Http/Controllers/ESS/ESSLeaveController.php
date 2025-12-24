<?php

namespace App\Http\Controllers\ESS;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ESSLeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('ess.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Leave balances
        $leaveBalances = EmployeeLeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get();

        // Leave requests
        $leaveRequests = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.ess.leave.index', compact('employee', 'leaveBalances', 'leaveRequests'));
    }

    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('ess.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $leaveTypes = LeaveType::active()->orderBy('name')->get();

        $leaveBalances = EmployeeLeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get()
            ->keyBy('leave_type_id');

        return view('admin.ess.leave.create', compact('employee', 'leaveTypes', 'leaveBalances'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('ess.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        // Calculate days
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Check balance
        $balance = EmployeeLeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', now()->year)
            ->first();

        if ($balance && $totalDays > $balance->remaining_balance) {
            return back()->withInput()->with('error', 'Sisa cuti tidak mencukupi.');
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('ess.leave.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function cancel($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $leaveRequest->update(['status' => 'cancelled']);

        return back()->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }
}
