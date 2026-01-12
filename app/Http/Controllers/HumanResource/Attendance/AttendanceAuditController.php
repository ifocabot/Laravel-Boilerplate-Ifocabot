<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceEvent;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Services\Attendance\AttendanceRebuildService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Attendance Audit Controller
 * 
 * For compliance team to view audit trail and forensic data.
 * Access restricted to HR Manager or higher.
 */
class AttendanceAuditController extends Controller
{

    /**
     * Full event timeline for employee on specific date
     */
    public function timeline(Request $request, $employeeId, $date)
    {
        $employee = Employee::with(['currentCareer.department', 'currentCareer.position'])
            ->findOrFail($employeeId);
        $date = Carbon::parse($date);

        $events = AttendanceEvent::getTimeline($employee->id, $date);
        $summary = AttendanceSummary::forEmployee($employee->id)
            ->forDate($date)
            ->first();

        return view('admin.hris.attendance.audit.timeline', compact(
            'employee',
            'date',
            'events',
            'summary'
        ));
    }

    /**
     * Timeline for date range (audit report)
     */
    public function periodTimeline(Request $request, $employeeId)
    {
        $employee = Employee::with(['currentCareer.department', 'currentCareer.position'])
            ->findOrFail($employeeId);

        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()->format('Y-m-d')));
        $endDate = Carbon::parse($request->input('end_date', now()->format('Y-m-d')));

        $eventsByDate = AttendanceEvent::getTimelineForPeriod($employee->id, $startDate, $endDate);

        $summaries = AttendanceSummary::forEmployee($employee->id)
            ->forDateRange($startDate, $endDate)
            ->orderBy('date')
            ->get()
            ->keyBy(fn($s) => $s->date->format('Y-m-d'));

        return view('admin.hris.attendance.audit.period', compact(
            'employee',
            'startDate',
            'endDate',
            'eventsByDate',
            'summaries'
        ));
    }

    /**
     * Changes/modifications for a specific date
     */
    public function changes(Request $request, $employeeId, $date)
    {
        $employee = Employee::findOrFail($employeeId);
        $date = Carbon::parse($date);

        // Get only correction/override events
        $changes = AttendanceEvent::forEmployee($employee->id)
            ->forDate($date)
            ->whereIn('event_type', [
                \App\Enums\AttendanceEventType::CLOCK_IN_CORRECTED,
                \App\Enums\AttendanceEventType::CLOCK_OUT_CORRECTED,
                \App\Enums\AttendanceEventType::LATE_WAIVED,
                \App\Enums\AttendanceEventType::EARLY_LEAVE_WAIVED,
                \App\Enums\AttendanceEventType::STATUS_OVERRIDE,
                \App\Enums\AttendanceEventType::MANUAL_CORRECTION,
            ])
            ->chronological()
            ->with('createdBy')
            ->get();

        return response()->json([
            'employee_id' => $employee->id,
            'date' => $date->format('Y-m-d'),
            'changes_count' => $changes->count(),
            'changes' => $changes->map(fn($e) => [
                'type' => $e->event_type->value,
                'label' => $e->type_label,
                'description' => $e->description,
                'payload' => $e->payload,
                'created_by' => $e->createdBy?->name,
                'created_at' => $e->created_at->format('Y-m-d H:i:s'),
            ]),
        ]);
    }

    /**
     * Verification/discrepancy report
     */
    public function discrepancies(Request $request, AttendanceRebuildService $rebuildService)
    {
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()->format('Y-m-d')));
        $endDate = Carbon::parse($request->input('end_date', now()->format('Y-m-d')));
        $employeeId = $request->input('employee_id');

        if ($employeeId) {
            $result = $rebuildService->verify((int) $employeeId, $startDate, $endDate);
            $results = [$result];
        } else {
            $results = [];
            $employees = Employee::where('status', 'active')->take(100)->get(); // Limit for performance

            foreach ($employees as $employee) {
                $result = $rebuildService->verify($employee->id, $startDate, $endDate);
                if ($result['discrepancy_count'] > 0) {
                    $result['employee_name'] = $employee->full_name;
                    $results[] = $result;
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],
                'total_discrepant_employees' => count($results),
                'results' => $results,
            ]);
        }

        return view('admin.hris.attendance.audit.discrepancies', compact(
            'startDate',
            'endDate',
            'results'
        ));
    }

    /**
     * Rebuild attendance from events (admin action)
     */
    public function rebuild(Request $request, AttendanceRebuildService $rebuildService)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));
        $date = Carbon::parse($request->input('date'));

        // Check if locked
        $summary = AttendanceSummary::forEmployee($employee->id)
            ->forDate($date)
            ->first();

        if ($summary && !$summary->canEdit()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot rebuild locked attendance. Current status: ' . $summary->lifecycle_status->label(),
                ], 422);
            }
            return redirect()->back()->with('error', 'Cannot rebuild locked attendance.');
        }

        $rebuilt = $rebuildService->rebuildDay($employee->id, $date);

        // Record the rebuild action as an event
        AttendanceEvent::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'event_type' => \App\Enums\AttendanceEventType::SUMMARY_CALCULATED,
            'payload' => [
                'reason' => 'Manual rebuild by admin',
                'triggered_by' => 'audit_controller',
            ],
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance rebuilt successfully',
                'summary' => [
                    'status' => $rebuilt->status,
                    'total_work_minutes' => $rebuilt->total_work_minutes,
                    'late_minutes' => $rebuilt->late_minutes,
                    'approved_overtime_minutes' => $rebuilt->approved_overtime_minutes,
                ],
            ]);
        }

        return redirect()->back()->with(
            'success',
            "Attendance rebuilt! Status: {$rebuilt->status}, Work: {$rebuilt->total_work_minutes}min"
        );
    }
}

