<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            // Payroll period link
            $table->foreignId('payroll_period_id')->nullable()->after('leave_request_id')
                ->constrained('payroll_periods')->onDelete('set null')
                ->comment('Linked payroll period for this summary');

            // Schedule link (for schedule-driven generation)
            $table->foreignId('schedule_id')->nullable()->after('payroll_period_id')
                ->constrained('employee_schedules')->onDelete('set null')
                ->comment('Source schedule for this summary');

            // Actual clock times (datetime for cross-midnight support)
            $table->datetime('clock_in_at')->nullable()->after('schedule_id')
                ->comment('Actual clock-in datetime');
            $table->datetime('clock_out_at')->nullable()->after('clock_in_at')
                ->comment('Actual clock-out datetime');

            // Planned shift times (for cross-midnight shifts)
            $table->datetime('planned_start_at')->nullable()->after('clock_out_at')
                ->comment('Scheduled shift start datetime');
            $table->datetime('planned_end_at')->nullable()->after('planned_start_at')
                ->comment('Scheduled shift end datetime');

            // Rename overtime_minutes to detected_overtime_minutes for clarity
            // Keep backward compatibility by adding new column
            $table->integer('detected_overtime_minutes')->default(0)->after('early_leave_minutes')
                ->comment('System-detected overtime before shift hours deducted');

            // Source tracking for audit
            $table->json('source_flags')->nullable()->after('system_notes')
                ->comment('Audit: sources that updated this record (clock, leave, overtime, schedule, manual)');

            // Add index for payroll period queries
            $table->index('payroll_period_id');
        });

        // Update status enum to include offday and holiday
        // MySQL requires rebuilding the enum
        DB::statement("ALTER TABLE attendance_summaries MODIFY COLUMN status ENUM(
            'present', 'late', 'absent', 'leave', 'sick', 'permission', 
            'wfh', 'business_trip', 'alpha', 'offday', 'holiday'
        ) DEFAULT 'absent'");

        // Copy existing overtime_minutes to detected_overtime_minutes
        DB::statement('UPDATE attendance_summaries SET detected_overtime_minutes = overtime_minutes WHERE overtime_minutes > 0');
    }

    public function down(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->dropForeign(['payroll_period_id']);
            $table->dropForeign(['schedule_id']);
            $table->dropIndex(['payroll_period_id']);

            $table->dropColumn([
                'payroll_period_id',
                'schedule_id',
                'clock_in_at',
                'clock_out_at',
                'planned_start_at',
                'planned_end_at',
                'detected_overtime_minutes',
                'source_flags',
            ]);
        });

        // Revert status enum
        DB::statement("ALTER TABLE attendance_summaries MODIFY COLUMN status ENUM(
            'present', 'late', 'absent', 'leave', 'sick', 'permission', 
            'wfh', 'business_trip', 'alpha'
        ) DEFAULT 'absent'");
    }
};
