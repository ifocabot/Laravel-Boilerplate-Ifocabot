<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\HumanResource\EmployeeFamilyController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AccessControlDashboardController;
use App\Http\Controllers\MasterData\General\DepartmentController;
use App\Http\Controllers\MasterData\General\LocationController;
use App\Http\Controllers\HumanResource\LevelController;
use App\Http\Controllers\HumanResource\PositionController;
use App\Http\Controllers\HumanResource\EmployeeController;
use App\Http\Controllers\HumanResource\EmployeeContractController;
use App\Http\Controllers\HumanResource\EmployeeCareerController;
use App\Http\Controllers\HumanResource\Payroll\PayrollPeriodController;
use App\Http\Controllers\HumanResource\Payroll\PayrollComponentController;
use App\Http\Controllers\HumanResource\Payroll\EmployeePayrollComponentController;
use App\Http\Controllers\HumanResource\Payroll\PayrollSlipController;
use App\Http\Controllers\HumanResource\Payroll\EmployeeSalaryManagementController;
use App\Http\Controllers\HumanResource\Attendance\ShiftController;
use App\Http\Controllers\HumanResource\Attendance\EmployeeScheduleController;

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

Route::middleware(['auth'])->prefix('master-data/hris')->name('master-data.hris.')->group(function () {
    Route::get('levels', [LevelController::class, 'index'])->name('levels.index');
    Route::post('levels', [LevelController::class, 'store'])->name('levels.store');
    Route::put('levels/{level}', [LevelController::class, 'update'])->name('levels.update');
    Route::delete('levels/{level}', [LevelController::class, 'destroy'])->name('levels.destroy');
    Route::get('levels/{id}/salary-range', [LevelController::class, 'getSalaryRange'])
        ->name('levels.salary-range');
});

Route::middleware(['auth'])->prefix('master-data/hris')->name('master-data.hris.')->group(function () {
    Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
    Route::post('positions', [PositionController::class, 'store'])->name('positions.store');
    Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update');
    Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
    Route::get('positions/department/{departmentId}', [PositionController::class, 'getByDepartment'])
        ->name('positions.by-department');
});

Route::middleware(['auth'])->prefix('hris')->name('hris.')->group(function () {
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Additional actions
    Route::post('employees/{id}/resign', [EmployeeController::class, 'resign'])->name('employees.resign');
    Route::post('employees/{id}/terminate', [EmployeeController::class, 'terminate'])->name('employees.terminate');
    Route::post('employees/{id}/reactivate', [EmployeeController::class, 'reactivate'])->name('employees.reactivate');

    // API endpoints
    Route::get('employees/api/positions-by-department/{departmentId}', [EmployeeController::class, 'getPositionsByDepartment'])
        ->name('employees.positions-by-department');
    Route::get('employees/api/generate-nik', [EmployeeController::class, 'generateNik'])
        ->name('employees.generate-nik');

    // Export
    Route::get('employees/export/all', [EmployeeController::class, 'export'])->name('employees.export');
});

