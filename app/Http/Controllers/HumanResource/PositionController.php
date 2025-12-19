<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('department')
            ->orderBy('name')
            ->paginate(50);

        $departments = Department::orderBy('name')->get();

        // Calculate statistics
        $departmentCount = Position::distinct('department_id')->count('department_id');
        $withJobDesc = Position::whereNotNull('job_description')
            ->where('job_description', '!=', '')
            ->count();

        // Recently added this month
        $recentlyAdded = Position::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('master-data.hrms.positions.index', compact(
            'positions',
            'departments',
            'departmentCount',
            'withJobDesc',
            'recentlyAdded'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'department_id' => 'required|exists:departments,id',
                'job_description' => 'nullable|string|max:5000',
            ], [
                'name.required' => 'Nama posisi wajib diisi.',
                'department_id.required' => 'Departemen wajib dipilih.',
                'department_id.exists' => 'Departemen tidak valid.',
                'job_description.max' => 'Deskripsi pekerjaan maksimal 5000 karakter.',
            ]);

            $position = Position::create($validated);

            DB::commit();

            Log::info('Position created', [
                'position_id' => $position->id,
                'name' => $position->name,
                'department' => $position->department->name
            ]);

            return redirect()
                ->route('master-data.hrms.positions.index')
                ->with('success', 'Posisi "' . $position->name . '" berhasil dibuat.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Position Store Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $position = Position::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'department_id' => 'required|exists:departments,id',
                'job_description' => 'nullable|string|max:5000',
            ], [
                'name.required' => 'Nama posisi wajib diisi.',
                'department_id.required' => 'Departemen wajib dipilih.',
                'department_id.exists' => 'Departemen tidak valid.',
                'job_description.max' => 'Deskripsi pekerjaan maksimal 5000 karakter.',
            ]);

            $position->update($validated);

            DB::commit();

            return redirect()
                ->route('master-data.hrms.positions.index')
                ->with('success', 'Posisi "' . $position->name . '" berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Position Update Error', [
                'error' => $e->getMessage(),
                'position_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $position = Position::findOrFail($id);

            // Check if position is being used by employees
            // Uncomment this when Employee model is ready
            // if ($position->employees()->count() > 0) {
            //     return redirect()
            //         ->back()
            //         ->with('error', 'Posisi tidak dapat dihapus karena masih digunakan oleh karyawan.');
            // }

            $name = $position->name;
            $position->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Posisi "' . $name . '" berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Position Delete Error', [
                'error' => $e->getMessage(),
                'position_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get positions by department
     */
    public function getByDepartment($departmentId)
    {
        try {
            $positions = Position::where('department_id', $departmentId)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $positions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data posisi.'
            ], 500);
        }
    }
}