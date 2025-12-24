<?php

namespace App\Http\Controllers\ESS;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\AttendanceSummary;
use App\Models\EmployeeLeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ESSController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('dashboard')->with('error', 'Akun Anda tidak terhubung dengan data karyawan.');
        }

        // Announcements - pinned first, then by date
        $announcements = Announcement::active()
            ->published()
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        // Today's attendance
        $todayAttendance = AttendanceSummary::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->first();

        // This month attendance stats
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $attendanceStats = AttendanceSummary::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->selectRaw('
                COUNT(*) as total_days,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_days
            ')
            ->first();

        // Leave balances
        $leaveBalances = EmployeeLeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->get();

        // Recent leave requests
        $recentLeaveRequests = LeaveRequest::with('leaveType')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Pending approvals count (if user is an approver)
        $pendingApprovalsCount = 0;

        return view('admin.ess.dashboard', compact(
            'employee',
            'announcements',
            'todayAttendance',
            'attendanceStats',
            'leaveBalances',
            'recentLeaveRequests',
            'pendingApprovalsCount'
        ));
    }

    public function showAnnouncement(Announcement $announcement)
    {
        // Make sure the announcement is active and published
        if (!$announcement->is_active) {
            return redirect()->route('ess.dashboard')->with('error', 'Pengumuman tidak ditemukan.');
        }

        return view('admin.ess.announcements.show', compact('announcement'));
    }
}
