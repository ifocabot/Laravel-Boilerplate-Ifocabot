<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // We use DB::statement because Doctrine/Laravel has trouble modifying ENUMs directly
        DB::statement("ALTER TABLE payroll_components MODIFY COLUMN calculation_type ENUM('fixed', 'percentage', 'daily_rate', 'hourly_rate') NOT NULL DEFAULT 'fixed'");
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            //
        });
    }
};
