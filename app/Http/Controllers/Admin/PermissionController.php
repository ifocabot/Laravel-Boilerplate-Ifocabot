<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;

use Illuminate\Support\Facades\Log;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('roles')->latest()->paginate(10);

        // Calculate statistics
        $usedPermissions = $permissions->filter(function ($permission) {
            return $permission->roles()->count() > 0;
        })->count();

        $unusedPermissions = $permissions->count() - $usedPermissions;
        $criticalPermissions = 0; // You can define logic for critical permissions

        return view('admin.access-control.permissions.index', compact(
            'permissions',
            'usedPermissions',
            'unusedPermissions',
            'criticalPermissions'
        ));
    }

    public function store(StorePermissionRequest $request)
    {
        try {
            $validated = $request->validated();

            Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            return redirect()
                ->route('access-control.permissions.index')
                ->with('success', 'Izin berhasil dibuat.');

        } catch (\Exception $e) {
            Log::error('Permission Store Error', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat izin.');
        }
    }

    public function update(UpdatePermissionRequest $request, $id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $validated = $request->validated();

            $permission->update([
                'name' => $validated['name'],
            ]);

            return redirect()
                ->route('access-control.permissions.index')
                ->with('success', 'Izin berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Permission Update Error', [
                'error' => $e->getMessage(),
                'permission_id' => $id,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui izin.');
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);

            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Izin tidak dapat dihapus karena masih digunakan oleh peran.');
            }

            $permission->delete();

            return redirect()
                ->back()
                ->with('success', 'Izin berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Permission Delete Error', [
                'error' => $e->getMessage(),
                'permission_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat menghapus izin.');
        }
    }
}