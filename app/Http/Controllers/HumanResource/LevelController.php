<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Level;

class LevelController extends Controller
{
    public function index()
    {
        $levels = Level::orderBy('name')->paginate(50);

        // Calculate statistics
        $lowestSalary = Level::min('min_salary') ?? 0;
        $highestSalary = Level::max('max_salary') ?? 0;

        // Average salary range (difference between max and min)
        $avgSalaryRange = Level::selectRaw('AVG(max_salary - min_salary) as avg_range')
            ->value('avg_range') ?? 0;

        return view('master-data.hrms.levels.index', compact(
            'levels',
            'lowestSalary',
            'highestSalary',
            'avgSalaryRange'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'grade_code' => 'required|string|max:50|unique:levels,grade_code',
                'min_salary' => 'required|numeric|min:0',
                'max_salary' => 'required|numeric|min:0|gt:min_salary',
            ], [
                'name.required' => 'Nama level wajib diisi.',
                'grade_code.required' => 'Grade code wajib diisi.',
                'grade_code.unique' => 'Grade code sudah digunakan.',
                'min_salary.required' => 'Gaji minimum wajib diisi.',
                'min_salary.min' => 'Gaji minimum tidak boleh negatif.',
                'max_salary.required' => 'Gaji maksimum wajib diisi.',
                'max_salary.min' => 'Gaji maksimum tidak boleh negatif.',
                'max_salary.gt' => 'Gaji maksimum harus lebih besar dari gaji minimum.',
            ]);

            $level = Level::create($validated);

            DB::commit();

            Log::info('Level created', [
                'level_id' => $level->id,
                'name' => $level->name,
                'grade_code' => $level->grade_code
            ]);

            return redirect()
                ->route('master-data.hris.levels.index')
                ->with('success', 'Level "' . $level->name . '" berhasil dibuat.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Level Store Error', [
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
            $level = Level::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'grade_code' => 'required|string|max:50|unique:levels,grade_code,' . $id,
                'min_salary' => 'required|numeric|min:0',
                'max_salary' => 'required|numeric|min:0|gt:min_salary',
            ], [
                'name.required' => 'Nama level wajib diisi.',
                'grade_code.required' => 'Grade code wajib diisi.',
                'grade_code.unique' => 'Grade code sudah digunakan.',
                'min_salary.required' => 'Gaji minimum wajib diisi.',
                'min_salary.min' => 'Gaji minimum tidak boleh negatif.',
                'max_salary.required' => 'Gaji maksimum wajib diisi.',
                'max_salary.min' => 'Gaji maksimum tidak boleh negatif.',
                'max_salary.gt' => 'Gaji maksimum harus lebih besar dari gaji minimum.',
            ]);

            $level->update($validated);

            DB::commit();

            return redirect()
                ->route('master-data.hris.levels.index')
                ->with('success', 'Level "' . $level->name . '" berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Level Update Error', [
                'error' => $e->getMessage(),
                'level_id' => $id,
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
            $level = Level::findOrFail($id);

            // Check if level is being used by employees
            // Uncomment this when Employee model is ready
            // if ($level->employees()->count() > 0) {
            //     return redirect()
            //         ->back()
            //         ->with('error', 'Level tidak dapat dihapus karena masih digunakan oleh karyawan.');
            // }

            $name = $level->name;
            $level->delete();

            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Level "' . $name . '" berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Level Delete Error', [
                'error' => $e->getMessage(),
                'level_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get salary range for a specific level
     */
    public function getSalaryRange($id)
    {
        try {
            $level = Level::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'min_salary' => $level->min_salary,
                    'max_salary' => $level->max_salary,
                    'range' => $level->max_salary - $level->min_salary,
                    'formatted_min' => 'Rp ' . number_format($level->min_salary, 0, ',', '.'),
                    'formatted_max' => 'Rp ' . number_format($level->max_salary, 0, ',', '.'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Level tidak ditemukan.'
            ], 404);
        }
    }
}