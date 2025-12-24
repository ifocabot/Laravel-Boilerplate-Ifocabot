<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\Department;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceSummaryController extends Controller
{
    /**
     * Display attendance summaries
     */
    public function index(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $departmentId = $request->input('department_id');
        $status = $request->input('status');
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $query = AttendanceSummary::with(['employee.currentCareer.department', 'shift'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($departmentId) {
            $query->whereHas('employee.currentCareer', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $summaries = $query->orderBy('date', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Get filters data
        $employees = Employee::where('status', 'active')->orderBy('full_name')->get();
        $departments = Department::orderBy('name')->get();

        // Statistics
        $stats = AttendanceSummary::getMonthlyStats($year, $month);

        return view('admin.hris.attendance.summaries.index', compact(
            'summaries',
            'employees',
            'departments',
            'stats',
            'year',
            'month'
        ));
    }

    /**
     * Show employee report
     */
    public function employeeReport(Request $request, $employeeId)
    {
        $employee = Employee::with(['currentCareer.department', 'currentCareer.position'])
            ->findOrFail($employeeId);

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get summaries
        $summaries = AttendanceSummary::forEmployee($employeeId)
            ->forDateRange($startDate, $endDate)
            ->orderBy('date')
            ->get();

        // Get payroll summary
        $payrollSummary = AttendanceSummary::getPayrollSummary($employeeId, $startDate, $endDate);

        // Build calendar data
        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $summary = $summaries->where('date', $currentDate->format('Y-m-d'))->first();

            $calendar[] = [
                'date' => $currentDate->copy(),
                'summary' => $summary,
                'is_weekend' => $currentDate->isWeekend(),
                'is_today' => $currentDate->isToday(),
            ];

            $currentDate->addDay();
        }

        return view('admin.hris.attendance.summaries.employee-report', compact(
            'employee',
            'summaries',
            'payrollSummary',
            'calendar',
            'year',
            'month',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Generate summaries
     */
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            DB::beginTransaction();

            $count = AttendanceSummary::generateForDateRange(
                $validated['start_date'],
                $validated['end_date']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$count} attendance summaries berhasil digenerate",
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Generate Attendance Summaries Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update summary status manually
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:present,late,absent,leave,sick,permission,wfh,business_trip,alpha',
                'notes' => 'nullable|string|max:500',
            ]);

            $summary = AttendanceSummary::findOrFail($id);

            DB::beginTransaction();

            if (in_array($validated['status'], ['leave', 'sick', 'permission', 'wfh', 'business_trip'])) {
                $summary->markAsLeave($validated['status'], $validated['notes'] ?? null);
            } else {
                $summary->status = $validated['status'];
                $summary->notes = $validated['notes'] ?? null;
                $summary->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status attendance berhasil diupdate',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Update Attendance Status Error', [
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
     * Lock summaries for payroll period
     */
    public function lockForPayroll(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            DB::beginTransaction();

            $count = AttendanceSummary::lockForPayrollPeriod(
                $validated['start_date'],
                $validated['end_date'],
                Auth::id()
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$count} attendance summaries locked for payroll processing",
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlock summary for correction
     */
    public function unlockForCorrection(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $summary = AttendanceSummary::findOrFail($id);

            if (!$summary->is_locked_for_payroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'Summary is not locked',
                ], 422);
            }

            DB::beginTransaction();

            $summary->unlockForPayroll(Auth::id(), $validated['reason']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Summary unlocked for correction',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}