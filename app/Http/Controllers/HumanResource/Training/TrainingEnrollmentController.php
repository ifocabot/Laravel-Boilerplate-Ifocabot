<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\TrainingEnrollment;
use App\Models\TrainingProgram;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrainingEnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments
     */
    public function index(Request $request)
    {
        $query = TrainingEnrollment::with(['program', 'employee', 'approver']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('program_id')) {
            $query->where('training_program_id', $request->program_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->paginate(20);

        $programs = TrainingProgram::whereIn('status', ['open', 'ongoing'])
            ->orderBy('name')
            ->get();

        // Stats
        $totalEnrollments = TrainingEnrollment::count();
        $pendingEnrollments = TrainingEnrollment::pending()->count();
        $inProgressEnrollments = TrainingEnrollment::whereIn('status', ['enrolled', 'in_progress'])->count();
        $completedEnrollments = TrainingEnrollment::completed()->count();

        return view('admin.hris.training.enrollments.index', compact(
            'enrollments',
            'programs',
            'totalEnrollments',
            'pendingEnrollments',
            'inProgressEnrollments',
            'completedEnrollments'
        ));
    }

    /**
     * Enroll an employee to a program
     */
    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'training_program_id' => 'required|exists:training_programs,id',
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $program = TrainingProgram::findOrFail($validated['training_program_id']);

            // Check if program is open for enrollment
            if (!$program->canEnroll()) {
                return redirect()
                    ->back()
                    ->with('error', 'Program tidak terbuka untuk pendaftaran atau sudah penuh.');
            }

            // Check if already enrolled
            $existingEnrollment = TrainingEnrollment::where('training_program_id', $validated['training_program_id'])
                ->where('employee_id', $validated['employee_id'])
                ->whereNotIn('status', ['cancelled', 'failed'])
                ->first();

            if ($existingEnrollment) {
                return redirect()
                    ->back()
                    ->with('error', 'Karyawan sudah terdaftar di program ini.');
            }

            TrainingEnrollment::create([
                'training_program_id' => $validated['training_program_id'],
                'employee_id' => $validated['employee_id'],
                'enrollment_date' => now(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Employee enrolled to training', [
                'program_id' => $validated['training_program_id'],
                'employee_id' => $validated['employee_id'],
            ]);

            return redirect()
                ->back()
                ->with('success', 'Karyawan berhasil didaftarkan ke program training.');

        } catch (\Exception $e) {
            Log::error('Training enrollment error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk enroll employees
     */
    public function bulkEnroll(Request $request)
    {
        $validated = $request->validate([
            'training_program_id' => 'required|exists:training_programs,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        try {
            $program = TrainingProgram::findOrFail($validated['training_program_id']);
            $enrolledCount = 0;
            $skippedCount = 0;

            foreach ($validated['employee_ids'] as $employeeId) {
                // Check for existing enrollment
                $exists = TrainingEnrollment::where('training_program_id', $validated['training_program_id'])
                    ->where('employee_id', $employeeId)
                    ->whereNotIn('status', ['cancelled', 'failed'])
                    ->exists();

                if (!$exists && !$program->is_full) {
                    TrainingEnrollment::create([
                        'training_program_id' => $validated['training_program_id'],
                        'employee_id' => $employeeId,
                        'enrollment_date' => now(),
                        'status' => 'pending',
                    ]);
                    $enrolledCount++;
                } else {
                    $skippedCount++;
                }
            }

            Log::info('Bulk enrollment completed', [
                'program_id' => $validated['training_program_id'],
                'enrolled' => $enrolledCount,
                'skipped' => $skippedCount,
            ]);

            $message = "{$enrolledCount} karyawan berhasil didaftarkan.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} dilewati (sudah terdaftar atau slot penuh).";
            }

            return redirect()
                ->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Bulk enrollment error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Approve an enrollment
     */
    public function approve(string $id)
    {
        try {
            $enrollment = TrainingEnrollment::findOrFail($id);

            if ($enrollment->status !== 'pending') {
                return redirect()
                    ->back()
                    ->with('error', 'Hanya pendaftaran pending yang dapat disetujui.');
            }

            $enrollment->approve(Auth::id());

            Log::info('Training enrollment approved', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Pendaftaran berhasil disetujui.');

        } catch (\Exception $e) {
            Log::error('Training enrollment approval error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an enrollment
     */
    public function cancel(string $id)
    {
        try {
            $enrollment = TrainingEnrollment::findOrFail($id);

            if (in_array($enrollment->status, ['completed', 'cancelled'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Pendaftaran tidak dapat dibatalkan.');
            }

            $enrollment->cancel();

            Log::info('Training enrollment cancelled', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Pendaftaran berhasil dibatalkan.');

        } catch (\Exception $e) {
            Log::error('Training enrollment cancel error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Start training for an enrollment
     */
    public function start(string $id)
    {
        try {
            $enrollment = TrainingEnrollment::findOrFail($id);

            if (!in_array($enrollment->status, ['approved', 'enrolled'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Training tidak dapat dimulai.');
            }

            $enrollment->startProgram();

            Log::info('Training started for enrollment', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Training dimulai untuk peserta.');

        } catch (\Exception $e) {
            Log::error('Training start error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mark enrollment as complete
     */
    public function complete(Request $request, string $id)
    {
        $validated = $request->validate([
            'final_score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:2000',
        ]);

        try {
            $enrollment = TrainingEnrollment::findOrFail($id);

            if ($enrollment->status !== 'in_progress') {
                return redirect()
                    ->back()
                    ->with('error', 'Hanya training yang sedang berjalan yang dapat diselesaikan.');
            }

            $enrollment->complete($validated['final_score']);

            if (isset($validated['feedback'])) {
                $enrollment->update(['feedback' => $validated['feedback']]);
            }

            Log::info('Training completed for enrollment', ['id' => $id, 'score' => $validated['final_score']]);

            return redirect()
                ->back()
                ->with('success', 'Training berhasil diselesaikan.');

        } catch (\Exception $e) {
            Log::error('Training completion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Issue certificate for completed training
     */
    public function issueCertificate(string $id)
    {
        try {
            $enrollment = TrainingEnrollment::findOrFail($id);

            if ($enrollment->status !== 'completed') {
                return redirect()
                    ->back()
                    ->with('error', 'Sertifikat hanya dapat diterbitkan untuk training yang sudah selesai.');
            }

            if ($enrollment->certificate_issued) {
                return redirect()
                    ->back()
                    ->with('error', 'Sertifikat sudah diterbitkan.');
            }

            // Generate certificate number
            $certNumber = 'CERT-' . date('Ymd') . '-' . str_pad($enrollment->id, 5, '0', STR_PAD_LEFT);

            $enrollment->issueCertificate($certNumber);

            Log::info('Certificate issued', ['enrollment_id' => $id, 'cert_number' => $certNumber]);

            return redirect()
                ->back()
                ->with('success', "Sertifikat berhasil diterbitkan: {$certNumber}");

        } catch (\Exception $e) {
            Log::error('Certificate issue error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
