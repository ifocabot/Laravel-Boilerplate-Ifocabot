<?php

namespace App\Http\Controllers\HumanResource\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollAdjustment;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollAdjustmentController extends Controller
{
    /**
     * Display listing of adjustments
     */
    public function index(Request $request)
    {
        $periodId = $request->input('period_id');
        $employeeId = $request->input('employee_id');
        $status = $request->input('status');
        $type = $request->input('type');

        $query = PayrollAdjustment::with([
            'employee' => fn($q) => $q->select('id', 'full_name', 'nik'),
            'payrollPeriod' => fn($q) => $q->select('id', 'period_name'),
            'createdByUser' => fn($q) => $q->select('id', 'name'),
            'approvedByUser' => fn($q) => $q->select('id', 'name'),
        ]);

        if ($periodId) {
            $query->where('payroll_period_id', $periodId);
        }

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $adjustments = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Statistics
        $stats = [
            'pending' => PayrollAdjustment::pending()->count(),
            'approved' => PayrollAdjustment::approved()->count(),
            'total_overtime_pending' => PayrollAdjustment::pending()
                ->where('type', 'overtime')
                ->sum('amount_minutes'),
        ];

        $periods = PayrollPeriod::orderBy('start_date', 'desc')
            ->take(12)
            ->get(['id', 'period_name']);

        $employees = Employee::where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nik']);

        return view('admin.hris.payroll.adjustments.index', compact(
            'adjustments',
            'stats',
            'periods',
            'employees'
        ));
    }

    /**
     * Show single adjustment
     */
    public function show($id)
    {
        $adjustment = PayrollAdjustment::with([
            'employee.currentCareer.department',
            'payrollPeriod',
            'sourcePeriod',
            'createdByUser',
            'approvedByUser',
        ])->findOrFail($id);

        return view('admin.hris.payroll.adjustments.show', compact('adjustment'));
    }

    /**
     * Create new adjustment form
     */
    public function create()
    {
        $periods = PayrollPeriod::where('attendance_locked', false)
            ->orWhere('status', 'draft')
            ->orderBy('start_date', 'desc')
            ->get(['id', 'period_name', 'start_date', 'end_date']);

        $employees = Employee::where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nik']);

        $types = [
            'overtime' => 'Overtime',
            'leave_correction' => 'Koreksi Cuti',
            'attendance_correction' => 'Koreksi Kehadiran',
            'late_correction' => 'Koreksi Keterlambatan',
            'schedule_change' => 'Perubahan Jadwal',
            'manual' => 'Manual',
            'other' => 'Lainnya',
        ];

        return view('admin.hris.payroll.adjustments.create', compact(
            'periods',
            'employees',
            'types'
        ));
    }

    /**
     * Store new adjustment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'payroll_period_id' => [
                'required',
                'exists:payroll_periods,id',
                // Custom validation: Check period status
                function ($attribute, $value, $fail) use ($request) {
                    $period = PayrollPeriod::find($value);
                    if (!$period)
                        return;

                    // Block if period is already finalized (paid/closed)
                    if (in_array($period->status, ['paid', 'closed'])) {
                        $fail('Tidak dapat membuat adjustment pada periode yang sudah dibayar/ditutup.');
                    }

                    // NOTE: We intentionally DO NOT block attendance types when locked.
                    // Adjustments are MEANT for retroactive fixes after lock.
                    // The adjustment will be applied to THIS period (if still draft)
                    // or redirected to next period automatically by the engine.
                },
            ],
            'source_date' => 'nullable|date',
            'type' => 'required|in:overtime,leave_correction,attendance_correction,late_correction,schedule_change,manual,other',
            'amount_minutes' => 'nullable|integer', // Signed: positive = add, negative = deduct
            'amount_days' => 'nullable|numeric',    // Signed: positive = add, negative = deduct
            'amount_money' => 'nullable|numeric',   // FIX 3: Signed allowed (no min:0)
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['status'] = 'pending';

        $adjustment = PayrollAdjustment::create($validated);

        return redirect()
            ->route('hris.payroll.adjustments.show', $adjustment->id)
            ->with('success', 'Adjustment berhasil dibuat');
    }

    /**
     * Approve adjustment
     */
    public function approve($id)
    {
        $adjustment = PayrollAdjustment::findOrFail($id);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Adjustment sudah diproses');
        }

        DB::beginTransaction();
        try {
            $adjustment->approve(Auth::id());
            DB::commit();

            return back()->with('success', 'Adjustment berhasil disetujui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui: ' . $e->getMessage());
        }
    }

    /**
     * Reject adjustment
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $adjustment = PayrollAdjustment::findOrFail($id);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Adjustment sudah diproses');
        }

        DB::beginTransaction();
        try {
            $adjustment->reject(Auth::id(), $validated['rejection_reason']);
            DB::commit();

            return back()->with('success', 'Adjustment berhasil ditolak');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }
}
