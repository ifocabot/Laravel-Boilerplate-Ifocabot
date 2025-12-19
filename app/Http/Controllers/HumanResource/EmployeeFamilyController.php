<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\EmployeeFamily;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeFamilyController extends Controller
{
    public function index($employeeId)
    {
        $employee = Employee::with([
            'families' => function ($query) {
                $query->orderBy('relation')->orderBy('name');
            }
        ])->findOrFail($employeeId);

        return view('admin.hris.employees.families.index', compact('employee'));
    }

    public function store(Request $request, $employeeId)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($employeeId);

            $validated = $request->validate([
                'name' => 'required|string|max:150',
                'relation' => 'required|in:spouse,child,parent,sibling',
                'phone' => 'nullable|string|max:20',
                'is_emergency_contact' => 'nullable|boolean',
                'is_bpjs_dependent' => 'nullable|boolean',
            ], [
                'name.required' => 'Nama wajib diisi.',
                'relation.required' => 'Hubungan keluarga wajib dipilih.',
                'relation.in' => 'Hubungan keluarga tidak valid.',
            ]);

            $validated['employee_id'] = $employeeId;
            $validated['is_emergency_contact'] = $request->has('is_emergency_contact');
            $validated['is_bpjs_dependent'] = $request->has('is_bpjs_dependent');

            $family = EmployeeFamily::create($validated);

            DB::commit();

            Log::info('Employee family created', [
                'family_id' => $family->id,
                'employee_id' => $employeeId,
                'name' => $family->name,
                'relation' => $family->relation
            ]);

            return redirect()
                ->route('hris.employees.families.index', $employeeId)
                ->with('success', 'Data keluarga "' . $family->name . '" berhasil ditambahkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Family Store Error', [
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
            $family = EmployeeFamily::where('employee_id', $employeeId)->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:150',
                'relation' => 'required|in:spouse,child,parent,sibling',
                'phone' => 'nullable|string|max:20',
                'is_emergency_contact' => 'nullable|boolean',
                'is_bpjs_dependent' => 'nullable|boolean',
            ], [
                'name.required' => 'Nama wajib diisi.',
                'relation.required' => 'Hubungan keluarga wajib dipilih.',
                'relation.in' => 'Hubungan keluarga tidak valid.',
            ]);

            $validated['is_emergency_contact'] = $request->has('is_emergency_contact');
            $validated['is_bpjs_dependent'] = $request->has('is_bpjs_dependent');

            $family->update($validated);

            DB::commit();

            Log::info('Employee family updated', [
                'family_id' => $family->id,
                'employee_id' => $employeeId,
                'name' => $family->name
            ]);

            return redirect()
                ->route('hris.employees.families.index', $employeeId)
                ->with('success', 'Data keluarga "' . $family->name . '" berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Family Update Error', [
                'error' => $e->getMessage(),
                'family_id' => $id,
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
            $family = EmployeeFamily::where('employee_id', $employeeId)->findOrFail($id);

            $name = $family->name;
            $family->delete();

            DB::commit();

            Log::info('Employee family deleted', [
                'family_id' => $id,
                'employee_id' => $employeeId,
                'name' => $name
            ]);

            return redirect()
                ->back()
                ->with('success', 'Data keluarga "' . $name . '" berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Family Delete Error', [
                'error' => $e->getMessage(),
                'family_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}