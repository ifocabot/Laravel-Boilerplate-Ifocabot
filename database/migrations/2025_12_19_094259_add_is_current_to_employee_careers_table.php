<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add is_current column
        Schema::table('employee_careers', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('is_active');
            $table->index('is_current');
        });

        // Update existing data: set is_current = true for active careers
        DB::statement("
            UPDATE employee_careers 
            SET is_current = 1 
            WHERE is_active = 1
        ");
    }

    public function down(): void
    {
        Schema::table('employee_careers', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });
    }
};