Route::middleware(['auth'])->prefix('hris/employees/{employee_id}/families')->name('hris.employees.families.')->group(function () {
    Route::get('/', [EmployeeFamilyController::class, 'index'])->name('index');
    Route::post('/store', [EmployeeFamilyController::class, 'store'])->name('store');
    Route::put('/update/{id}', [EmployeeFamilyController::class, 'update'])->name('update');
    Route::delete('/destroy/{id}', [EmployeeFamilyController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth'])->prefix('hris/employees/{employee_id}/contracts')->name('hris.employees.contracts.')->group(function () {
    Route::get('/', [EmployeeContractController::class, 'index'])->name('index');
    Route::post('/', [EmployeeContractController::class, 'store'])->name('store');
    Route::put('/{id}/update', [EmployeeContractController::class, 'update'])->name('update');
    Route::delete('/{id}/destroy', [EmployeeContractController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/deactivate', [EmployeeContractController::class, 'deactivate'])->name('deactivate');
    Route::get('/{id}/download', [EmployeeContractController::class, 'download'])->name('download');
    Route::post('/{id}/renew', [EmployeeContractController::class, 'renew'])->name('renew');
});

Route::middleware(['auth'])->prefix('hris')->name('hris.')->group(function () {
    Route::prefix('employees/{employee_id}/careers')->name('employees.careers.')->group(function () {
        Route::get('/', [EmployeeCareerController::class, 'index'])->name('index');
        Route::post('/', [EmployeeCareerController::class, 'store'])->name('store');
        Route::put('/{id}/update', [EmployeeCareerController::class, 'update'])->name('update');
        Route::delete('/{id}/destroy', [EmployeeCareerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/deactivate', [EmployeeCareerController::class, 'deactivate'])->name('deactivate');
    });
});



Route::middleware(['auth'])->prefix('hris')->name('hris.')->group(function () {

    // ... existing routes ...

    // ============================================
    // PAYROLL ROUTES
    // ============================================

    Route::prefix('payroll')->name('payroll.')->group(function () {

        // Payroll Periods
        Route::prefix('periods')->name('periods.')->group(function () {
            Route::get('/', [PayrollPeriodController::class, 'index'])->name('index');
            Route::get('/create', [PayrollPeriodController::class, 'create'])->name('create');
            Route::post('/', [PayrollPeriodController::class, 'store'])->name('store');
            Route::get('/{id}', [PayrollPeriodController::class, 'show'])->name('show');
            Route::delete('/{id}', [PayrollPeriodController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/generate-slips', [PayrollPeriodController::class, 'generateSlips'])->name('generate-slips');
            Route::post('/{id}/approve', [PayrollPeriodController::class, 'approve'])->name('approve');
            Route::post('/{id}/mark-as-paid', [PayrollPeriodController::class, 'markAsPaid'])->name('mark-as-paid');
        });

        // Payroll Components (Master)
        Route::prefix('components')->name('components.')->group(function () {
            Route::get('/', [PayrollComponentController::class, 'index'])->name('index');
            Route::post('/', [PayrollComponentController::class, 'store'])->name('store');
            Route::put('/{id}', [PayrollComponentController::class, 'update'])->name('update');
            Route::delete('/{id}', [PayrollComponentController::class, 'destroy'])->name('destroy');
        });

        // Employee Payroll Components (OLD - Keep for backward compatibility)
        Route::prefix('employees/{employee_id}/components')->name('employee-components.')->group(function () {
            Route::get('/', [EmployeePayrollComponentController::class, 'index'])->name('index');
            Route::post('/', [EmployeePayrollComponentController::class, 'store'])->name('store');
            Route::put('/{id}', [EmployeePayrollComponentController::class, 'update'])->name('update');
            Route::delete('/{id}', [EmployeePayrollComponentController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/deactivate', [EmployeePayrollComponentController::class, 'deactivate'])->name('deactivate');
        });

        // NEW: Employee Salary Management (Centralized)
        Route::prefix('employee-salaries')->name('employee-salaries.')->group(function () {
            Route::get('/', [EmployeeSalaryManagementController::class, 'index'])->name('index');
            Route::get('/{employee_id}', [EmployeeSalaryManagementController::class, 'show'])->name('show');
            Route::post('/{employee_id}/assign', [EmployeeSalaryManagementController::class, 'assign'])->name('assign');
            Route::put('/{employee_id}/components/{component_id}', [EmployeeSalaryManagementController::class, 'updateComponent'])->name('update-component');
            Route::delete('/{employee_id}/components/{component_id}', [EmployeeSalaryManagementController::class, 'destroyComponent'])->name('destroy-component');
            Route::post('/{employee_id}/components/{component_id}/deactivate', [EmployeeSalaryManagementController::class, 'deactivate'])->name('deactivate-component');

            // Bulk Actions
            Route::post('/bulk-assign', [EmployeeSalaryManagementController::class, 'bulkAssign'])->name('bulk-assign');
        });

        // Payroll Slips (Individual)
        Route::prefix('slips')->name('slips.')->group(function () {
            Route::get('/{id}', [PayrollSlipController::class, 'show'])->name('show');
            Route::put('/{id}', [PayrollSlipController::class, 'update'])->name('update');
            Route::delete('/{id}', [PayrollSlipController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/mark-as-paid', [PayrollSlipController::class, 'markAsPaid'])->name('mark-as-paid');
            Route::get('/{id}/download-pdf', [PayrollSlipController::class, 'downloadPdf'])->name('download-pdf');
            Route::post('/{id}/send-email', [PayrollSlipController::class, 'sendEmail'])->name('send-email');
        });

        // Shifts Management
    });

    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::prefix('shifts')->name('shifts.')->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->name('index');
            Route::get('/create', [ShiftController::class, 'create'])->name('create');
            Route::post('/', [ShiftController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [ShiftController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ShiftController::class, 'update'])->name('update');
            Route::delete('/{id}', [ShiftController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [EmployeeScheduleController::class, 'index'])->name('index');
            Route::get('/{id}', [EmployeeScheduleController::class, 'getSchedule'])->name('get'); // NEW
            Route::post('/', [EmployeeScheduleController::class, 'store'])->name('store');
            Route::post('/generate-bulk', [EmployeeScheduleController::class, 'generateBulk'])->name('generate-bulk');
            Route::post('/swap-shifts', [EmployeeScheduleController::class, 'swapShifts'])->name('swap-shifts');
            Route::post('/mark-holiday', [EmployeeScheduleController::class, 'markHoliday'])->name('mark-holiday');
            Route::delete('/{id}', [EmployeeScheduleController::class, 'destroy'])->name('destroy');
        });
    });
});
require __DIR__ . '/auth.php';

