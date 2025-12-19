<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    /**
     * Display listing of shifts
     */
    public function index(Request $request)
    {
        $query = Shift::query();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $shifts = $query->orderBy('code')->paginate(15);

        // Statistics
        $totalShifts = Shift::count();
        $activeShifts = Shift::where('is_active', true)->count();
        $fixedShifts = Shift::where('type', 'fixed')->count();
        $flexibleShifts = Shift::where('type', 'flexible')->count();

        return view('admin.hris.attendance.shifts.index', compact(
            'shifts',
            'totalShifts',
            'activeShifts',
            'fixedShifts',
            'flexibleShifts'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $suggestedCode = Shift::generateCode();

        return view('admin.hris.attendance.shifts.create', compact('suggestedCode'));
    }

    /**
     * Store new shift
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:50',
                'code' => 'required|string|max:10|unique:shifts,code',
                'type' => 'required|in:fixed,flexible',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'break_start' => 'nullable|date_format:H:i',
                'break_end' => 'nullable|date_format:H:i|after_or_equal:break_start',
                'work_hours_required' => 'required|integer|min:0|max:1440',
                'late_tolerance_minutes' => 'required|integer|min:0|max:120',
                'is_overnight' => 'nullable|boolean',
                'description' => 'nullable|string',
            ]);

            $validated['is_overnight'] = $request->has('is_overnight');
            $validated['is_active'] = true;

            $shift = Shift::create($validated);

            DB::commit();

            Log::info('Shift created', [
                'shift_id' => $shift->id,
                'code' => $shift->code,
            ]);

            return redirect()
                ->route('hris.attendance.shifts.index')
                ->with('success', 'Shift berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Shift Create Error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $shift = Shift::findOrFail($id);

        return view('admin.hris.attendance.shifts.edit', compact('shift'));
    }

    /**
     * Update shift
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $shift = Shift::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:50',
                'code' => 'required|string|max:10|unique:shifts,code,' . $id,
                'type' => 'required|in:fixed,flexible',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i',
                'break_start' => 'nullable|date_format:H:i',
                'break_end' => 'nullable|date_format:H:i|after_or_equal:break_start',
                'work_hours_required' => 'required|integer|min:0|max:1440',
                'late_tolerance_minutes' => 'required|integer|min:0|max:120',
                'is_overnight' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'description' => 'nullable|string',
            ]);

            $validated['is_overnight'] = $request->has('is_overnight');
            $validated['is_active'] = $request->has('is_active');

            $shift->update($validated);

            DB::commit();

            Log::info('Shift updated', [
                'shift_id' => $shift->id,
            ]);

            return redirect()
                ->route('hris.attendance.shifts.index')
                ->with('success', 'Shift berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Shift Update Error', [
                'error' => $e->getMessage(),
                'shift_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete shift
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $shift = Shift::findOrFail($id);

            // Check if shift is used
            // TODO: Check attendance records

            $shiftName = $shift->name;
            $shift->delete();

            DB::commit();

            Log::info('Shift deleted', [
                'shift_id' => $id,
                'shift_name' => $shiftName,
            ]);

            return redirect()
                ->route('hris.attendance.shifts.index')
                ->with('success', "Shift \"{$shiftName}\" berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Shift Delete Error', [
                'error' => $e->getMessage(),
                'shift_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}