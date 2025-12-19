<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop foreign keys dan columns yang akan dipindah ke employee_careers
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['location_id']);

            $table->dropColumn([
                'department_id',
                'position_id',
                'level_id',
                'location_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
        });
    }
};