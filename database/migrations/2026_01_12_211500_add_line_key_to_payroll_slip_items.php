<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Fix SlipItem uniqueness issue
     * 
     * Current key (payroll_slip_id, component_code) can cause overwrites
     * when same code comes from multiple sources (e.g., 2 overtime adjustments)
     * 
     * New key includes line_key which is unique per item
     */
    public function up(): void
    {
        Schema::table('payroll_slip_items', function (Blueprint $table) {
            // Add line_key for fine-grained uniqueness
            $table->string('line_key', 100)->nullable()->after('component_code');

            // Add source tracking for adjustments
            $table->string('source_type', 50)->nullable()->after('meta');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
        });

        // Update existing records to have line_key = component_code (for backward compat)
        \DB::statement('UPDATE payroll_slip_items SET line_key = component_code WHERE line_key IS NULL');

        // Now make line_key required and add unique index
        Schema::table('payroll_slip_items', function (Blueprint $table) {
            $table->string('line_key', 100)->nullable(false)->change();

            // New unique key: slip + line_key
            $table->unique(['payroll_slip_id', 'line_key'], 'slip_items_unique_line');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_slip_items', function (Blueprint $table) {
            $table->dropUnique('slip_items_unique_line');
            $table->dropColumn(['line_key', 'source_type', 'source_id']);
        });
    }
};
