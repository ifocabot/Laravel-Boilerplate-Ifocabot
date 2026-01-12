<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Add 'formula' and 'ter' to calculation_type enum
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE payroll_components MODIFY COLUMN calculation_type ENUM('fixed', 'percentage', 'daily_rate', 'hourly_rate', 'formula', 'ter') NOT NULL DEFAULT 'fixed'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payroll_components MODIFY COLUMN calculation_type ENUM('fixed', 'percentage', 'daily_rate', 'hourly_rate') NOT NULL DEFAULT 'fixed'");
    }
};
