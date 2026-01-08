<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_period_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');

            // Day counts
            $table->integer('present_days')->default(0)->comment('Days with present/late/wfh/business_trip');
            $table->integer('alpha_days')->default(0)->comment('No log, no leave');
            $table->integer('leave_days')->default(0)->comment('Approved leave');
            $table->integer('sick_days')->default(0)->comment('Sick leave');
            $table->integer('permission_days')->default(0)->comment('Permission/izin');
            $table->integer('late_days')->default(0)->comment('Days with late status');
            $table->integer('offday_days')->default(0)->comment('Scheduled days off');
            $table->integer('holiday_days')->default(0)->comment('National holidays');
            $table->integer('wfh_days')->default(0)->comment('Work from home days');
            $table->integer('business_trip_days')->default(0)->comment('Business trip days');
            $table->integer('scheduled_work_days')->default(0)->comment('Total scheduled work days');

            // Time aggregates (in minutes)
            $table->integer('total_worked_minutes')->default(0);
            $table->integer('total_late_minutes')->default(0);
            $table->integer('total_early_leave_minutes')->default(0);
            $table->integer('total_detected_overtime_minutes')->default(0)->comment('System detected');
            $table->integer('total_approved_overtime_minutes')->default(0)->comment('For payroll');

            // Lock info
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');

            // Generated info
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->unique(['employee_id', 'payroll_period_id'], 'unique_employee_period');
            $table->index('payroll_period_id');
            $table->index('is_locked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_period_summaries');
    }
};
