<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Phase 2: Normalization - Create payroll_slip_items for easy reporting
     * This table allows direct queries like "Total MEAL for January 2026"
     * without needing to parse JSON arrays
     */
    public function up(): void
    {
        Schema::create('payroll_slip_items', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('payroll_slip_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_component_id')->nullable()->constrained()->nullOnDelete();

            // Component snapshot (in case component is deleted later)
            $table->string('component_code', 50);
            $table->string('component_name', 100);

            // Type and category
            $table->enum('type', ['earning', 'deduction']);
            $table->string('category', 50)->nullable();

            // Amounts
            $table->decimal('base_amount', 15, 2)->default(0)->comment('Original amount before any calculation');
            $table->decimal('final_amount', 15, 2)->default(0)->comment('Final amount after proration/calculation');

            // Calculation metadata
            $table->json('meta')->nullable()->comment('Additional data: days, hours, rate, percentage, etc.');

            // Display
            $table->integer('display_order')->default(0);
            $table->boolean('is_taxable')->default(false);

            $table->timestamps();

            // Indexes for reporting
            $table->index(['payroll_slip_id', 'type']);
            $table->index(['component_code', 'type']);
            $table->index('category');
        });

        // Add index for payroll_adjustments for period queries
        Schema::table('payroll_adjustments', function (Blueprint $table) {
            if (!Schema::hasIndex('payroll_adjustments', 'idx_adjustments_period_status')) {
                $table->index(['payroll_period_id', 'status'], 'idx_adjustments_period_status');
            }
        });

        // Add index for employee_payroll_components
        Schema::table('employee_payroll_components', function (Blueprint $table) {
            if (!Schema::hasIndex('employee_payroll_components', 'idx_emp_components_active')) {
                $table->index(['employee_id', 'is_active'], 'idx_emp_components_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_slip_items');

        Schema::table('payroll_adjustments', function (Blueprint $table) {
            $table->dropIndex('idx_adjustments_period_status');
        });

        Schema::table('employee_payroll_components', function (Blueprint $table) {
            $table->dropIndex('idx_emp_components_active');
        });
    }
};
