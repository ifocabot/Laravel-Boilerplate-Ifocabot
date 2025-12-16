<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AccessControlDashboardController extends Controller
{
    public function index()
    {
        // Check if last_login_at column exists
        $hasLastLoginColumn = Schema::hasColumn('users', 'last_login_at');

        // Statistics
        $stats = [
            'users_count' => User::count(),
            'roles_count' => Role::count(),
            'permissions_count' => Permission::count(),
            'active_sessions' => $hasLastLoginColumn
                ? User::whereNotNull('last_login_at')
                    ->where('last_login_at', '>=', now()->subHours(24))
                    ->count()
                : User::where('created_at', '>=', now()->subHours(24))->count(), // Fallback
        ];

        // Role Distribution
        $roleDistribution = Role::withCount('users')
            ->get()
            ->map(function ($role) {
                return [
                    'name' => $role->name,
                    'count' => $role->users_count
                ];
            });

        // Recent Users (last 5)
        $recentUsers = User::with('roles')
            ->latest()
            ->take(5)
            ->get();

        // Recent Activities (last 5)
        $recentActivities = Audit::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.access-control.dashboard', compact(
            'stats',
            'roleDistribution',
            'recentUsers',
            'recentActivities'
        ));
    }
}