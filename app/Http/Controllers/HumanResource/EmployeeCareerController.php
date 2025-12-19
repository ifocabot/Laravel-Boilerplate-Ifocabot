<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCareer;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeCareerController extends Controller
{
    public function index($employeeId)
    {
        $employee = Employee::with([
            'careers' => function ($query) {
                $query->orderBy('start_date', 'desc')
                    ->with(['department', 'position', 'level', 'branch', 'manager']);
            }
        ])->findOrFail($employeeId);

        // Master data untuk dropdowns
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();
        $branches = Location::where('is_active', true)->orderBy('name')->get();
        $managers = Employee::where('status', 'active')
            ->where('id', '!=', $employeeId)
            ->orderBy('full_name')
            ->get();

        // Statistics
        $totalCareers = $employee->careers->count();
        $activeCareers = $employee->careers->where('is_active', true)->count();
        $totalPromotions = $employee->careers->where('is_active', false)->count();

        return view('admin.hris.employees.careers.index', compact(
            'employee',
            'departments',
            'positions',
            'levels',
            'branches',
            'managers',
            'totalCareers',
            'activeCareers',
            'totalPromotions',
        ));
    }

    public function store(Request $request, $employeeId)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($employeeId);

            $validated = $request->validate([
                'department_id' => 'required|exists:departments,id',
                'position_id' => 'required|exists:positions,id',
                'level_id' => 'required|exists:levels,id',
                'branch_id' => 'nullable|exists:locations,id',
                'manager_id' => 'nullable|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'nullable',
                'notes' => 'nullable|string',
            ], [
                'department_id.required' => 'Departemen wajib dipilih.',
                'position_id.required' => 'Posisi wajib dipilih.',
                'level_id.required' => 'Level wajib dipilih.',
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
                'manager_id.exists' => 'Manager tidak valid.',
            ]);

            $validated['employee_id'] = $employeeId;
            $validated['is_active'] = true; // Always true for new career

            $career = EmployeeCareer::create($validated);

            DB::commit();

            Log::info('Employee career created', [
                'career_id' => $career->id,
                'employee_id' => $employeeId,
            ]);

            return redirect()
                ->route('hris.employees.careers.index', $employeeId)
                ->with('success', 'Riwayat karir berhasil ditambahkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Career Store Error', [
                'error' => $e->getMessage(),
                'employee_id' => $employeeId,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $career = EmployeeCareer::where('employee_id', $employeeId)->findOrFail($id);

            $validated = $request->validate([
                'department_id' => 'required|exists:departments,id',
                'position_id' => 'required|exists:positions,id',
                'level_id' => 'required|exists:levels,id',
                'branch_id' => 'nullable|exists:locations,id',
                'manager_id' => 'nullable|exists:employees,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ], [
                'department_id.required' => 'Departemen wajib dipilih.',
                'position_id.required' => 'Posisi wajib dipilih.',
                'level_id.required' => 'Level wajib dipilih.',
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
            ]);

            $validated['is_active'] = $request->has('is_active') ? true : false;

            // If setting to active, deactivate others
            if ($validated['is_active'] && !$career->is_active) {
                EmployeeCareer::where('employee_id', $employeeId)
                    ->where('id', '!=', $id)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'end_date' => $validated['start_date'],
                    ]);
            }

            $career->update($validated);

            DB::commit();

            Log::info('Employee career updated', [
                'career_id' => $career->id,
                'employee_id' => $employeeId
            ]);

            return redirect()
                ->route('hris.employees.careers.index', $employeeId)
                ->with('success', 'Riwayat karir berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Career Update Error', [
                'error' => $e->getMessage(),
                'career_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $career = EmployeeCareer::where('employee_id', $employeeId)->findOrFail($id);

            // Prevent deleting active career
            if ($career->is_active) {
                return redirect()
                    ->back()
                    ->with('error', 'Riwayat karir yang masih aktif tidak dapat dihapus.');
            }

            $career->delete();

            DB::commit();

            Log::info('Employee career deleted', [
                'career_id' => $id,
                'employee_id' => $employeeId
            ]);

            return redirect()
                ->back()
                ->with('success', 'Riwayat karir berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Career Delete Error', [
                'error' => $e->getMessage(),
                'career_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate career
     */
    public function deactivate(Request $request, $employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $career = EmployeeCareer::where('employee_id', $employeeId)->findOrFail($id);

            if (!$career->is_active) {
                return redirect()
                    ->back()
                    ->with('error', 'Riwayat karir sudah tidak aktif.');
            }

            $validated = $request->validate([
                'end_date' => 'required|date|after_or_equal:' . $career->start_date->format('Y-m-d'),
                'notes' => 'nullable|string',
            ], [
                'end_date.required' => 'Tanggal akhir wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
            ]);

            $career->update([
                'is_active' => false,
                'end_date' => $validated['end_date'],
                'notes' => $validated['notes'] ?? $career->notes,
            ]);

            DB::commit();

            Log::info('Employee career deactivated', [
                'career_id' => $career->id,
                'employee_id' => $employeeId,
                'end_date' => $validated['end_date']
            ]);

            return redirect()
                ->back()
                ->with('success', 'Riwayat karir berhasil dinonaktifkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Career Deactivate Error', [
                'error' => $e->getMessage(),
                'career_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}