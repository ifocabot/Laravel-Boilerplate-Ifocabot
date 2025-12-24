<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CertificationController extends Controller
{
    /**
     * Display a listing of certifications
     */
    public function index(Request $request)
    {
        $query = Certification::with('skill')
            ->withCount('activeEmployeeCertifications as holder_count');

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('issuing_organization', 'like', "%{$search}%");
            });
        }

        $certifications = $query->orderBy('name')->paginate(20);

        // Stats
        $totalCertifications = Certification::count();
        $activeCertifications = Certification::active()->count();

        return view('admin.hris.training.certifications.index', compact(
            'certifications',
            'totalCertifications',
            'activeCertifications'
        ));
    }

    /**
     * Show the form for creating a new certification
     */
    public function create()
    {
        $skills = Skill::active()->orderBy('name')->get();

        return view('admin.hris.training.certifications.create', compact('skills'));
    }

    /**
     * Store a newly created certification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:50|unique:certifications,code',
            'issuing_organization' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'validity_months' => 'nullable|integer|min:1|max:120',
            'level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'skill_id' => 'nullable|exists:skills,id',
        ]);

        try {
            Certification::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'issuing_organization' => $validated['issuing_organization'],
                'description' => $validated['description'] ?? null,
                'validity_months' => $validated['validity_months'] ?? null,
                'level' => $validated['level'] ?? null,
                'skill_id' => $validated['skill_id'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Certification created', ['code' => $validated['code']]);

            return redirect()
                ->route('hris.training.certifications.index')
                ->with('success', 'Sertifikasi berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Certification creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified certification
     */
    public function show(Certification $certification)
    {
        $certification->load(['skill', 'employeeCertifications.employee']);

        return view('admin.hris.training.certifications.show', compact('certification'));
    }

    /**
     * Show the form for editing a certification
     */
    public function edit(Certification $certification)
    {
        $skills = Skill::active()->orderBy('name')->get();

        return view('admin.hris.training.certifications.edit', compact('certification', 'skills'));
    }

    /**
     * Update the specified certification
     */
    public function update(Request $request, Certification $certification)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:50|unique:certifications,code,' . $certification->id,
            'issuing_organization' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'validity_months' => 'nullable|integer|min:1|max:120',
            'level' => 'nullable|in:beginner,intermediate,advanced,expert',
            'skill_id' => 'nullable|exists:skills,id',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $certification->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'issuing_organization' => $validated['issuing_organization'],
                'description' => $validated['description'] ?? null,
                'validity_months' => $validated['validity_months'] ?? null,
                'level' => $validated['level'] ?? null,
                'skill_id' => $validated['skill_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Certification updated', ['id' => $certification->id]);

            return redirect()
                ->route('hris.training.certifications.index')
                ->with('success', 'Sertifikasi berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Certification update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified certification
     */
    public function destroy(Certification $certification)
    {
        try {
            $name = $certification->name;

            if ($certification->employeeCertifications()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak dapat menghapus sertifikasi yang sudah dimiliki karyawan.');
            }

            $certification->delete();

            Log::info('Certification deleted', ['id' => $certification->id]);

            return redirect()
                ->route('hris.training.certifications.index')
                ->with('success', "Sertifikasi \"{$name}\" berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Certification deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
