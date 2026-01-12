<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Add unique constraint to prevent duplicate slip items on rerun.
     * This ensures (payroll_slip_id, component_code) is unique.
     */
    public function up(): void
    {
        Schema::table('payroll_slip_items', function (Blueprint $table) {
            // Add unique constraint to prevent duplicates
            $table->unique(['payroll_slip_id', 'component_code'], 'unique_slip_component');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_slip_items', function (Blueprint $table) {
            $table->dropUnique('unique_slip_component');
        });
    }
};
