<?php

namespace App\Http\Controllers\ESS;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFamily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ESSProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::with([
            'currentCareer.position',
            'currentCareer.department',
            'currentCareer.level',
            'currentCareer.branch',
            'families',
            'contracts'
        ])->find($user->employee_id);

        if (!$employee) {
            return redirect()->route('ess.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        return view('admin.ess.profile.index', compact('employee'));
    }

    public function edit()
    {
        $user = Auth::user();
        $employee = Employee::with('families')->find($user->employee_id);

        if (!$employee) {
            return redirect()->route('ess.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        return view('admin.ess.profile.edit', compact('employee'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::find($user->employee_id);

        if (!$employee) {
            return redirect()->route('ess.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:100',
            'current_address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:50',
        ]);

        $employee->update($validated);

        return redirect()->route('ess.profile.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $employee = Employee::find($user->employee_id);

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Delete old photo
        if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
            Storage::disk('public')->delete($employee->photo);
        }

        // Store new photo
        $path = $request->file('photo')->store('employees/photos', 'public');
        $employee->update(['photo' => $path]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
