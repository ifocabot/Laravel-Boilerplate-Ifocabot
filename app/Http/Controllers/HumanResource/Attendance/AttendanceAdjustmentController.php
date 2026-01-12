<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceAdjustment;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Services\Attendance\AttendanceSummaryService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceAdjustmentController extends Controller
{
    protected AttendanceSummaryService $attendanceService;

    public function __construct(AttendanceSummaryService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display list of all adjustments
     */
    public function index(Request $request)
    {
        $query = AttendanceAdjustment::with(['employee', 'createdBy'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $adjustments = $query->paginate(20);

        $employees = Employee::orderBy('full_name')->get(['id', 'full_name', 'nik']);

        $adjustmentTypes = [
            AttendanceAdjustment::TYPE_LEAVE => 'Cuti',
            AttendanceAdjustment::TYPE_SICK => 'Sakit',
            AttendanceAdjustment::TYPE_PERMISSION => 'Izin',
            AttendanceAdjustment::TYPE_OVERTIME_ADD => 'Lembur Ditambah',
            AttendanceAdjustment::TYPE_OVERTIME_CANCEL => 'Lembur Dibatalkan',
            AttendanceAdjustment::TYPE_LATE_WAIVE => 'Telat Dihapuskan',
            AttendanceAdjustment::TYPE_MANUAL_OVERRIDE => 'Koreksi Manual',
        ];

        return view('admin.hris.attendance.adjustments.index', compact(
            'adjustments',
            'employees',
            'adjustmentTypes'
        ));
    }

    /**
     * Show form to create manual adjustment
     */
    public function create()
    {
        $employees = Employee::orderBy('full_name')->get(['id', 'full_name', 'nik']);

        $statusOptions = [
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'leave' => 'Cuti',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'wfh' => 'Work From Home',
            'business_trip' => 'Dinas Luar',
            'alpha' => 'Alpha',
        ];

        return view('admin.hris.attendance.adjustments.create', compact(
            'employees',
            'statusOptions'
        ));
    }

    /**
     * Store manual adjustment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'status_override' => 'nullable|string|max:20',
            'adjustment_minutes' => 'nullable|integer',
            'reason' => 'required|string|max:255',
        ]);

        $adjustment = AttendanceAdjustment::createManualOverride(
            $validated['employee_id'],
            $validated['date'],
            $validated['status_override'],
            $validated['adjustment_minutes'] ?? 0,
            $validated['reason'],
            auth()->id()
        );

        // ⭐ Emit AttendanceEvent for audit trail
        \App\Models\AttendanceEvent::recordManualCorrection(
            employeeId: $validated['employee_id'],
            date: Carbon::parse($validated['date']),
            changes: [
                'status' => $validated['status_override'],
                'adjustment_minutes' => $validated['adjustment_minutes'] ?? 0,
            ],
            reason: $validated['reason'],
            correctedBy: auth()->id()
        );

        // ⭐ Dispatch async rebuild job
        dispatch(new \App\Jobs\RecalculateAttendanceJob(
            $validated['employee_id'],
            $validated['date'],
            'manual_adjustment'
        ));

        return redirect()
            ->route('hris.attendance.adjustments.index')
            ->with('success', 'Adjustment berhasil disimpan dan diterapkan.');
    }

    /**
     * Show adjustment details
     */
    public function show(AttendanceAdjustment $adjustment)
    {
        $adjustment->load(['employee', 'source', 'createdBy']);

        // Get related summary
        $summary = AttendanceSummary::where('employee_id', $adjustment->employee_id)
            ->where('date', $adjustment->date)
            ->first();

        return view('admin.hris.attendance.adjustments.show', compact(
            'adjustment',
            'summary'
        ));
    }

    /**
     * Delete adjustment and re-recalculate
     */
    public function destroy(AttendanceAdjustment $adjustment)
    {
        $employeeId = $adjustment->employee_id;
        $date = $adjustment->date;

        $adjustment->delete();

        // Recalculate to remove the adjustment effect
        $this->attendanceService->recalculate($employeeId, $date);

        return redirect()
            ->route('hris.attendance.adjustments.index')
            ->with('success', 'Adjustment berhasil dihapus.');
    }
}
