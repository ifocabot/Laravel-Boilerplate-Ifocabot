<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\TrainingCourse;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TrainingCourseController extends Controller
{
    /**
     * Store a newly created course in a program
     */
    public function store(Request $request, string $programId)
    {
        $program = TrainingProgram::findOrFail($programId);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'duration_hours' => 'required|numeric|min:0.5|max:100',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'is_mandatory' => 'nullable|boolean',
            'learning_outcomes' => 'nullable|string',
            'materials' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip|max:20480',
        ]);

        try {
            // Get next sequence number
            $maxSequence = $program->courses()->max('sequence') ?? 0;

            $materialsPath = null;
            if ($request->hasFile('materials')) {
                $materialsPath = $request->file('materials')->store(
                    'training/courses/' . $programId,
                    'public'
                );
            }

            TrainingCourse::create([
                'training_program_id' => $programId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'duration_hours' => $validated['duration_hours'],
                'sequence' => $maxSequence + 1,
                'materials_path' => $materialsPath,
                'passing_score' => $validated['passing_score'] ?? 70,
                'is_mandatory' => $request->has('is_mandatory'),
                'learning_outcomes' => $validated['learning_outcomes'] ?? null,
            ]);

            Log::info('Training course created', ['program_id' => $programId]);

            return redirect()
                ->back()
                ->with('success', 'Materi kursus berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Training course creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, string $programId, string $id)
    {
        $course = TrainingCourse::where('training_program_id', $programId)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'duration_hours' => 'required|numeric|min:0.5|max:100',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'is_mandatory' => 'nullable|boolean',
            'learning_outcomes' => 'nullable|string',
            'materials' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip|max:20480',
        ]);

        try {
            $materialsPath = $course->materials_path;

            if ($request->hasFile('materials')) {
                // Delete old file
                if ($materialsPath) {
                    Storage::disk('public')->delete($materialsPath);
                }

                $materialsPath = $request->file('materials')->store(
                    'training/courses/' . $programId,
                    'public'
                );
            }

            $course->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'duration_hours' => $validated['duration_hours'],
                'materials_path' => $materialsPath,
                'passing_score' => $validated['passing_score'] ?? 70,
                'is_mandatory' => $request->has('is_mandatory'),
                'learning_outcomes' => $validated['learning_outcomes'] ?? null,
            ]);

            Log::info('Training course updated', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Materi kursus berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Training course update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified course
     */
    public function destroy(string $programId, string $id)
    {
        try {
            $course = TrainingCourse::where('training_program_id', $programId)
                ->findOrFail($id);
            $name = $course->name;

            // Delete materials file
            if ($course->materials_path) {
                Storage::disk('public')->delete($course->materials_path);
            }

            $course->delete();

            // Reorder remaining courses
            TrainingCourse::where('training_program_id', $programId)
                ->orderBy('sequence')
                ->get()
                ->each(function ($c, $index) {
                    $c->update(['sequence' => $index + 1]);
                });

            Log::info('Training course deleted', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', "Materi \"{$name}\" berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Training course deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reorder courses
     */
    public function reorder(Request $request, string $programId)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:training_courses,id',
        ]);

        try {
            foreach ($validated['order'] as $index => $courseId) {
                TrainingCourse::where('id', $courseId)
                    ->where('training_program_id', $programId)
                    ->update(['sequence' => $index + 1]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Training course reorder error', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Download course materials
     */
    public function downloadMaterials(string $programId, string $id)
    {
        $course = TrainingCourse::where('training_program_id', $programId)
            ->findOrFail($id);

        if (!$course->materials_path) {
            abort(404, 'Materi tidak ditemukan.');
        }

        return Storage::disk('public')->download($course->materials_path);
    }
}
