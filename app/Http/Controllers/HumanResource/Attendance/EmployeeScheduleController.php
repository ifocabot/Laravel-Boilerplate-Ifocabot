<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSchedule;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmployeeScheduleController extends Controller
{
    /**
     * Display schedules (calendar view)
     */
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        // Get filter parameters
        $employeeId = $request->input('employee_id');
        $departmentId = $request->input('department_id');
        $shiftId = $request->input('shift_id');

        // Build employee query
        $employeesQuery = Employee::where('status', 'active')
            ->with(['currentCareer.department', 'currentCareer.position']);

        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        }

        if ($departmentId) {
            $employeesQuery->whereHas('currentCareer', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $employees = $employeesQuery->orderBy('full_name')->limit(50)->get();

        // Get schedules for the month
        $schedules = EmployeeSchedule::with(['shift', 'employee'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->when($employeeId, fn($q) => $q->where('employee_id', $employeeId))
            ->when($shiftId, fn($q) => $q->where('shift_id', $shiftId))
            ->get()
            ->groupBy('employee_id');

        // Get all dates in month - CONVERT TO COLLECTION
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $dates = collect(); // <-- CHANGED: Use collection
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dates->push($currentDate->copy()); // <-- CHANGED: Use push
            $currentDate->addDay();
        }

        // Get filters data
        $allEmployees = Employee::where('status', 'active')->orderBy('full_name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        $shifts = Shift::active()->orderBy('code')->get();

        // Statistics
        $totalSchedules = EmployeeSchedule::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->count();

        $workingDays = EmployeeSchedule::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->workingDays()
            ->count();

        $dayOffs = EmployeeSchedule::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->dayOffs()
            ->count();

        $holidays = EmployeeSchedule::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->holidays()
            ->count();

        return view('admin.hris.attendance.schedules.index', compact(
            'employees',
            'schedules',
            'dates',
            'year',
            'month',
            'allEmployees',
            'departments',
            'shifts',
            'totalSchedules',
            'workingDays',
            'dayOffs',
            'holidays'
        ));
    }

    /**
     * Store/update schedule
     */
    /**
     * Store/update schedule
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date',
                'shift_id' => 'nullable|exists:shifts,id',
                'is_day_off' => 'nullable|boolean',
                'is_holiday' => 'nullable|boolean',
                'notes' => 'nullable|string|max:255',
            ]);

            // IMPORTANT: Handle boolean properly
            $validated['is_day_off'] = $request->input('is_day_off', false) === true || $request->input('is_day_off') === 'true' || $request->input('is_day_off') === 1;
            $validated['is_holiday'] = $request->input('is_holiday', false) === true || $request->input('is_holiday') === 'true' || $request->input('is_holiday') === 1;

            // Log untuk debug
            Log::info('Schedule Store Request', [
                'raw_request' => $request->all(),
                'validated' => $validated,
                'is_day_off_raw' => $request->input('is_day_off'),
                'is_holiday_raw' => $request->input('is_holiday'),
            ]);

            // Validation: Must have shift OR marked as day off/holiday
            if (!$validated['shift_id'] && !$validated['is_day_off'] && !$validated['is_holiday']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pilih shift atau tandai sebagai hari libur',
                ], 422);
            }

            // If day_off or holiday, remove shift_id
            if ($validated['is_day_off'] || $validated['is_holiday']) {
                $validated['shift_id'] = null;
            }

            // Check if schedule already exists
            $existingSchedule = EmployeeSchedule::where('employee_id', $validated['employee_id'])
                ->where('date', $validated['date'])
                ->first();

            if ($existingSchedule) {
                // UPDATE existing schedule
                $existingSchedule->update($validated);
                $schedule = $existingSchedule;
                $message = 'Jadwal berhasil diperbarui';
            } else {
                // CREATE new schedule
                $schedule = EmployeeSchedule::create($validated);
                $message = 'Jadwal berhasil ditambahkan';
            }

            DB::commit();

            Log::info('Schedule saved', [
                'schedule_id' => $schedule->id,
                'employee_id' => $schedule->employee_id,
                'date' => $schedule->date,
                'shift_id' => $schedule->shift_id,
                'is_day_off' => $schedule->is_day_off,
                'is_holiday' => $schedule->is_holiday,
                'action' => $existingSchedule ? 'updated' : 'created',
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'schedule' => $schedule->load('shift', 'employee'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Schedule Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate bulk schedules
     */
    public function generateBulk(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'employee_ids' => 'required|array',
                'employee_ids.*' => 'exists:employees,id',
                'year' => 'required|integer|min:2020|max:2100',
                'month' => 'required|integer|min:1|max:12',
                'shift_id' => 'required|exists:shifts,id',
            ]);

            $totalGenerated = 0;
            foreach ($validated['employee_ids'] as $employeeId) {
                $generated = EmployeeSchedule::generateMonthSchedules(
                    $employeeId,
                    $validated['year'],
                    $validated['month'],
                    $validated['shift_id'],
                    [] // Options no longer needed, working days determined by shift
                );
                $totalGenerated += $generated;
            }

            DB::commit();

            Log::info('Bulk schedules generated', [
                'employees_count' => count($validated['employee_ids']),
                'schedules_created' => $totalGenerated,
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$totalGenerated} jadwal berhasil dibuat untuk " . count($validated['employee_ids']) . " karyawan",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk Generate Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete schedule
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $schedule = EmployeeSchedule::findOrFail($id);
            $schedule->delete();

            DB::commit();

            Log::info('Schedule deleted', [
                'schedule_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dihapus',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Schedule Delete Error', [
                'error' => $e->getMessage(),
                'schedule_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Swap shifts between employees
     */
    public function swapShifts(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'employee_id_1' => 'required|exists:employees,id',
                'employee_id_2' => 'required|exists:employees,id|different:employee_id_1',
                'date' => 'required|date',
                'notes' => 'nullable|string|max:255',
            ]);

            $success = EmployeeSchedule::swapShifts(
                $validated['employee_id_1'],
                $validated['employee_id_2'],
                $validated['date'],
                $validated['notes']
            );

            if (!$success) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menukar shift. Pastikan kedua karyawan memiliki jadwal pada tanggal tersebut.',
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Shift berhasil ditukar',
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
     * Mark date as holiday
     */
    public function markHoliday(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'date' => 'required|date',
                'notes' => 'nullable|string|max:255',
            ]);

            $affected = EmployeeSchedule::markAsHoliday(
                $validated['date'],
                $validated['notes']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$affected} jadwal berhasil ditandai sebagai hari libur",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getSchedule($id)
    {
        try {
            $schedule = EmployeeSchedule::with(['shift', 'employee'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'schedule' => $schedule,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Schedule not found',
            ], 404);
        }
    }
}