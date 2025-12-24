<?php

namespace App\Http\Controllers\HumanResource\Training;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrainerController extends Controller
{
    /**
     * Display a listing of trainers
     */
    public function index(Request $request)
    {
        $query = Trainer::with('employee');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('organization', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        $trainers = $query->orderBy('type')->orderBy('name')->paginate(20);

        // Stats
        $totalTrainers = Trainer::count();
        $internalTrainers = Trainer::internal()->active()->count();
        $externalTrainers = Trainer::external()->active()->count();

        return view('admin.hris.training.trainers.index', compact(
            'trainers',
            'totalTrainers',
            'internalTrainers',
            'externalTrainers'
        ));
    }

    /**
     * Show the form for creating a new trainer
     */
    public function create()
    {
        $employees = Employee::active()
            ->with('currentCareer.position')
            ->orderBy('full_name')
            ->get();

        return view('admin.hris.training.trainers.create', compact('employees'));
    }

    /**
     * Store a newly created trainer
     */
    public function store(Request $request)
    {
        $rules = [
            'type' => 'required|in:internal,external',
            'expertise' => 'nullable|string|max:1000',
            'bio' => 'nullable|string|max:2000',
        ];

        if ($request->type === 'internal') {
            $rules['employee_id'] = 'required|exists:employees,id';
        } else {
            $rules['name'] = 'required|string|max:200';
            $rules['email'] = 'nullable|email|max:100';
            $rules['phone'] = 'nullable|string|max:20';
            $rules['organization'] = 'nullable|string|max:200';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        try {
            $data = [
                'type' => $validated['type'],
                'expertise' => $validated['expertise'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'is_active' => true,
            ];

            if ($validated['type'] === 'internal') {
                $data['employee_id'] = $validated['employee_id'];
            } else {
                $data['name'] = $validated['name'];
                $data['email'] = $validated['email'] ?? null;
                $data['phone'] = $validated['phone'] ?? null;
                $data['organization'] = $validated['organization'] ?? null;
                $data['hourly_rate'] = $validated['hourly_rate'] ?? null;
            }

            Trainer::create($data);

            Log::info('Trainer created', ['type' => $validated['type']]);

            return redirect()
                ->route('hris.training.trainers.index')
                ->with('success', 'Trainer berhasil ditambahkan.');

        } catch (\Exception $e) {
            Log::error('Trainer creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified trainer
     */
    public function show(Trainer $trainer)
    {
        $trainer->load(['employee', 'trainingPrograms']);

        return view('admin.hris.training.trainers.show', compact('trainer'));
    }

    /**
     * Show the form for editing a trainer
     */
    public function edit(Trainer $trainer)
    {
        $employees = Employee::active()
            ->with('currentCareer.position')
            ->orderBy('full_name')
            ->get();

        return view('admin.hris.training.trainers.edit', compact('trainer', 'employees'));
    }

    /**
     * Update the specified trainer
     */
    public function update(Request $request, Trainer $trainer)
    {
        $rules = [
            'type' => 'required|in:internal,external',
            'expertise' => 'nullable|string|max:1000',
            'bio' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
        ];

        if ($request->type === 'internal') {
            $rules['employee_id'] = 'required|exists:employees,id';
        } else {
            $rules['name'] = 'required|string|max:200';
            $rules['email'] = 'nullable|email|max:100';
            $rules['phone'] = 'nullable|string|max:20';
            $rules['organization'] = 'nullable|string|max:200';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
        }

        $validated = $request->validate($rules);

        try {
            $data = [
                'type' => $validated['type'],
                'expertise' => $validated['expertise'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'is_active' => $request->has('is_active'),
            ];

            if ($validated['type'] === 'internal') {
                $data['employee_id'] = $validated['employee_id'];
                $data['name'] = null;
                $data['email'] = null;
                $data['phone'] = null;
                $data['organization'] = null;
                $data['hourly_rate'] = null;
            } else {
                $data['employee_id'] = null;
                $data['name'] = $validated['name'];
                $data['email'] = $validated['email'] ?? null;
                $data['phone'] = $validated['phone'] ?? null;
                $data['organization'] = $validated['organization'] ?? null;
                $data['hourly_rate'] = $validated['hourly_rate'] ?? null;
            }

            $trainer->update($data);

            Log::info('Trainer updated', ['id' => $trainer->id]);

            return redirect()
                ->route('hris.training.trainers.index')
                ->with('success', 'Trainer berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Trainer update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified trainer
     */
    public function destroy(Trainer $trainer)
    {
        try {
            $name = $trainer->display_name;

            if ($trainer->trainingPrograms()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak dapat menghapus trainer yang sudah terkait dengan program training.');
            }

            $trainer->delete();

            Log::info('Trainer deleted', ['id' => $trainer->id]);

            return redirect()
                ->route('hris.training.trainers.index')
                ->with('success', "Trainer \"{$name}\" berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Trainer deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
