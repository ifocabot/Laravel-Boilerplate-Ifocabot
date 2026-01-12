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
use App\Http\Controllers\HumanResource\OrganizationController;
use App\Http\Controllers\HumanResource\Payroll\PayrollPeriodController;
use App\Http\Controllers\HumanResource\Payroll\PayrollComponentController;
use App\Http\Controllers\HumanResource\Payroll\EmployeePayrollComponentController;
use App\Http\Controllers\HumanResource\Payroll\PayrollSlipController;
use App\Http\Controllers\HumanResource\Payroll\EmployeeSalaryManagementController;
use App\Http\Controllers\HumanResource\Attendance\ShiftController;
use App\Http\Controllers\HumanResource\Attendance\EmployeeScheduleController;
use App\Http\Controllers\HumanResource\Attendance\AttendanceLogController;
use App\Http\Controllers\HumanResource\Attendance\AttendanceSummaryController;
use App\Http\Controllers\HumanResource\Attendance\OvertimeRequestController;
use App\Http\Controllers\HumanResource\DocumentCategoryController;
use App\Http\Controllers\HumanResource\EmployeeDocumentController;
use App\Http\Controllers\NotificationController;


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

// ============================================
// ACCESS CONTROL
// ============================================
Route::middleware('auth')->prefix('access-control')->name('access-control.')->group(function () {
    Route::get('dashboard', [AccessControlDashboardController::class, 'index'])->name('dashboard');

    // Roles
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Permissions
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // Users
    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{user}/assign-role', [UserManagementController::class, 'assignRole'])->name('users.assign-role');

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Announcements
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class)->names([
        'index' => 'announcements.index',
        'create' => 'announcements.create',
        'store' => 'announcements.store',
        'edit' => 'announcements.edit',
        'update' => 'announcements.update',
        'destroy' => 'announcements.destroy',
    ]);


});

// ============================================
// MASTER DATA
// ============================================
Route::middleware(['auth'])->prefix('master-data')->name('master-data.')->group(function () {

    // General
    Route::prefix('general')->name('general.')->group(function () {
        // Departments
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Locations
        Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
        Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
        Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
        Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
        Route::post('locations/check-geofence', [LocationController::class, 'checkGeofence'])->name('locations.check-geofence');
    });

    // HRIS
    Route::prefix('hris')->name('hris.')->group(function () {
        // Levels
        Route::get('levels', [LevelController::class, 'index'])->name('levels.index');
        Route::post('levels', [LevelController::class, 'store'])->name('levels.store');
        Route::put('levels/{level}', [LevelController::class, 'update'])->name('levels.update');
        Route::delete('levels/{level}', [LevelController::class, 'destroy'])->name('levels.destroy');
        Route::get('levels/{id}/salary-range', [LevelController::class, 'getSalaryRange'])->name('levels.salary-range');

        // Positions
        Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
        Route::post('positions', [PositionController::class, 'store'])->name('positions.store');
        Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update');
        Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
        Route::get('positions/department/{departmentId}', [PositionController::class, 'getByDepartment'])->name('positions.by-department');
    });
});

