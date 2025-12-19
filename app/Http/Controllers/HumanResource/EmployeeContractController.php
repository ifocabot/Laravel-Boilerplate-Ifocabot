<?php

namespace App\Http\Controllers\HumanResource;

use App\Http\Controllers\Controller;
use App\Models\EmployeeContract;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EmployeeContractController extends Controller
{
    public function index($employeeId)
    {
        $employee = Employee::with([
            'contracts' => function ($query) {
                $query->orderBy('start_date', 'desc');
            }
        ])->findOrFail($employeeId);

        // Statistics
        $totalContracts = $employee->contracts->count();
        $activeContracts = $employee->contracts->where('is_active', true)->count();
        $expiringContracts = $employee->contracts()
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->count();

        return view('admin.hris.employees.contracts.index', compact(
            'employee',
            'totalContracts',
            'activeContracts',
            'expiringContracts'
        ));
    }

    public function store(Request $request, $employeeId)
    {
        DB::beginTransaction();

        try {
            $employee = Employee::findOrFail($employeeId);

            $validated = $request->validate([
                'contract_number' => 'nullable|string|max:100',
                'type' => 'required|in:pkwt,pkwtt,internship,probation',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'nullable', // FIX: Ubah dari required jadi nullable
                'notes' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf|max:5120',
            ], [
                'type.required' => 'Tipe kontrak wajib dipilih.',
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
                'document.mimes' => 'Dokumen harus berformat PDF.',
                'document.max' => 'Ukuran dokumen maksimal 5MB.',
            ]);

            $validated['employee_id'] = $employeeId;

            $validated['is_active'] = true;

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = 'contract_' . $employeeId . '_' . time() . '.pdf';
                $path = $file->storeAs('contracts', $filename, 'public');
                $validated['document_path'] = $path;
            }

            if ($validated['type'] === 'pkwtt' && !empty($validated['end_date'])) {
                throw ValidationException::withMessages([
                    'end_date' => 'Kontrak PKWTT (Tetap) tidak boleh memiliki tanggal akhir.'
                ]);
            }

            // Validate: PKWT must have end_date
            if ($validated['type'] === 'pkwt' && empty($validated['end_date'])) {
                throw ValidationException::withMessages([
                    'end_date' => 'Kontrak PKWT harus memiliki tanggal akhir.'
                ]);
            }

            $contract = EmployeeContract::create($validated);

            DB::commit();

            Log::info('Employee contract created', [
                'contract_id' => $contract->id,
                'employee_id' => $employeeId,
                'type' => $contract->type
            ]);

            return redirect()
                ->route('hris.employees.contracts.index', $employeeId)
                ->with('success', 'Kontrak berhasil ditambahkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Contract Store Error', [
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
            $contract = EmployeeContract::where('employee_id', $employeeId)->findOrFail($id);

            $validated = $request->validate([
                'contract_number' => 'nullable|string|max:100',
                'type' => 'required|in:pkwt,pkwtt,internship,probation',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'boolean', // FIX: nullable
                'notes' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf|max:5120',
            ], [
                'type.required' => 'Tipe kontrak wajib dipilih.',
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
                'document.mimes' => 'Dokumen harus berformat PDF.',
                'document.max' => 'Ukuran dokumen maksimal 5MB.',
            ]);

            // FIX: Handle checkbox - jika tidak ada berarti false
            $validated['is_active'] = $request->has('is_active') ? true : false;

            // Handle document upload
            if ($request->hasFile('document')) {
                // Delete old document if exists
                if ($contract->document_path && Storage::disk('public')->exists($contract->document_path)) {
                    Storage::disk('public')->delete($contract->document_path);
                }

                $file = $request->file('document');
                $filename = 'contract_' . $employeeId . '_' . time() . '.pdf';
                $path = $file->storeAs('contracts', $filename, 'public');
                $validated['document_path'] = $path;
            }

            // Validate: PKWTT should not have end_date
            if ($validated['type'] === 'pkwtt' && !empty($validated['end_date'])) {
                throw ValidationException::withMessages([
                    'end_date' => 'Kontrak PKWTT (Tetap) tidak boleh memiliki tanggal akhir.'
                ]);
            }

            // Validate: PKWT must have end_date
            if ($validated['type'] === 'pkwt' && empty($validated['end_date'])) {
                throw ValidationException::withMessages([
                    'end_date' => 'Kontrak PKWT harus memiliki tanggal akhir.'
                ]);
            }

            // If setting to active, deactivate others
            if ($validated['is_active'] && !$contract->is_active) {
                EmployeeContract::where('employee_id', $employeeId)
                    ->where('id', '!=', $id)
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'end_date' => now(),
                    ]);
            }

            $contract->update($validated);

            DB::commit();

            Log::info('Employee contract updated', [
                'contract_id' => $contract->id,
                'employee_id' => $employeeId
            ]);

            return redirect()
                ->route('hris.employees.contracts.index', $employeeId)
                ->with('success', 'Kontrak berhasil diperbarui.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Contract Update Error', [
                'error' => $e->getMessage(),
                'contract_id' => $id,
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
            $contract = EmployeeContract::where('employee_id', $employeeId)->findOrFail($id);

            // Prevent deleting active contract
            if ($contract->is_active) {
                return redirect()
                    ->back()
                    ->with('error', 'Kontrak yang masih aktif tidak dapat dihapus.');
            }

            // Delete document if exists
            if ($contract->document_path && Storage::disk('public')->exists($contract->document_path)) {
                Storage::disk('public')->delete($contract->document_path);
            }

            $contract->delete();

            DB::commit();

            Log::info('Employee contract deleted', [
                'contract_id' => $id,
                'employee_id' => $employeeId
            ]);

            return redirect()
                ->back()
                ->with('success', 'Kontrak berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Contract Delete Error', [
                'error' => $e->getMessage(),
                'contract_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate contract
     */
    public function deactivate(Request $request, $employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $contract = EmployeeContract::where('employee_id', $employeeId)->findOrFail($id);

            if (!$contract->is_active) {
                return redirect()
                    ->back()
                    ->with('error', 'Kontrak sudah tidak aktif.');
            }

            $validated = $request->validate([
                'end_date' => 'required|date|after_or_equal:' . $contract->start_date->format('Y-m-d'),
                'notes' => 'nullable|string',
            ], [
                'end_date.required' => 'Tanggal akhir wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
            ]);

            $contract->update([
                'is_active' => false,
                'end_date' => $validated['end_date'],
                'notes' => $validated['notes'] ?? $contract->notes,
            ]);

            DB::commit();

            Log::info('Employee contract deactivated', [
                'contract_id' => $contract->id,
                'employee_id' => $employeeId,
                'end_date' => $validated['end_date']
            ]);

            return redirect()
                ->back()
                ->with('success', 'Kontrak berhasil dinonaktifkan.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Contract Deactivate Error', [
                'error' => $e->getMessage(),
                'contract_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download contract document
     */
    public function download($employeeId, $id)
    {
        try {
            $contract = EmployeeContract::where('employee_id', $employeeId)->findOrFail($id);

            if (!$contract->document_path) {
                return redirect()
                    ->back()
                    ->with('error', 'Dokumen kontrak tidak ditemukan.');
            }

            if (!Storage::disk('public')->exists($contract->document_path)) {
                return redirect()
                    ->back()
                    ->with('error', 'File dokumen tidak ditemukan di storage.');
            }

            $employee = Employee::findOrFail($employeeId);
            $filename = 'Contract_' . $employee->nik . '_' . $contract->contract_number . '.pdf';

            return Storage::disk('public')->download($contract->document_path, $filename);

        } catch (\Exception $e) {
            Log::error('Employee Contract Download Error', [
                'error' => $e->getMessage(),
                'contract_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat mengunduh dokumen.');
        }
    }

    /**
     * Renew/extend contract
     */
    public function renew(Request $request, $employeeId, $id)
    {
        DB::beginTransaction();

        try {
            $oldContract = EmployeeContract::where('employee_id', $employeeId)->findOrFail($id);

            $validated = $request->validate([
                'contract_number' => 'nullable|string|max:100',
                'type' => 'required|in:pkwt,pkwtt,internship,probation',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'notes' => 'nullable|string',
                'document' => 'nullable|file|mimes:pdf|max:5120',
            ]);

            // Deactivate old contract
            $oldContract->update([
                'is_active' => false,
                'end_date' => $validated['start_date'],
            ]);

            // Handle document upload
            $documentPath = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = 'contract_' . $employeeId . '_' . time() . '.pdf';
                $documentPath = $file->storeAs('contracts', $filename, 'public');
            }

            // Create new contract
            $newContract = EmployeeContract::create([
                'employee_id' => $employeeId,
                'contract_number' => $validated['contract_number'],
                'type' => $validated['type'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'document_path' => $documentPath,
                'is_active' => true,
                'notes' => $validated['notes'] ?? 'Renewal from contract #' . $oldContract->id,
            ]);

            DB::commit();

            Log::info('Employee contract renewed', [
                'old_contract_id' => $oldContract->id,
                'new_contract_id' => $newContract->id,
                'employee_id' => $employeeId
            ]);

            return redirect()
                ->route('employees.contracts.index', $employeeId)
                ->with('success', 'Kontrak berhasil diperpanjang.');

        } catch (ValidationException $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee Contract Renew Error', [
                'error' => $e->getMessage(),
                'contract_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}