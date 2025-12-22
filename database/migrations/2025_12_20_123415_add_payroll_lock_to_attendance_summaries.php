<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            // Payroll lock
            $table->boolean('is_locked_for_payroll')->default(false)->after('approved_overtime_minutes')
                ->comment('If true, cannot modify approved_overtime_minutes (payroll already processed)');
            $table->timestamp('locked_at')->nullable()->after('is_locked_for_payroll');
            $table->foreignId('locked_by')->nullable()->after('locked_at')
                ->constrained('users')->onDelete('set null')
                ->comment('User who locked for payroll');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['is_locked_for_payroll', 'locked_at', 'locked_by']);
        });
    }
};