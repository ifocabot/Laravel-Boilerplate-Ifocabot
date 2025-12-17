<?php

namespace App\Http\Controllers\MasterData\General;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with(['parent', 'children', 'manager'])
            ->orderBy('name')
            ->paginate(50);

        $users = User::orderBy('name')->get();

        // Calculate statistics
        $parentDepartments = Department::whereNull('parent_id')->count();
        $subDepartments = Department::whereNotNull('parent_id')->count();
        $withManager = Department::whereNotNull('manager_id')->count();

        return view('master-data.general.departments.index', compact(
            'departments',
            'users',
            'parentDepartments',
            'subDepartments',
            'withManager'
        ));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments,code',
                'parent_id' => 'nullable|exists:departments,id',
                'manager_id' => 'nullable|exists:users,id',
            ], [
                'name.required' => 'Nama departemen wajib diisi.',
                'code.required' => 'Kode departemen wajib diisi.',
                'code.unique' => 'Kode departemen sudah digunakan.',
                'parent_id.exists' => 'Induk departemen tidak valid.',
                'manager_id.exists' => 'Manager tidak valid.',
            ]);

            Department::create($validated);

            return redirect()
                ->route('master-data.general.departments.index')
                ->with('success', 'Departemen berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Department Store Error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat departemen.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $department = Department::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments,code,' . $id,
                'parent_id' => 'nullable|exists:departments,id',
                'manager_id' => 'nullable|exists:users,id',
            ], [
                'name.required' => 'Nama departemen wajib diisi.',
                'code.required' => 'Kode departemen wajib diisi.',
                'code.unique' => 'Kode departemen sudah digunakan.',
                'parent_id.exists' => 'Induk departemen tidak valid.',
                'manager_id.exists' => 'Manager tidak valid.',
            ]);

            // Prevent circular reference
            if ($validated['parent_id'] == $id) {
                return redirect()
                    ->back()
                    ->with('error', 'Departemen tidak dapat menjadi induk dari dirinya sendiri.');
            }

            // Check if trying to set parent to one of its children
            if ($validated['parent_id'] && $this->isDescendant($id, $validated['parent_id'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Departemen tidak dapat menjadi sub dari departemen turunannya.');
            }

            $department->update($validated);

            return redirect()
                ->route('master-data.general.departments.index')
                ->with('success', 'Departemen berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Department Update Error', [
                'error' => $e->getMessage(),
                'department_id' => $id,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui departemen.');
        }
    }

    public function destroy($id)
    {
        try {
            $department = Department::findOrFail($id);

            // Check if has children
            if ($department->children()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Departemen tidak dapat dihapus karena masih memiliki sub departemen.');
            }

            $department->delete();

            return redirect()
                ->back()
                ->with('success', 'Departemen berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Department Delete Error', [
                'error' => $e->getMessage(),
                'department_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menghapus departemen.');
        }
    }

    /**
     * Check if a department is a descendant of another
     */
    private function isDescendant($departmentId, $potentialParentId)
    {
        $department = Department::find($potentialParentId);

        while ($department) {
            if ($department->id == $departmentId) {
                return true;
            }
            $department = $department->parent;
        }

        return false;
    }
}