<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Phase 4: Component Formula Sandbox
     * 
     * Allows components to define custom calculation formulas
     * that are safely evaluated at runtime.
     */
    public function up(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            // Formula expression: e.g., "BASE * PRORATE_FACTOR" or "OT_HOURS * OT_RATE * OT_MULT"
            $table->text('formula_expression')->nullable()->after('rate_per_day');

            // Available variables for this component's formula
            $table->json('formula_variables')->nullable()->after('formula_expression');

            // Formula validation status
            $table->boolean('formula_validated')->default(false)->after('formula_variables');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            $table->dropColumn(['formula_expression', 'formula_variables', 'formula_validated']);
        });
    }
};
