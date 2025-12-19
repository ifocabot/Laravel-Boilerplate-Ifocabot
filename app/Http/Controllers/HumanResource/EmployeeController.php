<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSensitiveData;
use App\Models\EmployeeCareer;
use App\Models\EmployeeContract;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees
     */
    public function index()
    {
        $employees = Employee::with([
            'careers' => function ($query) {
                $query->where('is_active', true)->with(['department', 'position', 'level', 'branch']);
            },
            'contracts' => function ($query) {
                $query->where('is_active', true);
            }
        ])
            ->orderBy('full_name')
            ->paginate(20);

        // Stats
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $resignedEmployees = Employee::whereIn('status', ['resigned', 'terminated'])->count();
        $newThisMonth = Employee::whereMonth('join_date', now()->month)
            ->whereYear('join_date', now()->year)
            ->count();

        // Master data untuk filter
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('admin.hris.employees.index', compact(
            'employees',
            'totalEmployees',
            'activeEmployees',
            'resignedEmployees',
            'newThisMonth',
            'departments',
            'positions',
            'levels',
            'locations'
        ));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('status', 'active')->orderBy('full_name')->get();
        $autoNik = Employee::generateNik();

        return view('admin.hris.employees.create', compact(
            'departments',
            'positions',
            'levels',
            'locations',
            'employees',
            'autoNik'
        ));
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                // Basic Info
                'nik' => 'nullable|string|max:20|unique:employees,nik',
                'full_name' => 'required|string|max:150',
                'email_corporate' => 'nullable|email|max:100|unique:employees,email_corporate',
                'phone_number' => 'nullable|string|max:20',

                // Personal Data
                'place_of_birth' => 'nullable|string|max:100',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female',
                'marital_status' => 'nullable|in:single,married,widow,widower',
                'religion' => 'nullable|string|max:50',

                // Sensitive Data
                'id_card_number' => 'nullable|string|max:20',
                'npwp_number' => 'nullable|string|max:20',
                'bpjs_tk_number' => 'nullable|string|max:20',
                'bpjs_kes_number' => 'nullable|string|max:20',
                'bank_name' => 'nullable|string|max:100',
                'bank_account_number' => 'nullable|string|max:50',
                'bank_account_holder' => 'nullable|string|max:150',
                'tax_status' => 'nullable|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
                'emergency_contact_name' => 'nullable|string|max:150',
                'emergency_contact_relationship' => 'nullable|string|max:50',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_address' => 'nullable|string',

                // Employment - Basic
                'join_date' => 'required|date',
                'status' => 'required|in:active,resigned,terminated',

                // Career fields
                'department_id' => 'required|exists:departments,id',
                'position_id' => 'required|exists:positions,id',
                'level_id' => 'required|exists:levels,id',
                'branch_id' => 'nullable|exists:locations,id',
                'manager_id' => 'nullable|exists:employees,id',

                // User Account (Optional)
                'create_user_account' => 'nullable|boolean',
                'user_email' => 'required_if:create_user_account,1|nullable|email|unique:users,email',
                'user_password' => 'required_if:create_user_account,1|nullable|string|min:8',
            ], [
                'nik.required' => 'NIK wajib diisi.',
                'nik.unique' => 'NIK sudah terdaftar.',
                'full_name.required' => 'Nama lengkap wajib diisi.',
                'join_date.required' => 'Tanggal bergabung wajib diisi.',
                'status.required' => 'Status kepegawaian wajib dipilih.',
                'department_id.required' => 'Departemen wajib dipilih.',
                'position_id.required' => 'Posisi wajib dipilih.',
                'level_id.required' => 'Level wajib dipilih.',
                'user_email.unique' => 'Email user sudah terdaftar.',
            ]);

            // Create User Account if requested
            $userId = null;
            if ($request->has('create_user_account') && $request->create_user_account) {
                $user = User::create([
                    'name' => $validated['full_name'],
                    'email' => $validated['user_email'],
                    'password' => Hash::make($validated['user_password']),
                ]);
                $userId = $user->id;
            }

            if (empty($validated['nik'])) {
                $validated['nik'] = Employee::generateNik();
            }

            // Create Employee (NON-SENSITIVE DATA ONLY)
            $employeeData = [
                'user_id' => $userId,
                'nik' => $validated['nik'],
                'full_name' => $validated['full_name'],
                'email_corporate' => $validated['email_corporate'],
                'phone_number' => $validated['phone_number'],
                'place_of_birth' => $validated['place_of_birth'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'marital_status' => $validated['marital_status'],
                'religion' => $validated['religion'],
                'join_date' => $validated['join_date'],
                'status' => $validated['status'],
            ];

            $employee = Employee::create($employeeData);

            // Create Sensitive Data (ENCRYPTED - auto-created by boot method, just update)
            $employee->sensitiveData()->update([
                'id_card_number' => $validated['id_card_number'],
                'npwp_number' => $validated['npwp_number'],
                'bpjs_tk_number' => $validated['bpjs_tk_number'],
                'bpjs_kes_number' => $validated['bpjs_kes_number'],
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_account_holder' => $validated['bank_account_holder'],
                'tax_status' => $validated['tax_status'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_relationship' => $validated['emergency_contact_relationship'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'emergency_contact_address' => $validated['emergency_contact_address'],
            ]);

            // Create initial career record
            EmployeeCareer::create([
                'employee_id' => $employee->id,
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'level_id' => $validated['level_id'],
                'branch_id' => $validated['branch_id'] ?? null,
                'manager_id' => $validated['manager_id'] ?? null,
                'start_date' => $validated['join_date'],
                'end_date' => null,
                'is_active' => true,
                'notes' => 'Initial employment',
            ]);

            // Create DEFAULT contract record (Probation - 3 months)
            EmployeeContract::create([
                'employee_id' => $employee->id,
                'contract_number' => null,
                'type' => 'probation',
                'start_date' => $validated['join_date'],
                'end_date' => Carbon::parse($validated['join_date'])->addMonths(3),
                'is_active' => true,
                'notes' => 'Probation period - 3 months',
            ]);

            DB::commit();

            Log::info('Employee created', [
                'employee_id' => $employee->id,
                'nik' => $employee->nik,
                'name' => $employee->full_name,
            ]);

            return redirect()
                ->route('hris.employees.show', $employee->id)
                ->with('success', 'Karyawan "' . $employee->full_name . '" berhasil ditambahkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified employee
     */
    public function show($id)
    {
        $employee = Employee::with([
            'careers' => function ($query) {
                $query->orderBy('start_date', 'desc')
                    ->with(['department', 'position', 'level', 'branch', 'manager']);
            },
            'contracts' => function ($query) {
                $query->orderBy('start_date', 'desc');
            },
            'families',
            'sensitiveData',
            'user'
        ])->findOrFail($id);

        return view('admin.hris.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit($id)
    {
        $employee = Employee::with(['careers', 'contracts', 'sensitiveData'])->findOrFail($id);

        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();
        $levels = Level::orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('status', 'active')
            ->where('id', '!=', $id)
            ->orderBy('full_name')
            ->get();

        return view('admin.hris.employees.edit', compact(
            'employee',
            'departments',
            'positions',
            'levels',
            'locations',
            'employees'
        ));
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);

            $validated = $request->validate([
                // Basic Info
                'nik' => 'required|string|max:20|unique:employees,nik,' . $id,
                'full_name' => 'required|string|max:150',
                'email_corporate' => 'nullable|email|max:100|unique:employees,email_corporate,' . $id,
                'phone_number' => 'nullable|string|max:20',

                // Personal Data
                'place_of_birth' => 'nullable|string|max:100',
                'date_of_birth' => 'nullable|date|before:today',
                'gender' => 'nullable|in:male,female',
                'marital_status' => 'nullable|in:single,married,widow,widower',
                'religion' => 'nullable|string|max:50',

                // Sensitive Data
                'id_card_number' => 'nullable|string|max:20',
                'npwp_number' => 'nullable|string|max:20',
                'bpjs_tk_number' => 'nullable|string|max:20',
                'bpjs_kes_number' => 'nullable|string|max:20',
                'bank_name' => 'nullable|string|max:100',
                'bank_account_number' => 'nullable|string|max:50',
                'bank_account_holder' => 'nullable|string|max:150',
                'tax_status' => 'nullable|in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3',
                'emergency_contact_name' => 'nullable|string|max:150',
                'emergency_contact_relationship' => 'nullable|string|max:50',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_address' => 'nullable|string',

                // Employment
                'join_date' => 'required|date',
                'status' => 'required|in:active,resigned,terminated',
            ]);

            // Update Employee (NON-SENSITIVE DATA)
            $employee->update([
                'nik' => $validated['nik'],
                'full_name' => $validated['full_name'],
                'email_corporate' => $validated['email_corporate'],
                'phone_number' => $validated['phone_number'],
                'place_of_birth' => $validated['place_of_birth'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'marital_status' => $validated['marital_status'],
                'religion' => $validated['religion'],
                'join_date' => $validated['join_date'],
                'status' => $validated['status'],
            ]);

            // Update Sensitive Data (ENCRYPTED)
            $employee->sensitiveData()->update([
                'id_card_number' => $validated['id_card_number'],
                'npwp_number' => $validated['npwp_number'],
                'bpjs_tk_number' => $validated['bpjs_tk_number'],
                'bpjs_kes_number' => $validated['bpjs_kes_number'],
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_account_holder' => $validated['bank_account_holder'],
                'tax_status' => $validated['tax_status'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'emergency_contact_relationship' => $validated['emergency_contact_relationship'],
                'emergency_contact_phone' => $validated['emergency_contact_phone'],
                'emergency_contact_address' => $validated['emergency_contact_address'],
            ]);

            DB::commit();

            Log::info('Employee updated', [
                'employee_id' => $employee->id,
                'nik' => $employee->nik,
            ]);

            return redirect()
                ->route('hris.employees.show', $employee->id)
                ->with('success', 'Data karyawan berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Update Error', [
                'error' => $e->getMessage(),
                'employee_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified employee
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);

            // Soft check - prevent deleting if employee has dependencies
            if ($employee->careers->count() > 1 || $employee->contracts->count() > 1) {
                return redirect()
                    ->back()
                    ->with('error', 'Karyawan dengan riwayat karir/kontrak tidak dapat dihapus. Gunakan fitur resign/terminate.');
            }

            $name = $employee->full_name;
            $employee->delete();

            DB::commit();

            Log::info('Employee deleted', [
                'employee_id' => $id,
                'name' => $name,
            ]);

            return redirect()
                ->route('hris.employees.index')
                ->with('success', 'Karyawan "' . $name . '" berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Delete Error', [
                'error' => $e->getMessage(),
                'employee_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    /**
     * Resign employee
     */
    public function resign(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);

            $validated = $request->validate([
                'resign_date' => 'required|date|after_or_equal:' . $employee->join_date->format('Y-m-d'),
                'reason' => 'required|string|max:500',
            ], [
                'resign_date.required' => 'Tanggal resign wajib diisi.',
                'resign_date.after_or_equal' => 'Tanggal resign harus setelah tanggal bergabung.',
                'reason.required' => 'Alasan resign wajib diisi.',
            ]);

            $employee->resign(
                Carbon::parse($validated['resign_date']),
                $validated['reason']
            );

            DB::commit();

            Log::info('Employee resigned', [
                'employee_id' => $employee->id,
                'resign_date' => $validated['resign_date'],
                'reason' => $validated['reason'],
            ]);

            return redirect()
                ->route('hris.employees.show', $employee->id)
                ->with('success', 'Karyawan "' . $employee->full_name . '" berhasil di-resign.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Resign Error', [
                'error' => $e->getMessage(),
                'employee_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Terminate employee
     */
    public function terminate(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);

            $validated = $request->validate([
                'terminate_date' => 'required|date|after_or_equal:' . $employee->join_date->format('Y-m-d'),
                'reason' => 'required|string|max:500',
            ], [
                'terminate_date.required' => 'Tanggal terminate wajib diisi.',
                'terminate_date.after_or_equal' => 'Tanggal terminate harus setelah tanggal bergabung.',
                'reason.required' => 'Alasan terminate wajib diisi.',
            ]);

            $employee->terminate(
                Carbon::parse($validated['terminate_date']),
                $validated['reason']
            );

            DB::commit();

            Log::info('Employee terminated', [
                'employee_id' => $employee->id,
                'terminate_date' => $validated['terminate_date'],
                'reason' => $validated['reason'],
            ]);

            return redirect()
                ->route('hris.employees.show', $employee->id)
                ->with('success', 'Karyawan "' . $employee->full_name . '" berhasil di-terminate.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Terminate Error', [
                'error' => $e->getMessage(),
                'employee_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reactivate employee
     */
    public function reactivate($id)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($id);

            if ($employee->status === 'active') {
                return redirect()
                    ->back()
                    ->with('error', 'Karyawan sudah dalam status aktif.');
            }

            $employee->reactivate();

            DB::commit();

            Log::info('Employee reactivated', [
                'employee_id' => $employee->id,
            ]);

            return redirect()
                ->route('hris.employees.show', $employee->id)
                ->with('success', 'Karyawan "' . $employee->full_name . '" berhasil diaktifkan kembali.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Reactivate Error', [
                'error' => $e->getMessage(),
                'employee_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    public function generateNik()
    {
        try {
            $nik = Employee::generateNik();

            return response()->json([
                'success' => true,
                'nik' => $nik,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}