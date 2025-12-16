<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::count();
        $permissions = Permission::all();
        $roles = Role::with('permissions')->paginate(10);

        return view('admin.access-control.roles.index', compact('roles', 'permissions', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        try {
            // Get validated data
            $validated = $request->validated();

            // Create Role
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web', // Explicitly set guard name
            ]);

            // Sync Permissions using IDs
            if (!empty($validated['permissions'])) {
                // Get Permission models by IDs
                $permissions = Permission::findMany($validated['permissions']);
                $role->syncPermissions($permissions);
            }

            return redirect()
                ->route('access-control.roles.index')
                ->with('success', 'Peran berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Role Store Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat peran: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            // Update role name
            $role->update([
                'name' => $request->name,
            ]);

            // Sync permissions using Permission models, not IDs directly
            $permissionIds = $request->input('permissions', []);

            if (!empty($permissionIds)) {
                // Get Permission models by IDs
                $permissions = Permission::findMany($permissionIds);
                $role->syncPermissions($permissions);
            } else {
                // Clear all permissions if none selected
                $role->syncPermissions([]);
            }

            return redirect()
                ->route('access-control.roles.index')
                ->with('success', 'Peran berhasil diperbarui.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Role Not Found', [
                'role_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Peran tidak ditemukan.');

        } catch (\Exception $e) {
            Log::error('Role Update Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role_id' => $id,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui peran: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Check if role is assigned to any users
            if ($role->users()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Peran tidak dapat dihapus karena masih digunakan oleh user.');
            }

            // Delete role (permissions will be auto-detached by Spatie)
            $role->delete();

            return redirect()
                ->back()
                ->with('success', 'Peran berhasil dihapus.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->back()
                ->with('error', 'Peran tidak ditemukan.');

        } catch (\Exception $e) {
            Log::error('Role Delete Error', [
                'error' => $e->getMessage(),
                'role_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menghapus peran: ' . $e->getMessage());
        }
    }
}