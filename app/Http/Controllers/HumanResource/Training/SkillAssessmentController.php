<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\SkillAssessment;
use App\Models\EmployeeSkill;
use App\Models\Employee;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SkillAssessmentController extends Controller
{
    /**
     * Display a listing of skill assessments
     */
    public function index(Request $request)
    {
        $query = SkillAssessment::with(['employee', 'skill', 'assessor']);

        if ($request->filled('skill_id')) {
            $query->where('skill_id', $request->skill_id);
        }

        if ($request->filled('type')) {
            $query->where('assessment_type', $request->type);
        }

        if ($request->filled('level')) {
            $query->where('proficiency_level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->paginate(20);

        $skills = Skill::active()->orderBy('name')->get();

        // Stats
        $totalAssessments = SkillAssessment::count();
        $recentAssessments = SkillAssessment::recent(1)->count(); // Last month
        $averageLevel = round(SkillAssessment::avg('proficiency_level'), 1);

        return view('admin.hris.training.assessments.index', compact(
            'assessments',
            'skills',
            'totalAssessments',
            'recentAssessments',
            'averageLevel'
        ));
    }

    /**
     * Show the form for creating a new assessment
     */
    public function create(Request $request)
    {
        $employees = Employee::active()
            ->with('currentCareer.position')
            ->orderBy('full_name')
            ->get();

        $skills = Skill::active()
            ->with('category')
            ->orderBy('name')
            ->get();

        $selectedEmployeeId = $request->get('employee_id');
        $selectedSkillId = $request->get('skill_id');

        return view('admin.hris.training.assessments.create', compact(
            'employees',
            'skills',
            'selectedEmployeeId',
            'selectedSkillId'
        ));
    }

    /**
     * Store a newly created assessment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'skill_id' => 'required|exists:skills,id',
            'assessment_date' => 'required|date',
            'proficiency_level' => 'required|integer|min:1|max:5',
            'proficiency_score' => 'nullable|numeric|min:0|max:100',
            'assessment_type' => 'required|in:self,manager,peer,360',
            'evidence' => 'nullable|string|max:2000',
            'strengths' => 'nullable|string|max:1000',
            'areas_for_improvement' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'next_assessment_date' => 'nullable|date|after:assessment_date',
        ]);

        try {
            $assessment = SkillAssessment::create([
                ...$validated,
                'assessor_id' => Auth::id(),
            ]);

            // Update employee skill record
            $assessment->updateEmployeeSkill();

            Log::info('Skill assessment created', [
                'employee_id' => $validated['employee_id'],
                'skill_id' => $validated['skill_id'],
            ]);

            return redirect()
                ->route('hris.training.assessments.index')
                ->with('success', 'Penilaian skill berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Skill assessment creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display employee skill profile
     */
    public function employeeProfile(string $employeeId)
    {
        $employee = Employee::with(['currentCareer.position', 'currentCareer.department'])
            ->findOrFail($employeeId);

        // Get all employee skills with their latest assessment
        $employeeSkills = EmployeeSkill::with(['skill.category'])
            ->where('employee_id', $employeeId)
            ->get();

        // Get assessment history
        $assessmentHistory = SkillAssessment::with(['skill', 'assessor'])
            ->where('employee_id', $employeeId)
            ->orderBy('assessment_date', 'desc')
            ->take(20)
            ->get();

        // Skills by category
        $skillsByCategory = $employeeSkills->groupBy(function ($es) {
            return $es->skill->category->name ?? 'Uncategorized';
        });

        return view('admin.hris.training.assessments.employee-profile', compact(
            'employee',
            'employeeSkills',
            'assessmentHistory',
            'skillsByCategory'
        ));
    }

    /**
     * Show the form for editing an assessment
     */
    public function edit(string $id)
    {
        $assessment = SkillAssessment::with(['employee', 'skill'])->findOrFail($id);
        $employees = Employee::active()->orderBy('full_name')->get();
        $skills = Skill::active()->orderBy('name')->get();

        return view('admin.hris.training.assessments.edit', compact('assessment', 'employees', 'skills'));
    }

    /**
     * Update the specified assessment
     */
    public function update(Request $request, string $id)
    {
        $assessment = SkillAssessment::findOrFail($id);

        $validated = $request->validate([
            'assessment_date' => 'required|date',
            'proficiency_level' => 'required|integer|min:1|max:5',
            'proficiency_score' => 'nullable|numeric|min:0|max:100',
            'assessment_type' => 'required|in:self,manager,peer,360',
            'evidence' => 'nullable|string|max:2000',
            'strengths' => 'nullable|string|max:1000',
            'areas_for_improvement' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'next_assessment_date' => 'nullable|date|after:assessment_date',
        ]);

        try {
            $assessment->update($validated);

            // Update employee skill record
            $assessment->updateEmployeeSkill();

            Log::info('Skill assessment updated', ['id' => $id]);

            return redirect()
                ->route('hris.training.assessments.index')
                ->with('success', 'Penilaian skill berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Skill assessment update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified assessment
     */
    public function destroy(string $id)
    {
        try {
            $assessment = SkillAssessment::findOrFail($id);
            $assessment->delete();

            Log::info('Skill assessment deleted', ['id' => $id]);

            return redirect()
                ->route('hris.training.assessments.index')
                ->with('success', 'Penilaian skill berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Skill assessment deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assessment for multiple employees
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'skill_id' => 'required|exists:skills,id',
            'assessment_date' => 'required|date',
            'assessment_type' => 'required|in:self,manager,peer,360',
            'assessments' => 'required|array|min:1',
            'assessments.*.employee_id' => 'required|exists:employees,id',
            'assessments.*.proficiency_level' => 'required|integer|min:1|max:5',
            'assessments.*.notes' => 'nullable|string|max:500',
        ]);

        try {
            $createdCount = 0;

            foreach ($validated['assessments'] as $item) {
                $assessment = SkillAssessment::create([
                    'employee_id' => $item['employee_id'],
                    'skill_id' => $validated['skill_id'],
                    'assessor_id' => Auth::id(),
                    'assessment_date' => $validated['assessment_date'],
                    'proficiency_level' => $item['proficiency_level'],
                    'assessment_type' => $validated['assessment_type'],
                    'notes' => $item['notes'] ?? null,
                ]);

                $assessment->updateEmployeeSkill();
                $createdCount++;
            }

            Log::info('Bulk skill assessment created', [
                'skill_id' => $validated['skill_id'],
                'count' => $createdCount,
            ]);

            return redirect()
                ->route('hris.training.assessments.index')
                ->with('success', "{$createdCount} penilaian skill berhasil ditambahkan.");

        } catch (\Exception $e) {
            Log::error('Bulk skill assessment error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get skill gap analysis for an employee
     */
    public function skillGap(string $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $skills = EmployeeSkill::with('skill')
            ->where('employee_id', $employeeId)
            ->needsImprovement()
            ->get();

        return response()->json([
            'employee' => $employee->full_name,
            'skills_with_gap' => $skills->map(function ($es) {
                return [
                    'skill' => $es->skill->name,
                    'current_level' => $es->current_level,
                    'current_label' => $es->current_level_label,
                    'target_level' => $es->target_level,
                    'target_label' => $es->target_level_label,
                    'gap' => $es->gap_to_target,
                ];
            }),
        ]);
    }
}
