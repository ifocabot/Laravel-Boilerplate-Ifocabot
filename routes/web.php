<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AccessControlDashboardController;
use App\Http\Controllers\MasterData\General\DepartmentController;
use App\Http\Controllers\MasterData\General\LocationController;

Route::get('/', function () {
    return ('hello');
});

Route::get('/dashboard', function () {
    return view('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->prefix('access-control')->name('access-control.')->group(function () {
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
});

Route::middleware('auth')->prefix('access-control')->name('access-control.')->group(function () {
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
});

Route::middleware('auth')->prefix('access-control')->name('access-control.')->group(function () {
    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{user}/assign-role', [UserManagementController::class, 'assignRole'])
        ->name('users.assign-role');
});

Route::middleware('auth')->prefix('access-control')->name('access-control.')->group(function () {
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

Route::middleware('auth')->prefix('access-control')->name('access-control.')->group(function () {
    Route::get('dashboard', [AccessControlDashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth'])->prefix('master-data/general')->name('master-data.general.')->group(function () {
    Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
});

Route::middleware(['auth'])->prefix('master-data/general')->name('master-data.general.')->group(function () {
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
    Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
    Route::post('locations/check-geofence', [LocationController::class, 'checkGeofence'])
        ->name('locations.check-geofence');
});

require __DIR__ . '/auth.php';