// ============================================
// HRIS - EMPLOYEE MANAGEMENT
// ============================================
Route::middleware(['auth'])->prefix('hris')->name('hris.')->group(function () {

    // Organization Structure
    Route::get('organization', [OrganizationController::class, 'index'])->name('organization.index');
    Route::get('organization/chart-data', [OrganizationController::class, 'getChartData'])->name('organization.chart-data');

    // Employees
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Employee Actions
    Route::post('employees/{id}/resign', [EmployeeController::class, 'resign'])->name('employees.resign');
    Route::post('employees/{id}/terminate', [EmployeeController::class, 'terminate'])->name('employees.terminate');
    Route::post('employees/{id}/reactivate', [EmployeeController::class, 'reactivate'])->name('employees.reactivate');

    // Employee API
    Route::get('employees/api/positions-by-department/{departmentId}', [EmployeeController::class, 'getPositionsByDepartment'])->name('employees.positions-by-department');
    Route::get('employees/api/generate-nik', [EmployeeController::class, 'generateNik'])->name('employees.generate-nik');

    // Export
    Route::get('employees/export/all', [EmployeeController::class, 'export'])->name('employees.export');

    // Employee Families
    Route::prefix('employees/{employee_id}/families')->name('employees.families.')->group(function () {
        Route::get('/', [EmployeeFamilyController::class, 'index'])->name('index');
        Route::post('/store', [EmployeeFamilyController::class, 'store'])->name('store');
        Route::put('/update/{id}', [EmployeeFamilyController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [EmployeeFamilyController::class, 'destroy'])->name('destroy');
    });

    // Employee Contracts
    Route::prefix('employees/{employee_id}/contracts')->name('employees.contracts.')->group(function () {
        Route::get('/', [EmployeeContractController::class, 'index'])->name('index');
        Route::post('/', [EmployeeContractController::class, 'store'])->name('store');
        Route::put('/{id}/update', [EmployeeContractController::class, 'update'])->name('update');
        Route::delete('/{id}/destroy', [EmployeeContractController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/deactivate', [EmployeeContractController::class, 'deactivate'])->name('deactivate');
        Route::get('/{id}/download', [EmployeeContractController::class, 'download'])->name('download');
        Route::post('/{id}/renew', [EmployeeContractController::class, 'renew'])->name('renew');
    });

    // Employee Careers
    Route::prefix('employees/{employee_id}/careers')->name('employees.careers.')->group(function () {
        Route::get('/', [EmployeeCareerController::class, 'index'])->name('index');
        Route::post('/', [EmployeeCareerController::class, 'store'])->name('store');
        Route::put('/{id}/update', [EmployeeCareerController::class, 'update'])->name('update');
        Route::delete('/{id}/destroy', [EmployeeCareerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/deactivate', [EmployeeCareerController::class, 'deactivate'])->name('deactivate');
    });
});

// ============================================
// HRIS - PAYROLL
// ============================================
Route::middleware(['auth'])->prefix('hris/payroll')->name('hris.payroll.')->group(function () {

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

    // Payroll Components
    Route::prefix('components')->name('components.')->group(function () {
        Route::get('/', [PayrollComponentController::class, 'index'])->name('index');
        Route::post('/', [PayrollComponentController::class, 'store'])->name('store');
        Route::put('/{id}', [PayrollComponentController::class, 'update'])->name('update');
        Route::delete('/{id}', [PayrollComponentController::class, 'destroy'])->name('destroy');
    });

    // Employee Payroll Components (OLD - Backward compatibility)
    Route::prefix('employees/{employee_id}/components')->name('employee-components.')->group(function () {
        Route::get('/', [EmployeePayrollComponentController::class, 'index'])->name('index');
        Route::post('/', [EmployeePayrollComponentController::class, 'store'])->name('store');
        Route::put('/{id}', [EmployeePayrollComponentController::class, 'update'])->name('update');
        Route::delete('/{id}', [EmployeePayrollComponentController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/deactivate', [EmployeePayrollComponentController::class, 'deactivate'])->name('deactivate');
    });

    // Employee Salary Management (NEW - Centralized)
    Route::prefix('employee-salaries')->name('employee-salaries.')->group(function () {
        Route::get('/', [EmployeeSalaryManagementController::class, 'index'])->name('index');
        Route::get('/{employee_id}', [EmployeeSalaryManagementController::class, 'show'])->name('show');
        Route::post('/{employee_id}/assign', [EmployeeSalaryManagementController::class, 'assign'])->name('assign');
        Route::put('/{employee_id}/components/{component_id}', [EmployeeSalaryManagementController::class, 'updateComponent'])->name('update-component');
        Route::delete('/{employee_id}/components/{component_id}', [EmployeeSalaryManagementController::class, 'destroyComponent'])->name('destroy-component');
        Route::post('/{employee_id}/components/{component_id}/deactivate', [EmployeeSalaryManagementController::class, 'deactivate'])->name('deactivate-component');
        Route::post('/bulk-assign', [EmployeeSalaryManagementController::class, 'bulkAssign'])->name('bulk-assign');
    });

    // Payroll Slips
    Route::prefix('slips')->name('slips.')->group(function () {
        Route::get('/{id}', [PayrollSlipController::class, 'show'])->name('show');
        Route::put('/{id}', [PayrollSlipController::class, 'update'])->name('update');
        Route::delete('/{id}', [PayrollSlipController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/mark-as-paid', [PayrollSlipController::class, 'markAsPaid'])->name('mark-as-paid');
        Route::get('/{id}/download-pdf', [PayrollSlipController::class, 'downloadPdf'])->name('download-pdf');
        Route::post('/{id}/send-email', [PayrollSlipController::class, 'sendEmail'])->name('send-email');
    });

    // Payroll Adjustments (Post-Lock Changes)
    Route::prefix('adjustments')->name('adjustments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Payroll\PayrollAdjustmentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Payroll\PayrollAdjustmentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Payroll\PayrollAdjustmentController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\HumanResource\Payroll\PayrollAdjustmentController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\HumanResource\Payroll\PayrollAdjustmentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\HumanResource\Payroll\PayrollAdjustmentController::class, 'reject'])->name('reject');
    });
});

// ============================================
// HRIS - ATTENDANCE & OVERTIME
// ============================================
Route::middleware(['auth'])->prefix('hris/attendance')->name('hris.attendance.')->group(function () {

    // Shifts
    Route::prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::get('/create', [ShiftController::class, 'create'])->name('create');
        Route::post('/', [ShiftController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [ShiftController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ShiftController::class, 'update'])->name('update');
        Route::delete('/{id}', [ShiftController::class, 'destroy'])->name('destroy');
    });

    // Employee Schedules
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [EmployeeScheduleController::class, 'index'])->name('index');
        Route::get('/{id}', [EmployeeScheduleController::class, 'getSchedule'])->name('get');
        Route::post('/', [EmployeeScheduleController::class, 'store'])->name('store');
        Route::post('/generate-bulk', [EmployeeScheduleController::class, 'generateBulk'])->name('generate-bulk');
        Route::post('/swap-shifts', [EmployeeScheduleController::class, 'swapShifts'])->name('swap-shifts');
        Route::post('/mark-holiday', [EmployeeScheduleController::class, 'markHoliday'])->name('mark-holiday');
        Route::delete('/{id}', [EmployeeScheduleController::class, 'destroy'])->name('destroy');
    });

    // National Holidays
    Route::prefix('holidays')->name('holidays.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Attendance\NationalHolidayController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Attendance\NationalHolidayController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Attendance\NationalHolidayController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\HumanResource\Attendance\NationalHolidayController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\HumanResource\Attendance\NationalHolidayController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\HumanResource\Attendance\NationalHolidayController::class, 'destroy'])->name('destroy');
        Route::post('/copy-recurring', [\App\Http\Controllers\HumanResource\Attendance\NationalHolidayController::class, 'copyRecurring'])->name('copy-recurring');
    });

    // Attendance Logs
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [AttendanceLogController::class, 'index'])->name('index');
        Route::get('/today', [AttendanceLogController::class, 'today'])->name('today');
        Route::get('/my-attendance', [AttendanceLogController::class, 'myAttendance'])->name('my-attendance'); // Must come before /{id}
        Route::get('/summary/{employee_id}', [AttendanceLogController::class, 'summary'])->name('summary');
        Route::get('/{id}', [AttendanceLogController::class, 'show'])->name('show'); // Dynamic route must be after specific routes
        Route::post('/clock-in', [AttendanceLogController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [AttendanceLogController::class, 'clockOut'])->name('clock-out');
        Route::delete('/{id}', [AttendanceLogController::class, 'destroy'])->name('destroy');
    });

    // Attendance Summaries (NO OVERTIME APPROVAL - Moved to Overtime Module)
    Route::prefix('summaries')->name('summaries.')->group(function () {
        Route::get('/', [AttendanceSummaryController::class, 'index'])->name('index');
        Route::get('/employee/{employee_id}', [AttendanceSummaryController::class, 'employeeReport'])->name('employee-report');
        Route::post('/generate', [AttendanceSummaryController::class, 'generate'])->name('generate');
        Route::patch('/{id}/status', [AttendanceSummaryController::class, 'updateStatus'])->name('update-status');
        Route::post('/lock-for-payroll', [AttendanceSummaryController::class, 'lockForPayroll'])->name('lock-for-payroll');
        Route::post('/{id}/unlock-for-correction', [AttendanceSummaryController::class, 'unlockForCorrection'])->name('unlock-for-correction');
    });

    // ⭐ Attendance Adjustments (Ledger View + Manual Corrections)
    Route::prefix('adjustments')->name('adjustments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAdjustmentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAdjustmentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAdjustmentController::class, 'store'])->name('store');
        Route::get('/{adjustment}', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAdjustmentController::class, 'show'])->name('show');
        Route::delete('/{adjustment}', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAdjustmentController::class, 'destroy'])->name('destroy');
    });

    // 🔍 Attendance Audit (Compliance - HR Manager+)
    Route::prefix('audit')->name('audit.')->group(function () {
        Route::get('/timeline/{employee}/{date}', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAuditController::class, 'timeline'])->name('timeline');
        Route::get('/period/{employee}', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAuditController::class, 'periodTimeline'])->name('period');
        Route::get('/changes/{employee}/{date}', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAuditController::class, 'changes'])->name('changes');
        Route::get('/discrepancies', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAuditController::class, 'discrepancies'])->name('discrepancies');
        Route::post('/rebuild', [\App\Http\Controllers\HumanResource\Attendance\AttendanceAuditController::class, 'rebuild'])->name('rebuild');
    });

    // ✅ OVERTIME MANAGEMENT (Complete Module)
    Route::prefix('overtime')->name('overtime.')->group(function () {
        // List & CRUD
        Route::get('/', [OvertimeRequestController::class, 'index'])->name('index');
        Route::get('/create', [OvertimeRequestController::class, 'create'])->name('create');
        Route::post('/', [OvertimeRequestController::class, 'store'])->name('store');
        Route::get('/{id}', [OvertimeRequestController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OvertimeRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OvertimeRequestController::class, 'update'])->name('update');
        Route::delete('/{id}', [OvertimeRequestController::class, 'destroy'])->name('destroy');

        // ✅ Approval Actions (AUTO-SYNC to attendance_summaries)
        Route::get('/approvals/dashboard', [OvertimeRequestController::class, 'approvals'])->name('approvals');
        Route::post('/{id}/approve', [OvertimeRequestController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [OvertimeRequestController::class, 'reject'])->name('reject');
        Route::post('/{id}/cancel', [OvertimeRequestController::class, 'cancel'])->name('cancel');
        Route::post('/bulk-approve', [OvertimeRequestController::class, 'bulkApprove'])->name('bulk-approve');
    });
});

