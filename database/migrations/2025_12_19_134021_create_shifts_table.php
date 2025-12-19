<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 10)->unique();
            $table->enum('type', ['fixed', 'flexible'])->default('fixed')
                ->comment('Fixed: Jam masuk/keluar tetap. Flexible: Hitung durasi saja');

            // Time slots
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            // Work hours (in minutes)
            $table->integer('work_hours_required')->default(0)
                ->comment('Required work hours in minutes (e.g., 480 for 8 hours)');

            // Tolerance
            $table->integer('late_tolerance_minutes')->default(15)
                ->comment('Grace period for late clock-in');

            // Special flags
            $table->boolean('is_overnight')->default(false)
                ->comment('Shift crosses midnight (e.g., 22:00 - 06:00)');
            $table->boolean('is_active')->default(true);

            // Description
            $table->text('description')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('code');
            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};