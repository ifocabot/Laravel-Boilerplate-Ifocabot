<?php

namespace App\Http\Controllers\ESS;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\LeaveType;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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

        // Leave requests with days
        $leaveRequests = LeaveRequest::with(['leaveType', 'days'])
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

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // Generate dates in the period
        $period = CarbonPeriod::create($startDate, $endDate);
        $dates = [];
        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }
        $totalDays = count($dates);

        // Check balance
        $balance = EmployeeLeaveBalance::getOrCreate(
            $employee->id,
            $validated['leave_type_id'],
            $startDate->year
        );

        if ($totalDays > $balance->remaining) {
            return back()->withInput()->with(
                'error',
                "Sisa cuti tidak mencukupi. Tersedia: {$balance->remaining} hari, dibutuhkan: {$totalDays} hari."
            );
        }

        // ⭐ Check overlap per day
        $overlappingDates = LeaveRequestDay::getOverlappingDates($employee->id, $dates);
        if (!empty($overlappingDates)) {
            $formattedDates = array_map(fn($d) => Carbon::parse($d)->format('d M Y'), $overlappingDates);
            return back()->withInput()->with(
                'error',
                'Tanggal berikut sudah ada cuti yang disetujui: ' . implode(', ', $formattedDates)
            );
        }

        // Create leave request with per-day records
        DB::transaction(function () use ($validated, $employee, $startDate, $endDate, $totalDays, $dates) {
            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $validated['leave_type_id'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'reason' => $validated['reason'],
                'status' => LeaveRequest::STATUS_PENDING,
            ]);

            // ⭐ Create per-day records
            foreach ($dates as $date) {
                LeaveRequestDay::create([
                    'leave_request_id' => $leaveRequest->id,
                    'date' => $date,
                    'day_value' => 1.0, // Full day default
                    'status' => LeaveRequestDay::STATUS_PENDING,
                ]);
            }

            // Submit to approval workflow if available
            try {
                $leaveRequest->submitForApproval();
            } catch (\Exception $e) {
                \Log::warning('Leave approval workflow not configured', [
                    'leave_id' => $leaveRequest->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        return redirect()->route('ess.leave.index')->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    public function cancel($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)
            ->where('id', $id)
            ->whereIn('status', [
                LeaveRequest::STATUS_PENDING,
                LeaveRequest::STATUS_NEEDS_HR_REVIEW
            ])
            ->firstOrFail();

        // Use the model's cancel method which handles ledger
        if ($leaveRequest->cancel()) {
            return back()->with('success', 'Pengajuan cuti berhasil dibatalkan.');
        }

        return back()->with('error', 'Tidak dapat membatalkan pengajuan cuti.');
    }
}

