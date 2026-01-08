<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            // Cut-off date configuration
            $table->integer('cutoff_start_day')->default(21)->after('month')
                ->comment('Day of month period starts (e.g., 21 for 21st)');
            $table->integer('cutoff_end_day')->default(20)->after('cutoff_start_day')
                ->comment('Day of month period ends (e.g., 20 for 20th)');

            // Attendance lock status
            $table->boolean('attendance_locked')->default(false)->after('status');
            $table->timestamp('attendance_locked_at')->nullable()->after('attendance_locked');
            $table->foreignId('attendance_locked_by')->nullable()->after('attendance_locked_at')
                ->constrained('users')->onDelete('set null');

            // Processing timestamps
            $table->timestamp('period_summary_generated_at')->nullable()->after('attendance_locked_by');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['attendance_locked_by']);
            $table->dropColumn([
                'cutoff_start_day',
                'cutoff_end_day',
                'attendance_locked',
                'attendance_locked_at',
                'attendance_locked_by',
                'period_summary_generated_at',
            ]);
        });
    }
};
