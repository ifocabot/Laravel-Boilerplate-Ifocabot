<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            // Check if columns exist before adding
            if (!Schema::hasColumn('payroll_components', 'rate_per_day')) {
                $table->decimal('rate_per_day', 15, 2)->nullable()->after('calculation_type')
                    ->comment('Rate per day for daily_rate calculation');
            }

            if (!Schema::hasColumn('payroll_components', 'rate_per_hour')) {
                $table->decimal('rate_per_hour', 15, 2)->nullable()->after('rate_per_day')
                    ->comment('Rate per hour for hourly_rate calculation');
            }

            if (!Schema::hasColumn('payroll_components', 'percentage_value')) {
                $table->decimal('percentage_value', 5, 2)->nullable()->after('rate_per_hour')
                    ->comment('Percentage value for percentage calculation (e.g., 10.00 for 10%)');
            }

            if (!Schema::hasColumn('payroll_components', 'calculation_notes')) {
                $table->text('calculation_notes')->nullable()->after('percentage_value')
                    ->comment('Explanation of how this component is calculated');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            $columns = ['rate_per_day', 'rate_per_hour', 'percentage_value', 'calculation_notes'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('payroll_components', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};