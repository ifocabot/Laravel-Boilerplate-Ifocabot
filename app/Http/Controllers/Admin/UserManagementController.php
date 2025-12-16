<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Role;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        $roles = Role::with('permissions')->get();

        // Calculate statistics
        $activeUsers = User::whereHas('roles')->count();
        $usersWithRoles = User::has('roles')->count();
        $usersWithoutRoles = User::doesntHave('roles')->count();

        return view('admin.access-control.users.index', compact(
            'users',
            'roles',
            'activeUsers',
            'usersWithRoles',
            'usersWithoutRoles'
        ));
    }

    public function assignRole(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'exists:roles,id',
            ], [
                'roles.required' => 'Pilih minimal satu role.',
                'roles.*.exists' => 'Role yang dipilih tidak valid.',
            ]);

            // Get Role models by IDs
            $roles = Role::findMany($validated['roles']);
            $user->syncRoles($roles);

            return redirect()
                ->route('access-control.users.index')
                ->with('success', 'Role berhasil ditetapkan ke pengguna.');

        } catch (\Exception $e) {
            Log::error('Assign Role Error', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menetapkan role.');
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting own account
            if ($user->id === auth()->id()) {
                return redirect()
                    ->back()
                    ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            }

            $user->delete();

            return redirect()
                ->back()
                ->with('success', 'Pengguna berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('User Delete Error', [
                'error' => $e->getMessage(),
                'user_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menghapus pengguna.');
        }
    }
}
