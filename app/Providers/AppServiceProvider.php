<?php

namespace App\Providers;

use App\Models\AttendanceLog;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Observers\AttendanceLogObserver;
use App\Observers\EmployeeScheduleObserver;
use App\Observers\LeaveRequestObserver;
use App\Observers\OvertimeRequestObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-create Employee when User is created
        User::observe(UserObserver::class);

        // Attendance sync observers
        AttendanceLog::observe(AttendanceLogObserver::class);
        LeaveRequest::observe(LeaveRequestObserver::class);
        OvertimeRequest::observe(OvertimeRequestObserver::class);
        EmployeeSchedule::observe(EmployeeScheduleObserver::class);
    }
}