// ============================================
// HRIS - LEAVE MANAGEMENT
// ============================================
Route::middleware(['auth'])->prefix('hris/leave')->name('hris.leave.')->group(function () {
    // Leave Types (Admin Master Data)
    Route::prefix('types')->name('types.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Leave\LeaveTypeController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Leave\LeaveTypeController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Leave\LeaveTypeController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\HumanResource\Leave\LeaveTypeController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\HumanResource\Leave\LeaveTypeController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\HumanResource\Leave\LeaveTypeController::class, 'destroy'])->name('destroy');
    });

    // Leave Requests (User)
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'store'])->name('store');
        Route::get('/admin/all', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'adminIndex'])->name('admin');
        Route::get('/{id}', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'show'])->name('show');
        Route::post('/{id}/cancel', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/approve', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\HumanResource\Leave\LeaveRequestController::class, 'reject'])->name('reject');
    });
});

// ============================================
// HRIS - DOCUMENT MANAGEMENT
// ============================================
Route::middleware(['auth'])->prefix('hris/documents')->name('hris.documents.')->group(function () {

    // Document Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [DocumentCategoryController::class, 'index'])->name('index');
        Route::get('/create', [DocumentCategoryController::class, 'create'])->name('create');
        Route::post('/', [DocumentCategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [DocumentCategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [DocumentCategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [DocumentCategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [DocumentCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{category}/toggle-status', [DocumentCategoryController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Employee Documents
    Route::get('/', [EmployeeDocumentController::class, 'index'])->name('index');
    Route::get('/create', [EmployeeDocumentController::class, 'create'])->name('create');
    Route::post('/', [EmployeeDocumentController::class, 'store'])->name('store');
    Route::get('/{document}', [EmployeeDocumentController::class, 'show'])->name('show');
    Route::get('/{document}/edit', [EmployeeDocumentController::class, 'edit'])->name('edit');
    Route::put('/{document}', [EmployeeDocumentController::class, 'update'])->name('update');
    Route::delete('/{document}', [EmployeeDocumentController::class, 'destroy'])->name('destroy');

    // Document Actions
    Route::get('/{document}/download', [EmployeeDocumentController::class, 'download'])->name('download');
    Route::post('/{document}/approve', [EmployeeDocumentController::class, 'approve'])->name('approve');
    Route::post('/{document}/reject', [EmployeeDocumentController::class, 'reject'])->name('reject');
});

Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
});

// ============================================
// APPROVAL WORKFLOW MANAGEMENT (Admin)
// ============================================
Route::middleware(['auth'])->prefix('admin/approval-workflows')->name('admin.approval-workflows.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ApprovalWorkflowController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\ApprovalWorkflowController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\ApprovalWorkflowController::class, 'store'])->name('store');
    Route::get('/{id}', [\App\Http\Controllers\Admin\ApprovalWorkflowController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\ApprovalWorkflowController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\ApprovalWorkflowController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\ApprovalWorkflowController::class, 'destroy'])->name('destroy');
});

// ============================================
// USER APPROVALS
// ============================================
Route::middleware(['auth'])->prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/pending', [\App\Http\Controllers\User\ApprovalController::class, 'pending'])->name('pending');
    Route::get('/history', [\App\Http\Controllers\User\ApprovalController::class, 'history'])->name('history');
    Route::get('/{id}', [\App\Http\Controllers\User\ApprovalController::class, 'show'])->name('show');
    Route::post('/{id}/approve', [\App\Http\Controllers\User\ApprovalController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [\App\Http\Controllers\User\ApprovalController::class, 'reject'])->name('reject');
});

// ============================================
// HRIS - TRAINING & DEVELOPMENT
// ============================================
Route::middleware(['auth'])->prefix('hris/training')->name('hris.training.')->group(function () {

    // Skill Categories
    Route::resource('skill-categories', \App\Http\Controllers\HumanResource\Training\SkillCategoryController::class);

    // Skills
    Route::resource('skills', \App\Http\Controllers\HumanResource\Training\SkillController::class);
    Route::get('skills/api/proficiency-levels', [\App\Http\Controllers\HumanResource\Training\SkillController::class, 'getProficiencyLevels'])->name('skills.proficiency-levels');

    // Trainers
    Route::resource('trainers', \App\Http\Controllers\HumanResource\Training\TrainerController::class);

    // Training Programs
    Route::prefix('programs')->name('programs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/publish', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'publish'])->name('publish');
        Route::post('/{id}/start', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'start'])->name('start');
        Route::post('/{id}/complete', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'complete'])->name('complete');
        Route::post('/{id}/cancel', [\App\Http\Controllers\HumanResource\Training\TrainingProgramController::class, 'cancel'])->name('cancel');
    });

    // Training Courses (nested under programs)
    Route::prefix('programs/{program_id}/courses')->name('courses.')->group(function () {
        Route::post('/', [\App\Http\Controllers\HumanResource\Training\TrainingCourseController::class, 'store'])->name('store');
        Route::put('/{id}', [\App\Http\Controllers\HumanResource\Training\TrainingCourseController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\HumanResource\Training\TrainingCourseController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [\App\Http\Controllers\HumanResource\Training\TrainingCourseController::class, 'reorder'])->name('reorder');
        Route::get('/{id}/download', [\App\Http\Controllers\HumanResource\Training\TrainingCourseController::class, 'downloadMaterials'])->name('download');
    });

    // Training Enrollments
    Route::prefix('enrollments')->name('enrollments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'index'])->name('index');
        Route::post('/enroll', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'enroll'])->name('enroll');
        Route::post('/bulk-enroll', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'bulkEnroll'])->name('bulk-enroll');
        Route::post('/{id}/approve', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'approve'])->name('approve');
        Route::post('/{id}/cancel', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/start', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'start'])->name('start');
        Route::post('/{id}/complete', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'complete'])->name('complete');
        Route::post('/{id}/issue-certificate', [\App\Http\Controllers\HumanResource\Training\TrainingEnrollmentController::class, 'issueCertificate'])->name('issue-certificate');
    });

    // Certifications (Master)
    Route::resource('certifications', \App\Http\Controllers\HumanResource\Training\CertificationController::class);

    // Employee Certifications
    Route::prefix('employee-certifications')->name('employee-certifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'index'])->name('index');
        Route::get('/expiring', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'expiring'])->name('expiring');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/verify', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'verify'])->name('verify');
        Route::get('/{id}/download', [\App\Http\Controllers\HumanResource\Training\EmployeeCertificationController::class, 'download'])->name('download');
    });

    // Skill Assessments
    Route::prefix('assessments')->name('assessments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'store'])->name('store');
        Route::get('/employee/{employee_id}', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'employeeProfile'])->name('employee-profile');
        Route::get('/employee/{employee_id}/skill-gap', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'skillGap'])->name('skill-gap');
        Route::get('/{id}/edit', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'destroy'])->name('destroy');
        Route::post('/bulk', [\App\Http\Controllers\HumanResource\Training\SkillAssessmentController::class, 'bulkCreate'])->name('bulk');
    });
});

