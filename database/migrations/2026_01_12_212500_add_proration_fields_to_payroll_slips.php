<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add proration policy fields to payroll_slips:
     * - paid_days: days used for proration calculation
     * - late_days: separate tracking for late days
     * - excess_deduction: when deductions > gross, carry over to next period
     */
    public function up(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->integer('paid_days')->default(0)->after('leave_days');
            $table->integer('late_days')->default(0)->after('paid_days');
            $table->decimal('excess_deduction', 15, 2)->default(0)->after('net_salary');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->dropColumn(['paid_days', 'late_days', 'excess_deduction']);
        });
    }
};
