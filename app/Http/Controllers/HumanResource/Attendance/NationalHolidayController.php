<?php

namespace App\Http\Controllers\HumanResource\Attendance;

use App\Http\Controllers\Controller;
use App\Models\NationalHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NationalHolidayController extends Controller
{
    /**
     * Display a listing of holidays
     */
    public function index(Request $request)
    {
        $query = NationalHoliday::query();

        // Filter by year
        if ($request->filled('year')) {
            $query->forYear($request->year);
        } else {
            $query->forYear(now()->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->whereMonth('date', $request->month);
        }

        // Filter recurring only
        if ($request->has('recurring_only')) {
            $query->recurring();
        }

        // Filter by status
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $holidays = $query->orderBy('date')->paginate(20);

        // Statistics
        $currentYear = $request->input('year', now()->year);
        $totalHolidays = NationalHoliday::forYear($currentYear)->count();
        $recurringHolidays = NationalHoliday::forYear($currentYear)->recurring()->count();
        $upcomingHolidays = NationalHoliday::active()
            ->where('date', '>=', now())
            ->count();

        return view('admin.hris.attendance.holidays.index', compact(
            'holidays',
            'totalHolidays',
            'recurringHolidays',
            'upcomingHolidays'
        ));
    }

    /**
     * Show the form for creating a new holiday
     */
    public function create()
    {
        return view('admin.hris.attendance.holidays.create');
    }

    /**
     * Store a newly created holiday
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'date' => 'required|date',
                'is_recurring' => 'nullable|boolean',
                'description' => 'nullable|string|max:500',
            ]);

            $validated['is_recurring'] = $request->has('is_recurring');
            $validated['is_active'] = true;

            $holiday = NationalHoliday::create($validated);

            DB::commit();

            Log::info('National holiday created', [
                'holiday_id' => $holiday->id,
                'name' => $holiday->name,
                'date' => $holiday->date,
            ]);

            return redirect()
                ->route('hris.attendance.holidays.index')
                ->with('success', 'Hari libur nasional berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Holiday Create Error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the holiday
     */
    public function edit($id)
    {
        $holiday = NationalHoliday::findOrFail($id);

        return view('admin.hris.attendance.holidays.edit', compact('holiday'));
    }

    /**
     * Update the specified holiday
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $holiday = NationalHoliday::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'date' => 'required|date',
                'is_recurring' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'description' => 'nullable|string|max:500',
            ]);

            $validated['is_recurring'] = $request->has('is_recurring');
            $validated['is_active'] = $request->has('is_active');

            $holiday->update($validated);

            DB::commit();

            Log::info('National holiday updated', [
                'holiday_id' => $holiday->id,
            ]);

            return redirect()
                ->route('hris.attendance.holidays.index')
                ->with('success', 'Hari libur nasional berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Holiday Update Error', [
                'error' => $e->getMessage(),
                'holiday_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified holiday
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $holiday = NationalHoliday::findOrFail($id);
            $holidayName = $holiday->name;
            $holiday->delete();

            DB::commit();

            Log::info('National holiday deleted', [
                'holiday_id' => $id,
                'holiday_name' => $holidayName,
            ]);

            return redirect()
                ->route('hris.attendance.holidays.index')
                ->with('success', "Hari libur \"{$holidayName}\" berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Holiday Delete Error', [
                'error' => $e->getMessage(),
                'holiday_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Copy recurring holidays to a new year
     */
    public function copyRecurring(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'target_year' => 'required|integer|min:2020|max:2100',
            ]);

            $copied = NationalHoliday::copyRecurringToYear($validated['target_year']);

            DB::commit();

            Log::info('Recurring holidays copied', [
                'target_year' => $validated['target_year'],
                'count' => $copied,
            ]);

            return redirect()
                ->route('hris.attendance.holidays.index', ['year' => $validated['target_year']])
                ->with('success', "{$copied} hari libur berulang berhasil disalin ke tahun {$validated['target_year']}.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Copy Recurring Holidays Error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
