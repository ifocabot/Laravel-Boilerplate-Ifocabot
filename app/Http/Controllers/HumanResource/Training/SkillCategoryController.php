<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\SkillCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SkillCategoryController extends Controller
{
    /**
     * Display a listing of skill categories
     */
    public function index(Request $request)
    {
        $query = SkillCategory::withCount('skills');

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('name')->paginate(20);

        // Stats
        $totalCategories = SkillCategory::count();
        $activeCategories = SkillCategory::active()->count();

        return view('admin.hris.training.skill-categories.index', compact(
            'categories',
            'totalCategories',
            'activeCategories'
        ));
    }

    /**
     * Show the form for creating a new skill category
     */
    public function create()
    {
        return view('admin.hris.training.skill-categories.create');
    }

    /**
     * Store a newly created skill category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:skill_categories,code',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            SkillCategory::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Skill category created', ['code' => $validated['code']]);

            return redirect()
                ->route('hris.training.skill-categories.index')
                ->with('success', 'Kategori skill berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Skill category creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified skill category
     */
    public function show(SkillCategory $skillCategory)
    {
        $skillCategory->load('skills');

        return view('admin.hris.training.skill-categories.show', compact('skillCategory'));
    }

    /**
     * Show the form for editing a skill category
     */
    public function edit(SkillCategory $skillCategory)
    {
        return view('admin.hris.training.skill-categories.edit', compact('skillCategory'));
    }

    /**
     * Update the specified skill category
     */
    public function update(Request $request, SkillCategory $skillCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:skill_categories,code,' . $skillCategory->id,
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $skillCategory->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info('Skill category updated', ['id' => $skillCategory->id]);

            return redirect()
                ->route('hris.training.skill-categories.index')
                ->with('success', 'Kategori skill berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Skill category update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified skill category
     */
    public function destroy(SkillCategory $skillCategory)
    {
        try {
            $name = $skillCategory->name;

            if ($skillCategory->skills()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak dapat menghapus kategori yang memiliki skill.');
            }

            $skillCategory->delete();

            Log::info('Skill category deleted', ['id' => $skillCategory->id, 'name' => $name]);

            return redirect()
                ->route('hris.training.skill-categories.index')
                ->with('success', "Kategori \"{$name}\" berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Skill category deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
