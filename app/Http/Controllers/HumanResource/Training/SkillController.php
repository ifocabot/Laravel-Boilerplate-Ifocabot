<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkillController extends Controller
{
    /**
     * Display a listing of skills
     */
    public function index(Request $request)
    {
        $query = Skill::with('category');

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('category')) {
            $query->where('skill_category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $skills = $query->orderBy('name')->paginate(20);
        $categories = SkillCategory::active()->orderBy('name')->get();

        // Stats
        $totalSkills = Skill::count();
        $activeSkills = Skill::active()->count();

        return view('admin.hris.training.skills.index', compact(
            'skills',
            'categories',
            'totalSkills',
            'activeSkills'
        ));
    }

    /**
     * Show the form for creating a new skill
     */
    public function create()
    {
        $categories = SkillCategory::active()->orderBy('name')->get();

        return view('admin.hris.training.skills.create', compact('categories'));
    }

    /**
     * Store a newly created skill
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_category_id' => 'required|exists:skill_categories,id',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:skills,code',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            Skill::create([
                'skill_category_id' => $validated['skill_category_id'],
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Skill created', ['code' => $validated['code']]);

            return redirect()
                ->route('hris.training.skills.index')
                ->with('success', 'Skill berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Skill creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified skill
     */
    public function show(Skill $skill)
    {
        $skill->load(['category', 'employeeSkills.employee', 'assessments']);

        return view('admin.hris.training.skills.show', compact('skill'));
    }

    /**
     * Show the form for editing a skill
     */
    public function edit(Skill $skill)
    {
        $categories = SkillCategory::active()->orderBy('name')->get();

        return view('admin.hris.training.skills.edit', compact('skill', 'categories'));
    }

    /**
     * Update the specified skill
     */
    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'skill_category_id' => 'required|exists:skill_categories,id',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:skills,code,' . $skill->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $skill->update([
                'skill_category_id' => $validated['skill_category_id'],
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Skill updated', ['id' => $skill->id]);

            return redirect()
                ->route('hris.training.skills.index')
                ->with('success', 'Skill berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Skill update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified skill
     */
    public function destroy(Skill $skill)
    {
        try {
            $name = $skill->name;

            if ($skill->employeeSkills()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak dapat menghapus skill yang sudah dimiliki karyawan.');
            }

            $skill->delete();

            Log::info('Skill deleted', ['id' => $skill->id, 'name' => $name]);

            return redirect()
                ->route('hris.training.skills.index')
                ->with('success', "Skill \"{$name}\" berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Skill deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get proficiency level descriptions (API)
     */
    public function getProficiencyLevels()
    {
        return response()->json([
            'levels' => Skill::PROFICIENCY_LABELS,
            'descriptions' => Skill::PROFICIENCY_DESCRIPTIONS,
        ]);
    }
}
