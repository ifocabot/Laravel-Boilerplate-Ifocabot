<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\EmployeeCertification;
use App\Models\Certification;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmployeeCertificationController extends Controller
{
    /**
     * Display a listing of employee certifications
     */
    public function index(Request $request)
    {
        $query = EmployeeCertification::with(['employee', 'certification', 'verifier']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('certification_id')) {
            $query->where('certification_id', $request->certification_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('certification_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        $employeeCertifications = $query->orderBy('issue_date', 'desc')->paginate(20);

        $certifications = Certification::active()->orderBy('name')->get();

        // Stats
        $totalCerts = EmployeeCertification::count();
        $activeCerts = EmployeeCertification::active()->count();
        $expiringSoonCerts = EmployeeCertification::expiringSoon(30)->count();
        $expiredCerts = EmployeeCertification::expired()->count();

        return view('admin.hris.training.certifications.employee.index', compact(
            'employeeCertifications',
            'certifications',
            'totalCerts',
            'activeCerts',
            'expiringSoonCerts',
            'expiredCerts'
        ));
    }

    /**
     * Show certifications expiring soon
     */
    public function expiring(Request $request)
    {
        $days = $request->get('days', 30);

        $expiringCertifications = EmployeeCertification::with(['employee', 'certification'])
            ->expiringSoon($days)
            ->orderBy('expiry_date')
            ->paginate(20);

        return view('admin.hris.training.certifications.employee.expiring', compact(
            'expiringCertifications',
            'days'
        ));
    }

    /**
     * Show the form for creating a new employee certification
     */
    public function create()
    {
        $employees = Employee::active()->orderBy('full_name')->get();
        $certifications = Certification::active()->orderBy('name')->get();

        return view('admin.hris.training.certifications.employee.create', compact(
            'employees',
            'certifications'
        ));
    }

    /**
     * Store a newly created employee certification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'certification_id' => 'required|exists:certifications,id',
            'certification_number' => 'nullable|string|max:100',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cost' => 'nullable|numeric|min:0',
            'company_sponsored' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $filePath = null;
            if ($request->hasFile('file')) {
                $filePath = $request->file('file')->store(
                    'certifications/' . $validated['employee_id'],
                    'public'
                );
            }

            // Auto-calculate expiry date if certification has validity period
            $expiryDate = $validated['expiry_date'] ?? null;
            if (!$expiryDate) {
                $certification = Certification::find($validated['certification_id']);
                if ($certification && $certification->validity_months) {
                    $expiryDate = \Carbon\Carbon::parse($validated['issue_date'])
                        ->addMonths($certification->validity_months);
                }
            }

            EmployeeCertification::create([
                'employee_id' => $validated['employee_id'],
                'certification_id' => $validated['certification_id'],
                'certification_number' => $validated['certification_number'] ?? null,
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $expiryDate,
                'file_path' => $filePath,
                'status' => 'pending_verification',
                'cost' => $validated['cost'] ?? 0,
                'company_sponsored' => $request->has('company_sponsored'),
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Employee certification created', [
                'employee_id' => $validated['employee_id'],
                'certification_id' => $validated['certification_id'],
            ]);

            return redirect()
                ->route('hris.training.employee-certifications.index')
                ->with('success', 'Sertifikasi karyawan berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Employee certification creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified employee certification
     */
    public function show(string $id)
    {
        $employeeCertification = EmployeeCertification::with(['employee', 'certification', 'verifier'])
            ->findOrFail($id);

        return view('admin.hris.training.certifications.employee.show', compact('employeeCertification'));
    }

    /**
     * Show the form for editing an employee certification
     */
    public function edit(string $id)
    {
        $employeeCertification = EmployeeCertification::findOrFail($id);
        $employees = Employee::active()->orderBy('full_name')->get();
        $certifications = Certification::active()->orderBy('name')->get();

        return view('admin.hris.training.certifications.employee.edit', compact(
            'employeeCertification',
            'employees',
            'certifications'
        ));
    }

    /**
     * Update the specified employee certification
     */
    public function update(Request $request, string $id)
    {
        $employeeCertification = EmployeeCertification::findOrFail($id);

        $validated = $request->validate([
            'certification_number' => 'nullable|string|max:100',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cost' => 'nullable|numeric|min:0',
            'company_sponsored' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|in:active,expired,revoked,pending_verification',
        ]);

        try {
            $filePath = $employeeCertification->file_path;

            if ($request->hasFile('file')) {
                // Delete old file
                if ($filePath) {
                    Storage::disk('public')->delete($filePath);
                }

                $filePath = $request->file('file')->store(
                    'certifications/' . $employeeCertification->employee_id,
                    'public'
                );
            }

            $employeeCertification->update([
                'certification_number' => $validated['certification_number'] ?? null,
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'file_path' => $filePath,
                'cost' => $validated['cost'] ?? 0,
                'company_sponsored' => $request->has('company_sponsored'),
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'] ?? $employeeCertification->status,
            ]);

            Log::info('Employee certification updated', ['id' => $id]);

            return redirect()
                ->route('hris.training.employee-certifications.index')
                ->with('success', 'Sertifikasi karyawan berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Employee certification update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified employee certification
     */
    public function destroy(string $id)
    {
        try {
            $employeeCertification = EmployeeCertification::findOrFail($id);

            // Delete file
            if ($employeeCertification->file_path) {
                Storage::disk('public')->delete($employeeCertification->file_path);
            }

            $employeeCertification->delete();

            Log::info('Employee certification deleted', ['id' => $id]);

            return redirect()
                ->route('hris.training.employee-certifications.index')
                ->with('success', 'Sertifikasi karyawan berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Employee certification deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Verify an employee certification
     */
    public function verify(string $id)
    {
        try {
            $employeeCertification = EmployeeCertification::findOrFail($id);

            if ($employeeCertification->status !== 'pending_verification') {
                return redirect()
                    ->back()
                    ->with('error', 'Sertifikasi ini sudah diverifikasi.');
            }

            $employeeCertification->verify(Auth::id());

            Log::info('Employee certification verified', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Sertifikasi berhasil diverifikasi.');

        } catch (\Exception $e) {
            Log::error('Employee certification verify error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download certification file
     */
    public function download(string $id)
    {
        $employeeCertification = EmployeeCertification::findOrFail($id);

        if (!$employeeCertification->file_path) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($employeeCertification->file_path);
    }
}
