<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * ERP-Grade Component Hierarchy:
     * 1. payroll_components = Company Policy/Template (default values)
     * 2. employee_payroll_components = Assignment + Override (exceptions only)
     */
    public function up(): void
    {
        // ========================================
        // PAYROLL COMPONENTS - Add default_amount
        // ========================================
        Schema::table('payroll_components', function (Blueprint $table) {
            // Default amount for fixed components
            if (!Schema::hasColumn('payroll_components', 'default_amount')) {
                $table->decimal('default_amount', 15, 2)->nullable()->after('percentage_value')
                    ->comment('Default amount if employee has no override');
            }
        });

        // ========================================
        // EMPLOYEE PAYROLL COMPONENTS - Add is_override flag
        // ========================================
        Schema::table('employee_payroll_components', function (Blueprint $table) {
            // Flag to indicate if this is an override or just assignment
            if (!Schema::hasColumn('employee_payroll_components', 'is_override')) {
                $table->boolean('is_override')->default(false)->after('amount')
                    ->comment('True if amount differs from component default');
            }

            // Original amount from component (for audit trail)
            if (!Schema::hasColumn('employee_payroll_components', 'original_amount')) {
                $table->decimal('original_amount', 15, 2)->nullable()->after('is_override')
                    ->comment('Original default amount at time of assignment');
            }

            // Override reason (audit trail)
            if (!Schema::hasColumn('employee_payroll_components', 'override_reason')) {
                $table->string('override_reason', 255)->nullable()->after('original_amount')
                    ->comment('Reason for override if is_override=true');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_components', 'default_amount')) {
                $table->dropColumn('default_amount');
            }
        });

        Schema::table('employee_payroll_components', function (Blueprint $table) {
            $columns = ['is_override', 'original_amount', 'override_reason'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('employee_payroll_components', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
