<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AttendanceLogController extends Controller
{
    /**
     * Today's Attendance Dashboard
     * Real-time monitoring untuk hari ini
     */
    /**
     * Today's Attendance Dashboard
     * Real-time monitoring untuk hari ini
     */

    public function myAttendance(Request $request)
    {
        // Get current authenticated user's employee record
        $user = auth()->user();

        // Cek apakah relasi 'employee' ada isinya
        if (!$user->employee) {
            abort(403, 'Anda tidak terdaftar sebagai karyawan');
        }

        $employee = $user->employee;
        $employee->load(['currentCareer.department', 'currentCareer.position']);

        $today = now()->format('Y-m-d');

        // Get today's schedule
        $schedule = EmployeeSchedule::where('employee_id', $employee->id)
            ->where('date', $today)
            ->with('shift')
            ->first();

        // Get today's attendance log
        $todayLog = AttendanceLog::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        // Get recent attendance logs (last 7 days)
        $recentLogs = AttendanceLog::where('employee_id', $employee->id)
            ->where('date', '>=', now()->subDays(7)->format('Y-m-d'))
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.hris.attendance.logs.my-attendance', compact(
            'employee',
            'schedule',
            'todayLog',
            'recentLogs'
        ));
    }

    public function today(Request $request)
    {
        $today = now()->format('Y-m-d');

        // Get all active employees
        $employees = Employee::where('status', 'active')
            ->with([
                'currentCareer.department',
                'currentCareer.position'
            ])
            ->get();

        // Get today's attendance logs
        $attendanceLogs = AttendanceLog::where('date', $today)
            ->with(['employee', 'shift', 'location'])
            ->orderBy('clock_in_time', 'desc')
            ->get();

        // Get today's schedules
        $schedules = EmployeeSchedule::where('date', $today)
            ->with(['employee', 'shift'])
            ->get()
            ->keyBy('employee_id');

        // Statistics
        $stats = [
            'total_employees' => $employees->count(),
            'clocked_in' => $attendanceLogs->where('has_clocked_in', true)->count(),
            'clocked_out' => $attendanceLogs->where('has_clocked_out', true)->count(),
            'late' => $attendanceLogs->where('is_late', true)->count(),
            'not_clocked_in' => 0, // Will be calculated below
        ];

        // Combine data for scheduled employees
        $attendanceData = collect();

        foreach ($schedules as $schedule) {
            $employee = $schedule->employee;
            $log = $attendanceLogs->where('employee_id', $employee->id)->first();

            // Determine status
            $status = $this->getEmployeeStatus($employee, $log, $schedule);

            $attendanceData->push([
                'employee' => $employee,
                'shift' => $schedule->shift,  // Pass shift directly from schedule
                'attendance' => $log,  // Rename from 'log' to 'attendance'
                'is_late' => $log?->is_late ?? false,  // Add is_late directly
                'status' => $status,
            ]);
        }

        $totalScheduled = $schedules->count();
        $present = $attendanceLogs->where('has_clocked_in', true)->count();
        $notPresent = $attendanceLogs->where('has_clocked_in', false)->count();
        $working = $attendanceLogs->where('has_clocked_in', true)->where('has_clocked_out', true)->count();
        $late = $attendanceLogs->where('is_late', true)->count();
        $early_out = $attendanceLogs->where('is_early_out', true)->count();
        $totalAbsent = $employees->count() - $present;
        $completed = $attendanceLogs->where('has_clocked_in', true)->where('has_clocked_out', true)->count();
        $incomplete = $attendanceLogs->where('has_clocked_in', true)->where('has_clocked_out', false)->count();


        // Calculate not clocked in
        $stats['not_clocked_in'] = $attendanceData->where('status.type', 'not_clocked_in')->count();

        return view('admin.hris.attendance.logs.today', compact('attendanceData', 'stats', 'totalScheduled', 'present', 'notPresent', 'late', 'early_out', 'totalAbsent', 'working', 'completed', 'incomplete'));
    }

    /**
     * Get employee attendance status
     */
    private function getEmployeeStatus($employee, $log, $schedule)
    {
        // No schedule today
        if (!$schedule) {
            return [
                'type' => 'no_schedule',
                'label' => 'Tidak Ada Jadwal',
                'color' => 'gray',
            ];
        }

        // Has schedule but not clocked in yet
        if (!$log || !$log->has_clocked_in) {
            return [
                'type' => 'not_clocked_in',
                'label' => 'Belum Clock In',
                'color' => 'red',
            ];
        }

        // Already clocked out (completed)
        if ($log->has_clocked_out) {
            return [
                'type' => 'completed',
                'label' => 'Selesai',
                'color' => 'green',
            ];
        }

        // Clocked in but late
        if ($log->is_late) {
            return [
                'type' => 'late',
                'label' => 'Terlambat',
                'color' => 'orange',
            ];
        }

        // Currently working (clocked in, on time, not clocked out yet)
        if ($log->has_clocked_in && !$log->has_clocked_out) {
            return [
                'type' => 'working',
                'label' => 'Sedang Bekerja',
                'color' => 'blue',
            ];
        }

        // Default
        return [
            'type' => 'unknown',
            'label' => '-',
            'color' => 'gray',
        ];
    }

    /**
     * Display all attendance logs
     */
    public function index(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $date = $request->input('date');
        $status = $request->input('status');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = AttendanceLog::with(['employee.currentCareer.department', 'shift', 'location']);

        // Filters
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($date) {
            $query->where('date', $date);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if ($status === 'late') {
            $query->where('is_late', true);
        } elseif ($status === 'early_out') {
            $query->where('is_early_out', true);
        } elseif ($status === 'incomplete') {
            $query->where(function ($q) {
                $q->whereNull('clock_out_time')
                    ->orWhereNull('clock_in_time');
            });
        }

        $logs = $query->orderBy('date', 'desc')
            ->orderBy('clock_in_time', 'desc')
            ->paginate(20)
            ->appends($request->all());

        // Get employees for filter
        $employees = Employee::where('status', 'active')->orderBy('full_name')->get();

        // Statistics
        $stats = [
            'total_logs' => AttendanceLog::count(),
            'today_logs' => AttendanceLog::where('date', now()->format('Y-m-d'))->count(),
            'late_today' => AttendanceLog::where('date', now()->format('Y-m-d'))
                ->where('is_late', true)->count(),
        ];

        return view('admin.hris.attendance.logs.index', compact('logs', 'employees', 'stats'));
    }

    /**
     * Show attendance log detail
     */
    public function show($id)
    {
        $log = AttendanceLog::with([
            'employee.currentCareer.department',
            'employee.currentCareer.position',
            'shift',
            'location'
        ])->findOrFail($id);

        return view('admin.hris.attendance.logs.show', compact('log'));
    }

    /**
     * Clock In
     * Process employee clock in with GPS and photo
     */
    public function clockIn(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'photo' => 'required|image|max:5120', // Max 5MB
                'notes' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $employee = Employee::findOrFail($validated['employee_id']);
            $today = now()->format('Y-m-d');
            $currentTime = now();

            // Check if already clocked in today
            $existingLog = AttendanceLog::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if ($existingLog && $existingLog->has_clocked_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan clock in hari ini',
                ], 422);
            }

            // Get today's schedule
            $schedule = EmployeeSchedule::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada jadwal untuk hari ini',
                ], 422);
            }

            // Validate geofence (optional)
            if ($schedule->location_id) {
                $location = Location::find($schedule->location_id);

                if ($location && $location->geofence_radius > 0) {
                    $distance = $this->calculateDistance(
                        $validated['latitude'],
                        $validated['longitude'],
                        $location->latitude,
                        $location->longitude
                    );

                    if ($distance > $location->geofence_radius) {
                        return response()->json([
                            'success' => false,
                            'message' => "Anda berada di luar area kerja. Jarak: " . round($distance) . " meter dari lokasi.",
                            'distance' => round($distance),
                            'max_distance' => $location->geofence_radius,
                        ], 422);
                    }
                }
            }

            // Upload photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = 'clock_in_' . $employee->id . '_' . time() . '.' . $photo->extension();
                $photoPath = $photo->storeAs('attendance/clock-in', $filename, 'public');
            }

            // Create or update attendance log
            if ($existingLog) {
                $existingLog->update([
                    'shift_id' => $schedule->shift_id,
                    'location_id' => $schedule->location_id,
                    'clock_in_time' => $currentTime->format('H:i:s'),
                    'clock_in_latitude' => $validated['latitude'],
                    'clock_in_longitude' => $validated['longitude'],
                    'clock_in_photo' => $photoPath,
                    'clock_in_notes' => $validated['notes'] ?? null,
                ]);
                $log = $existingLog;
            } else {
                $log = AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'date' => $today,
                    'shift_id' => $schedule->shift_id,
                    'location_id' => $schedule->location_id,
                    'clock_in_time' => $currentTime->format('H:i:s'),
                    'clock_in_latitude' => $validated['latitude'],
                    'clock_in_longitude' => $validated['longitude'],
                    'clock_in_photo' => $photoPath,
                    'clock_in_notes' => $validated['notes'] ?? null,
                ]);
            }

            DB::commit();

            Log::info('Clock In Success', [
                'employee_id' => $employee->id,
                'date' => $today,
                'time' => $currentTime->format('H:i:s'),
                'is_late' => $log->is_late,
            ]);

            return response()->json([
                'success' => true,
                'message' => $log->is_late
                    ? 'Clock in berhasil. Anda terlambat ' . $log->late_duration_minutes . ' menit.'
                    : 'Clock in berhasil!',
                'data' => [
                    'id' => $log->id,
                    'clock_in_time' => $log->formatted_clock_in_time,
                    'is_late' => $log->is_late,
                    'late_minutes' => $log->late_duration_minutes,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Clock In Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clock Out
     * Process employee clock out with GPS and photo
     */
    public function clockOut(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'photo' => 'required|image|max:5120',
                'notes' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            $employee = Employee::findOrFail($validated['employee_id']);
            $today = now()->format('Y-m-d');
            $currentTime = now();

            // Get today's attendance log
            $log = AttendanceLog::where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            if (!$log) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum melakukan clock in hari ini',
                ], 422);
            }

            if ($log->has_clocked_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan clock out hari ini',
                ], 422);
            }

            // Upload photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = 'clock_out_' . $employee->id . '_' . time() . '.' . $photo->extension();
                $photoPath = $photo->storeAs('attendance/clock-out', $filename, 'public');
            }

            // Update attendance log
            $log->update([
                'clock_out_time' => $currentTime->format('H:i:s'),
                'clock_out_latitude' => $validated['latitude'],
                'clock_out_longitude' => $validated['longitude'],
                'clock_out_photo' => $photoPath,
                'clock_out_notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            Log::info('Clock Out Success', [
                'employee_id' => $employee->id,
                'date' => $today,
                'time' => $currentTime->format('H:i:s'),
                'work_duration' => $log->work_duration_minutes,
            ]);

            // ❌ REMOVE THIS LINE:
            // \App\Models\AttendanceSummary::generateFromLog($log);

            // ✅ Summary will be generated by CRON job at midnight

            return response()->json([
                'success' => true,
                'message' => 'Clock out berhasil!',
                'data' => [
                    'id' => $log->id,
                    'clock_out_time' => $log->formatted_clock_out_time,
                    'work_duration' => $log->formatted_work_duration,
                    'is_early_out' => $log->is_early_out,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Clock Out Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete attendance log
     */
    public function destroy($id)
    {
        try {
            $log = AttendanceLog::findOrFail($id);

            DB::beginTransaction();

            // Delete photos
            if ($log->clock_in_photo) {
                Storage::disk('public')->delete($log->clock_in_photo);
            }
            if ($log->clock_out_photo) {
                Storage::disk('public')->delete($log->clock_out_photo);
            }

            $log->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance log berhasil dihapus',
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
     * Get employee attendance summary
     */
    public function summary(Request $request, $employeeId)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $logs = AttendanceLog::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('shift')
            ->orderBy('date')
            ->get();

        $summary = [
            'total_days' => $logs->count(),
            'present_days' => $logs->where('has_clocked_in', true)->count(),
            'late_days' => $logs->where('is_late', true)->count(),
            'early_out_days' => $logs->where('is_early_out', true)->count(),
            'total_work_hours' => round($logs->sum('work_duration_minutes') / 60, 2),
            'total_late_minutes' => $logs->sum('late_duration_minutes'),
            'total_overtime_minutes' => $logs->sum('overtime_minutes'),
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
            'logs' => $logs,
        ]);
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in meters
    }

    /**
     * Employee's own attendance page (Clock In/Out Interface)
     */

}