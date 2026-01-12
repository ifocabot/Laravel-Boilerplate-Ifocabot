<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Step 1: Add idempotency constraint + applied lifecycle
     * 
     * - Unique key prevents duplicate adjustments
     * - applied_at/payroll_slip_id tracks when adjustment was consumed
     */
    public function up(): void
    {
        Schema::table('payroll_adjustments', function (Blueprint $table) {
            // Applied lifecycle tracking
            $table->timestamp('applied_at')->nullable()->after('approved_at');
            $table->foreignId('payroll_slip_id')
                ->nullable()
                ->constrained('payroll_slips')
                ->nullOnDelete()
                ->after('applied_at');

            // Unique constraint for idempotency (prevent duplicate adjustments)
            // Natural key: same employee, type, reference, and source date
            $table->unique(
                ['employee_id', 'type', 'reference_type', 'reference_id', 'source_date'],
                'payroll_adjustments_natural_key'
            );
        });
    }

    public function down(): void
    {
        Schema::table('payroll_adjustments', function (Blueprint $table) {
            $table->dropUnique('payroll_adjustments_natural_key');
            $table->dropConstrainedForeignId('payroll_slip_id');
            $table->dropColumn('applied_at');
        });
    }
};
