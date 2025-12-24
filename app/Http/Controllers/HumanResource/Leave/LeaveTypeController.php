<?php

namespace App\Http\Controllers\HumanResource\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of leave types
     */
    public function index(Request $request)
    {
        $query = LeaveType::query();

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $leaveTypes = $query->orderBy('name')->paginate(20);

        // Stats
        $totalTypes = LeaveType::count();
        $activeTypes = LeaveType::active()->count();

        return view('admin.hris.leave.types.index', compact(
            'leaveTypes',
            'totalTypes',
            'activeTypes'
        ));
    }

    /**
     * Show the form for creating a new leave type
     */
    public function create()
    {
        return view('admin.hris.leave.types.create');
    }

    /**
     * Store a newly created leave type
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:leave_types,code',
            'default_quota' => 'required|integer|min:0|max:365',
            'max_consecutive_days' => 'nullable|integer|min:1|max:365',
            'requires_attachment' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            LeaveType::create([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'default_quota' => $validated['default_quota'],
                'max_consecutive_days' => $validated['max_consecutive_days'] ?? null,
                'requires_attachment' => $request->has('requires_attachment'),
                'is_paid' => $request->has('is_paid') || !$request->has('is_paid_submitted'),
                'is_active' => true,
                'description' => $validated['description'] ?? null,
            ]);

            Log::info('Leave type created', ['code' => $validated['code']]);

            return redirect()
                ->route('hris.leave.types.index')
                ->with('success', 'Tipe cuti berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Leave type creation error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a leave type
     */
    public function edit(string $id)
    {
        $leaveType = LeaveType::findOrFail($id);

        return view('admin.hris.leave.types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type
     */
    public function update(Request $request, string $id)
    {
        $leaveType = LeaveType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:10|unique:leave_types,code,' . $id,
            'default_quota' => 'required|integer|min:0|max:365',
            'max_consecutive_days' => 'nullable|integer|min:1|max:365',
            'requires_attachment' => 'nullable|boolean',
            'is_paid' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        try {
            $leaveType->update([
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'default_quota' => $validated['default_quota'],
                'max_consecutive_days' => $validated['max_consecutive_days'] ?? null,
                'requires_attachment' => $request->has('requires_attachment'),
                'is_paid' => $request->has('is_paid'),
                'is_active' => $request->has('is_active'),
                'description' => $validated['description'] ?? null,
            ]);

            Log::info('Leave type updated', ['id' => $id]);

            return redirect()
                ->route('hris.leave.types.index')
                ->with('success', 'Tipe cuti berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Leave type update error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified leave type
     */
    public function destroy(string $id)
    {
        try {
            $leaveType = LeaveType::findOrFail($id);
            $name = $leaveType->name;

            // Check if has active leave requests
            if ($leaveType->leaveRequests()->pending()->exists()) {
                return redirect()
                    ->back()
                    ->with('error', 'Tidak dapat menghapus tipe cuti yang memiliki pengajuan aktif.');
            }

            $leaveType->delete();

            Log::info('Leave type deleted', ['id' => $id, 'name' => $name]);

            return redirect()
                ->route('hris.leave.types.index')
                ->with('success', "Tipe cuti \"{$name}\" berhasil dihapus.");

        } catch (\Exception $e) {
            Log::error('Leave type deletion error', ['error' => $e->getMessage()]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
