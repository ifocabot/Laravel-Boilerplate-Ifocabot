<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add policy config fields to payroll_periods
     * 
     * These replace hardcoded values in PayrollCalculator:
     * - late_penalty_per_minute: was hardcoded 1000
     * - standard_monthly_hours: was hardcoded 173
     * - overtime_multiplier: was hardcoded 1.5
     * - overtime_hourly_rate: fixed rate instead of basic/hours
     */
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->decimal('late_penalty_per_minute', 10, 2)->default(1000);
            $table->integer('standard_monthly_hours')->default(173);
            $table->decimal('overtime_multiplier', 4, 2)->default(1.5);
            $table->decimal('overtime_hourly_rate', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn([
                'late_penalty_per_minute',
                'standard_monthly_hours',
                'overtime_multiplier',
                'overtime_hourly_rate',
            ]);
        });
    }
};
