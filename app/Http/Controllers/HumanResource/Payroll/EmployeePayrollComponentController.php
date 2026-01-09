<?php

namespace App\Http\Controllers\HumanResource\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;
use App\Models\PayrollComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeePayrollComponentController extends Controller
{
    /**
     * Display employee payroll components
     */
    public function index($employeeId)
    {
        $employee = Employee::with([
            'payrollComponents' => function ($query) {
                $query->orderBy('effective_from', 'desc')
                    ->with('component');
            }
        ])->findOrFail($employeeId);

        // Master components untuk dropdown
        $earningComponents = PayrollComponent::active()
            ->earnings()
            ->ordered()
            ->get();

        $deductionComponents = PayrollComponent::active()
            ->deductions()
            ->ordered()
            ->get();

        // Statistics
        $totalComponents = $employee->payrollComponents->count();
        $activeComponents = $employee->payrollComponents->where('is_active', true)->count();
        $recurringComponents = $employee->payrollComponents->where('is_recurring', true)->count();

        // Calculate total salary
        $totalEarnings = $employee->activePayrollComponents
            ->filter(fn($c) => $c->component->type === 'earning')
            ->sum('amount');

        $totalDeductions = $employee->activePayrollComponents
            ->filter(fn($c) => $c->component->type === 'deduction')
            ->sum('amount');

        $netSalary = $totalEarnings - $totalDeductions;

        return view('admin.hris.payroll.employee-components.index', compact(
            'employee',
            'earningComponents',
            'deductionComponents',
            'totalComponents',
            'activeComponents',
            'recurringComponents',
            'totalEarnings',
            'totalDeductions',
            'netSalary'
        ));
    }

    /**
     * Store new employee payroll component
     */
    public function store(Request $request, $employeeId)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($employeeId);

            $validated = $request->validate([
                'component_id' => 'required|exists:payroll_components,id',
                'amount' => 'required|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after_or_equal:effective_from',
                'is_recurring' => 'nullable|boolean',
                'is_override' => 'nullable|boolean',
                'override_reason' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ], [
                'component_id.required' => 'Komponen wajib dipilih.',
                'amount.required' => 'Jumlah wajib diisi.',
                'effective_from.required' => 'Tanggal efektif wajib diisi.',
                'effective_to.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal efektif.',
            ]);

            // Get component to capture default amount
            $component = PayrollComponent::findOrFail($validated['component_id']);

            $validated['employee_id'] = $employeeId;
            $validated['is_active'] = true;
            $validated['is_recurring'] = $request->has('is_recurring');
            $validated['is_override'] = $request->has('is_override');
            $validated['unit'] = $validated['unit'] ?? 'IDR';

            // Store original amount for audit trail
            $validated['original_amount'] = $component->default_amount ?? $component->rate_per_day;

            $employeeComponent = EmployeePayrollComponent::create($validated);

            DB::commit();

            Log::info('Employee payroll component created', [
                'employee_component_id' => $employeeComponent->id,
                'employee_id' => $employeeId,
                'component_id' => $validated['component_id'],
                'is_override' => $validated['is_override'],
            ]);

            return redirect()
                ->route('hris.payroll.employee-components.index', $employeeId)
                ->with('success', 'Komponen gaji berhasil ditambahkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Payroll Component Store Error', [
                'error' => $e->getMessage(),
                'employee_id' => $employeeId,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update employee payroll component
     */
    public function update(Request $request, $employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $employeeComponent = EmployeePayrollComponent::where('employee_id', $employeeId)
                ->findOrFail($id);

            $validated = $request->validate([
                'component_id' => 'required|exists:payroll_components,id',
                'amount' => 'required|numeric|min:0',
                'unit' => 'nullable|string|max:50',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after_or_equal:effective_from',
                'is_active' => 'nullable|boolean',
                'is_recurring' => 'nullable|boolean',
                'is_override' => 'nullable|boolean',
                'override_reason' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ], [
                'component_id.required' => 'Komponen wajib dipilih.',
                'amount.required' => 'Jumlah wajib diisi.',
                'effective_from.required' => 'Tanggal efektif wajib diisi.',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_recurring'] = $request->has('is_recurring');
            $validated['is_override'] = $request->has('is_override');
            $validated['unit'] = $validated['unit'] ?? 'IDR';

            $employeeComponent->update($validated);

            DB::commit();

            Log::info('Employee payroll component updated', [
                'employee_component_id' => $employeeComponent->id,
                'employee_id' => $employeeId,
                'is_override' => $validated['is_override'],
            ]);

            return redirect()
                ->route('hris.payroll.employee-components.index', $employeeId)
                ->with('success', 'Komponen gaji berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Payroll Component Update Error', [
                'error' => $e->getMessage(),
                'employee_component_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete employee payroll component
     */
    public function destroy($employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $employeeComponent = EmployeePayrollComponent::where('employee_id', $employeeId)
                ->findOrFail($id);

            // Prevent deleting active component
            if ($employeeComponent->is_active) {
                return redirect()
                    ->back()
                    ->with('error', 'Komponen yang masih aktif tidak dapat dihapus. Nonaktifkan terlebih dahulu.');
            }

            $employeeComponent->delete();

            DB::commit();

            Log::info('Employee payroll component deleted', [
                'employee_component_id' => $id,
                'employee_id' => $employeeId,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Komponen gaji berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Payroll Component Delete Error', [
                'error' => $e->getMessage(),
                'employee_component_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate employee payroll component
     */
    public function deactivate(Request $request, $employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $employeeComponent = EmployeePayrollComponent::where('employee_id', $employeeId)
                ->findOrFail($id);

            if (!$employeeComponent->is_active) {
                return redirect()
                    ->back()
                    ->with('error', 'Komponen sudah tidak aktif.');
            }

            $validated = $request->validate([
                'effective_to' => 'required|date|after_or_equal:' . $employeeComponent->effective_from->format('Y-m-d'),
                'notes' => 'nullable|string',
            ], [
                'effective_to.required' => 'Tanggal berakhir wajib diisi.',
                'effective_to.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal efektif.',
            ]);

            $employeeComponent->deactivate($validated['effective_to']);

            if ($validated['notes']) {
                $employeeComponent->notes = $validated['notes'];
                $employeeComponent->save();
            }

            DB::commit();

            Log::info('Employee payroll component deactivated', [
                'employee_component_id' => $employeeComponent->id,
                'employee_id' => $employeeId,
                'effective_to' => $validated['effective_to'],
            ]);

            return redirect()
                ->back()
                ->with('success', 'Komponen gaji berhasil dinonaktifkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Payroll Component Deactivate Error', [
                'error' => $e->getMessage(),
                'employee_component_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}