// ============================================
// EMPLOYEE SELF-SERVICE (ESS)
// ============================================
Route::middleware(['auth'])->prefix('ess')->name('ess.')->group(function () {
    Route::get('/', [\App\Http\Controllers\ESS\ESSController::class, 'index'])->name('dashboard');
    Route::get('/announcements/{announcement}', [\App\Http\Controllers\ESS\ESSController::class, 'showAnnouncement'])->name('announcements.show');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ESS\ESSProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [\App\Http\Controllers\ESS\ESSProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [\App\Http\Controllers\ESS\ESSProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [\App\Http\Controllers\ESS\ESSProfileController::class, 'updatePhoto'])->name('profile.photo');

    // Leave
    Route::get('/leave', [\App\Http\Controllers\ESS\ESSLeaveController::class, 'index'])->name('leave.index');
    Route::get('/leave/create', [\App\Http\Controllers\ESS\ESSLeaveController::class, 'create'])->name('leave.create');
    Route::post('/leave', [\App\Http\Controllers\ESS\ESSLeaveController::class, 'store'])->name('leave.store');
    Route::post('/leave/{id}/cancel', [\App\Http\Controllers\ESS\ESSLeaveController::class, 'cancel'])->name('leave.cancel');

    // Payroll
    Route::get('/payroll', [\App\Http\Controllers\ESS\ESSPayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/{id}', [\App\Http\Controllers\ESS\ESSPayrollController::class, 'show'])->name('payroll.show');
    Route::get('/payroll/{id}/download', [\App\Http\Controllers\ESS\ESSPayrollController::class, 'download'])->name('payroll.download');
});

require __DIR__ . '/auth.php';