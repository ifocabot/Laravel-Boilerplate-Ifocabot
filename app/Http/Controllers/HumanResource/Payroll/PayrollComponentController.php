<?php

namespace App\Http\Controllers\HumanResource\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollComponentController extends Controller
{
    /**
     * Display listing of payroll components
     */
    public function index()
    {
        $components = PayrollComponent::ordered()->get();

        // Group by type
        $earnings = $components->where('type', 'earning');
        $deductions = $components->where('type', 'deduction');

        // Statistics
        $totalComponents = $components->count();
        $activeComponents = $components->where('is_active', true)->count();
        $earningsCount = $earnings->count();
        $deductionsCount = $deductions->count();

        return view('admin.hris.payroll.components.index', compact(
            'components',
            'earnings',
            'deductions',
            'totalComponents',
            'activeComponents',
            'earningsCount',
            'deductionsCount'
        ));
    }

    /**
     * Store new component
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:payroll_components,code',
                'name' => 'required|string|max:150',
                'description' => 'nullable|string',
                'type' => 'required|in:earning,deduction',
                'category' => 'required|in:basic_salary,fixed_allowance,variable_allowance,statutory,other_deduction',
                'calculation_type' => 'required|in:fixed,daily_rate,hourly_rate,percentage,formula',
                'calculation_formula' => 'nullable|string',
                'rate_per_day' => 'nullable|numeric|min:0',
                'rate_per_hour' => 'nullable|numeric|min:0',
                'percentage_value' => 'nullable|numeric|min:0|max:100',
                'proration_type' => 'nullable|in:none,daily,attendance',
                'forfeit_on_alpha' => 'nullable|boolean',
                'forfeit_on_late' => 'nullable|boolean',
                'min_attendance_percent' => 'nullable|integer|min:0|max:100',
                'is_taxable' => 'nullable|boolean',
                'is_bpjs_base' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
                'show_on_slip' => 'nullable|boolean',
            ]);

            $validated['is_taxable'] = $request->has('is_taxable');
            $validated['is_bpjs_base'] = $request->has('is_bpjs_base');
            $validated['show_on_slip'] = $request->has('show_on_slip') ? true : false;
            $validated['forfeit_on_alpha'] = $request->has('forfeit_on_alpha');
            $validated['forfeit_on_late'] = $request->has('forfeit_on_late');
            $validated['is_active'] = true;

            $component = PayrollComponent::create($validated);

            // Guardrail validation (Phase 3)
            $validator = new \App\Services\Payroll\ComponentValidator();
            if (!$validator->validate($component)) {
                // Log warnings but don't block - just inform user
                Log::warning('Component config warnings', [
                    'component_id' => $component->id,
                    'warnings' => $validator->getWarnings(),
                    'errors' => $validator->getErrors(),
                ]);
            }

            DB::commit();

            Log::info('Payroll component created', [
                'component_id' => $component->id,
                'code' => $component->code,
            ]);

            return redirect()
                ->route('hris.payroll.components.index')
                ->with('success', 'Komponen payroll berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payroll Component Create Error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update component
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $component = PayrollComponent::findOrFail($id);

            $validated = $request->validate([
                'code' => 'required|string|max:50|unique:payroll_components,code,' . $id,
                'name' => 'required|string|max:150',
                'description' => 'nullable|string',
                'type' => 'required|in:earning,deduction',
                'category' => 'required|in:basic_salary,fixed_allowance,variable_allowance,statutory,other_deduction',
                'calculation_type' => 'required|in:fixed,daily_rate,hourly_rate,percentage,formula',
                'calculation_formula' => 'nullable|string',
                'rate_per_day' => 'nullable|numeric|min:0',
                'rate_per_hour' => 'nullable|numeric|min:0',
                'percentage_value' => 'nullable|numeric|min:0|max:100',
                'proration_type' => 'nullable|in:none,daily,attendance',
                'forfeit_on_alpha' => 'nullable|boolean',
                'forfeit_on_late' => 'nullable|boolean',
                'min_attendance_percent' => 'nullable|integer|min:0|max:100',
                'is_taxable' => 'nullable|boolean',
                'is_bpjs_base' => 'nullable|boolean',
                'display_order' => 'nullable|integer|min:0',
                'show_on_slip' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            $validated['is_taxable'] = $request->has('is_taxable');
            $validated['is_bpjs_base'] = $request->has('is_bpjs_base');
            $validated['show_on_slip'] = $request->has('show_on_slip');
            $validated['forfeit_on_alpha'] = $request->has('forfeit_on_alpha');
            $validated['forfeit_on_late'] = $request->has('forfeit_on_late');
            $validated['is_active'] = $request->has('is_active');

            $component->update($validated);

            // Guardrail validation (Phase 3)
            $validator = new \App\Services\Payroll\ComponentValidator();
            if (!$validator->validate($component)) {
                Log::warning('Component config warnings', [
                    'component_id' => $component->id,
                    'warnings' => $validator->getWarnings(),
                    'errors' => $validator->getErrors(),
                ]);
            }

            DB::commit();

            Log::info('Payroll component updated', [
                'component_id' => $component->id,
            ]);

            return redirect()
                ->route('hris.payroll.components.index')
                ->with('success', 'Komponen payroll berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payroll Component Update Error', [
                'error' => $e->getMessage(),
                'component_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete component
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $component = PayrollComponent::findOrFail($id);

            // Check if component is used
            $usageCount = $component->employeeComponents()->count();

            if ($usageCount > 0) {
                return redirect()
                    ->back()
                    ->with('error', "Komponen ini digunakan oleh {$usageCount} karyawan. Nonaktifkan komponen daripada menghapusnya.");
            }

            $componentName = $component->name;
            $component->delete();

            DB::commit();

            Log::info('Payroll component deleted', [
                'component_id' => $id,
                'component_name' => $componentName,
            ]);

            return redirect()
                ->route('hris.payroll.components.index')
                ->with('success', "Komponen \"{$componentName}\" berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payroll Component Delete Error', [
                'error' => $e->getMessage(),
                'component_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}