<?php

namespace App\Http\Controllers\HumanResource\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeePayrollComponent;
use App\Models\PayrollComponent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeSalaryManagementController extends Controller
{
    /**
     * Display centralized employee salary management
     */
    public function index(Request $request)
    {
        $query = Employee::where('status', 'active')
            ->with([
                'activePayrollComponents.component',
                'currentCareer.department',
                'currentCareer.position',
            ]);

        // Filter by department
        if ($request->filled('department_id')) {
            $query->whereHas('currentCareer', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by position
        if ($request->filled('position_id')) {
            $query->whereHas('currentCareer', function ($q) use ($request) {
                $q->where('position_id', $request->position_id);
            });
        }

        // Search by name or NIK
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('full_name')->paginate(20);

        // Calculate salary summary for each employee
        foreach ($employees as $employee) {
            $earnings = $employee->activePayrollComponents
                ->filter(fn($c) => $c->component->type === 'earning')
                ->sum('amount');

            $deductions = $employee->activePayrollComponents
                ->filter(fn($c) => $c->component->type === 'deduction')
                ->sum('amount');

            $employee->total_earnings = $earnings;
            $employee->total_deductions = $deductions;
            $employee->net_salary = $earnings - $deductions;
        }

        // Get all active components for assignment
        $earningComponents = PayrollComponent::active()->earnings()->ordered()->get();
        $deductionComponents = PayrollComponent::active()->deductions()->ordered()->get();

        // Get departments and positions for filters
        $departments = \App\Models\Department::orderBy('name')->get();
        $positions = \App\Models\Position::orderBy('name')->get();

        return view('admin.hris.payroll.employee-salaries.index', compact(
            'employees',
            'earningComponents',
            'deductionComponents',
            'departments',
            'positions'
        ));
    }

    /**
     * Show employee salary detail in modal/slide-over
     */
    public function show($employeeId)
    {
        $employee = Employee::with([
            'payrollComponents.component',
            'currentCareer.department',
            'currentCareer.position',
        ])->findOrFail($employeeId);

        $earningComponents = PayrollComponent::active()->earnings()->ordered()->get();
        $deductionComponents = PayrollComponent::active()->deductions()->ordered()->get();

        return view('admin.hris.payroll.employee-salaries.show', compact(
            'employee',
            'earningComponents',
            'deductionComponents'
        ));
    }

    /**
     * Assign component to employee
     */
    public function assign(Request $request, $employeeId)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($employeeId);

            $validated = $request->validate([
                'component_id' => 'required|exists:payroll_components,id',
                'amount' => 'required|numeric|min:0',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after_or_equal:effective_from',
                'is_recurring' => 'nullable|boolean',
            ]);

            $validated['employee_id'] = $employeeId;
            $validated['is_active'] = true;
            $validated['is_recurring'] = $request->has('is_recurring');
            $validated['unit'] = 'IDR';

            EmployeePayrollComponent::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komponen berhasil ditambahkan',
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
     * Update employee component
     */
    public function updateComponent(Request $request, $employeeId, $componentId)
    {
        DB::beginTransaction();

        try {
            $employeeComponent = EmployeePayrollComponent::where('employee_id', $employeeId)
                ->where('id', $componentId)
                ->firstOrFail();

            $validated = $request->validate([
                'amount' => 'required|numeric|min:0',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after_or_equal:effective_from',
                'is_active' => 'nullable|boolean',
                'is_recurring' => 'nullable|boolean',
            ]);

            $validated['is_active'] = $request->has('is_active');
            $validated['is_recurring'] = $request->has('is_recurring');

            $employeeComponent->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komponen berhasil diperbarui',
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
     * Delete employee component
     */
    public function destroyComponent($employeeId, $componentId)
    {
        DB::beginTransaction();

        try {
            $employeeComponent = EmployeePayrollComponent::where('employee_id', $employeeId)
                ->where('id', $componentId)
                ->firstOrFail();

            $employeeComponent->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komponen berhasil dihapus',
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
     * Deactivate employee component
     */
    public function deactivate(Request $request, $employeeId, $componentId)
    {
        DB::beginTransaction();

        try {
            $employeeComponent = EmployeePayrollComponent::where('employee_id', $employeeId)
                ->where('id', $componentId)
                ->firstOrFail();

            $validated = $request->validate([
                'effective_to' => 'required|date',
            ]);

            $employeeComponent->deactivate($validated['effective_to']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Komponen berhasil dinonaktifkan',
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
     * Bulk assign components to multiple employees
     */
    public function bulkAssign(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'employee_ids' => 'required|array',
                'employee_ids.*' => 'exists:employees,id',
                'component_id' => 'required|exists:payroll_components,id',
                'amount' => 'required|numeric|min:0',
                'effective_from' => 'required|date',
                'effective_to' => 'nullable|date|after_or_equal:effective_from',
                'is_recurring' => 'nullable|boolean',
            ]);

            $created = 0;
            foreach ($validated['employee_ids'] as $employeeId) {
                EmployeePayrollComponent::create([
                    'employee_id' => $employeeId,
                    'component_id' => $validated['component_id'],
                    'amount' => $validated['amount'],
                    'unit' => 'IDR',
                    'effective_from' => $validated['effective_from'],
                    'effective_to' => $validated['effective_to'] ?? null,
                    'is_active' => true,
                    'is_recurring' => $request->has('is_recurring'),
                ]);
                $created++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Komponen berhasil ditambahkan ke {$created} karyawan",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}