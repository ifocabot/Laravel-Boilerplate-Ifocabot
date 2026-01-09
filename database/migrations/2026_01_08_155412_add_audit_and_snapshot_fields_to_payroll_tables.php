<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Phase 1: Freeze & Audit - Add audit trail and snapshot fields for ERP-grade payroll
     */
    public function up(): void
    {
        // ========================================
        // PAYROLL SLIPS - Add calculation snapshot
        // ========================================
        Schema::table('payroll_slips', function (Blueprint $table) {
            // Calculation snapshot - stores all data used for calculation
            if (!Schema::hasColumn('payroll_slips', 'calculation_snapshot')) {
                $table->json('calculation_snapshot')->nullable()->after('notes')
                    ->comment('Snapshot of components, attendance, and params used for calculation');
            }

            // Audit fields
            if (!Schema::hasColumn('payroll_slips', 'generated_by')) {
                $table->foreignId('generated_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payroll_slips', 'generated_at')) {
                $table->timestamp('generated_at')->nullable();
            }
        });

        // ========================================
        // PAYROLL PERIODS - Add missing audit fields
        // (approved_by already exists from original migration)
        // ========================================
        Schema::table('payroll_periods', function (Blueprint $table) {
            // approved_by already exists, add approved_at if missing
            if (!Schema::hasColumn('payroll_periods', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            // Add paid_by, paid_at for marking as paid
            if (!Schema::hasColumn('payroll_periods', 'paid_by')) {
                $table->foreignId('paid_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payroll_periods', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }

            // Add closed_by, closed_at for closing period
            if (!Schema::hasColumn('payroll_periods', 'closed_by')) {
                $table->foreignId('closed_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('payroll_periods', 'closed_at')) {
                $table->timestamp('closed_at')->nullable();
            }
        });

        // ========================================
        // ATTENDANCE PERIOD SUMMARIES - Add lock audit
        // ========================================
        Schema::table('attendance_period_summaries', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_period_summaries', 'locked_by')) {
                $table->foreignId('locked_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('attendance_period_summaries', 'locked_at')) {
                $table->timestamp('locked_at')->nullable();
            }
        });

        // ========================================
        // EMPLOYEE PAYROLL COMPONENTS - Add audit trail
        // ========================================
        Schema::table('employee_payroll_components', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_payroll_components', 'created_by')) {
                $table->foreignId('created_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('employee_payroll_components', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_slips', 'generated_by')) {
                $table->dropForeign(['generated_by']);
                $table->dropColumn('generated_by');
            }
            if (Schema::hasColumn('payroll_slips', 'generated_at')) {
                $table->dropColumn('generated_at');
            }
            if (Schema::hasColumn('payroll_slips', 'calculation_snapshot')) {
                $table->dropColumn('calculation_snapshot');
            }
        });

        Schema::table('payroll_periods', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_periods', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('payroll_periods', 'paid_by')) {
                $table->dropForeign(['paid_by']);
                $table->dropColumn('paid_by');
            }
            if (Schema::hasColumn('payroll_periods', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('payroll_periods', 'closed_by')) {
                $table->dropForeign(['closed_by']);
                $table->dropColumn('closed_by');
            }
            if (Schema::hasColumn('payroll_periods', 'closed_at')) {
                $table->dropColumn('closed_at');
            }
        });

        Schema::table('attendance_period_summaries', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_period_summaries', 'locked_by')) {
                $table->dropForeign(['locked_by']);
                $table->dropColumn('locked_by');
            }
            if (Schema::hasColumn('attendance_period_summaries', 'locked_at')) {
                $table->dropColumn('locked_at');
            }
        });

        Schema::table('employee_payroll_components', function (Blueprint $table) {
            if (Schema::hasColumn('employee_payroll_components', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('employee_payroll_components', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};
