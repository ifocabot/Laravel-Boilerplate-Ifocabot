<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\TrainingProgram;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrainingProgramController extends Controller
{
    /**
     * Display a listing of training programs
     */
    public function index(Request $request)
    {
        $query = TrainingProgram::with(['trainer', 'creator'])
            ->withCount('enrollments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('start_date', 'desc')->paginate(15);

        // Stats
        $totalPrograms = TrainingProgram::count();
        $openPrograms = TrainingProgram::open()->count();
        $ongoingPrograms = TrainingProgram::ongoing()->count();
        $completedPrograms = TrainingProgram::completed()->count();

        return view('admin.hris.training.programs.index', compact(
            'programs',
            'totalPrograms',
            'openPrograms',
            'ongoingPrograms',
            'completedPrograms'
        ));
    }

    /**
     * Show the form for creating a new training program
     */
    public function create()
    {
        $trainers = Trainer::active()->with('employee')->get();

        return view('admin.hris.training.programs.create', compact('trainers'));
    }

    /**
     * Store a newly created training program
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:20|unique:training_programs,code',
            'description' => 'nullable|string',
            'type' => 'required|in:internal,external,online,hybrid',
            'provider' => 'nullable|string|max:200',
            'trainer_id' => 'nullable|exists:trainers,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:200',
            'max_participants' => 'nullable|integer|min:1',
            'cost_per_person' => 'nullable|numeric|min:0',
            'total_budget' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|integer|min:1',
            'objectives' => 'nullable|string',
            'prerequisites' => 'nullable|string',
        ]);

        try {
            TrainingProgram::create([
                ...$validated,
                'code' => strtoupper($validated['code']),
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            Log::info('Training program created', ['code' => $validated['code']]);

            return redirect()
                ->route('hris.training.programs.index')
                ->with('success', 'Program training berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Training program creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified training program
     */
    public function show(string $id)
    {
        $program = TrainingProgram::with([
            'trainer.employee',
            'creator',
            'courses',
            'enrollments.employee',
        ])->findOrFail($id);

        return view('admin.hris.training.programs.show', compact('program'));
    }

    /**
     * Show the form for editing a training program
     */
    public function edit(string $id)
    {
        $program = TrainingProgram::findOrFail($id);
        $trainers = Trainer::active()->with('employee')->get();

        return view('admin.hris.training.programs.edit', compact('program', 'trainers'));
    }

    /**
     * Update the specified training program
     */
    public function update(Request $request, string $id)
    {
        $program = TrainingProgram::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'code' => 'required|string|max:20|unique:training_programs,code,' . $id,
            'description' => 'nullable|string',
            'type' => 'required|in:internal,external,online,hybrid',
            'provider' => 'nullable|string|max:200',
            'trainer_id' => 'nullable|exists:trainers,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:200',
            'max_participants' => 'nullable|integer|min:1',
            'cost_per_person' => 'nullable|numeric|min:0',
            'total_budget' => 'nullable|numeric|min:0',
            'duration_hours' => 'nullable|integer|min:1',
            'objectives' => 'nullable|string',
            'prerequisites' => 'nullable|string',
            'status' => 'nullable|in:draft,open,ongoing,completed,cancelled',
        ]);

        try {
            $program->update([
                ...$validated,
                'code' => strtoupper($validated['code']),
            ]);

            Log::info('Training program updated', ['id' => $id]);

            return redirect()
                ->route('hris.training.programs.index')
                ->with('success', 'Program training berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Training program update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified training program
     */
    public function destroy(string $id)
    {
        try {
            $program = TrainingProgram::findOrFail($id);
            $name = $program->name;

            if ($program->enrollments()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak dapat menghapus program yang sudah memiliki peserta.');
            }

            $program->delete();

            Log::info('Training program deleted', ['id' => $id]);

            return redirect()
                ->route('hris.training.programs.index')
                ->with('success', "Program \"{$name}\" berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Training program deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Publish the training program (open for enrollment)
     */
    public function publish(string $id)
    {
        try {
            $program = TrainingProgram::findOrFail($id);

            if ($program->status !== 'draft') {
                return redirect()
                    ->back()
                    ->with('error', 'Hanya program draft yang dapat dipublish.');
            }

            $program->publish();

            Log::info('Training program published', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Program berhasil dipublish dan dibuka untuk pendaftaran.');

        } catch (\Exception $e) {
            Log::error('Training program publish error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Start the training program
     */
    public function start(string $id)
    {
        try {
            $program = TrainingProgram::findOrFail($id);

            if (!in_array($program->status, ['draft', 'open'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Program tidak dapat dimulai.');
            }

            $program->start();

            Log::info('Training program started', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Program berhasil dimulai.');

        } catch (\Exception $e) {
            Log::error('Training program start error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Complete the training program
     */
    public function complete(string $id)
    {
        try {
            $program = TrainingProgram::findOrFail($id);

            if ($program->status !== 'ongoing') {
                return redirect()
                    ->back()
                    ->with('error', 'Hanya program yang sedang berjalan yang dapat diselesaikan.');
            }

            $program->complete();

            Log::info('Training program completed', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Program berhasil ditandai selesai.');

        } catch (\Exception $e) {
            Log::error('Training program complete error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel the training program
     */
    public function cancel(string $id)
    {
        try {
            $program = TrainingProgram::findOrFail($id);

            if ($program->status === 'completed') {
                return redirect()
                    ->back()
                    ->with('error', 'Program yang sudah selesai tidak dapat dibatalkan.');
            }

            $program->cancel();

            Log::info('Training program cancelled', ['id' => $id]);

            return redirect()
                ->back()
                ->with('success', 'Program berhasil dibatalkan.');

        } catch (\Exception $e) {
            Log::error('Training program cancel error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
