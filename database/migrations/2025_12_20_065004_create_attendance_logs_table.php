<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('employee_schedules')->onDelete('set null')
                ->comment('Reference to scheduled shift for this date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null')
                ->comment('Snapshot of shift at clock-in time (in case master shift changes)');

            // Date and times
            // OPTION A: Keep ->index() here and remove it from the bottom
            $table->date('date')->index();

            // Clock In
            $table->dateTime('clock_in_time')->nullable();
            $table->decimal('clock_in_lat', 10, 8)->nullable()->comment('Latitude');
            $table->decimal('clock_in_long', 11, 8)->nullable()->comment('Longitude');
            $table->string('clock_in_device', 100)->nullable()->comment('Device info (mobile/web)');
            $table->string('clock_in_photo', 2048)->nullable()->comment('Photo path in storage');
            $table->text('clock_in_notes')->nullable()->comment('Notes at clock in');

            // Clock Out
            $table->dateTime('clock_out_time')->nullable();
            $table->decimal('clock_out_lat', 10, 8)->nullable()->comment('Latitude');
            $table->decimal('clock_out_long', 11, 8)->nullable()->comment('Longitude');
            $table->string('clock_out_device', 100)->nullable()->comment('Device info (mobile/web)');
            $table->string('clock_out_photo', 2048)->nullable()->comment('Photo path in storage');
            $table->text('clock_out_notes')->nullable()->comment('Notes at clock out');

            // Status flags
            $table->boolean('is_late')->default(false)->comment('Clock in late');
            $table->boolean('is_early_out')->default(false)->comment('Clock out early');
            $table->integer('late_duration_minutes')->default(0)->comment('How many minutes late');
            $table->integer('work_duration_minutes')->default(0)->comment('Total work duration');

            $table->timestamps();

            // Indexes
            // 1. UNIQUE index handles lookups for (employee_id + date). 
            $table->unique(['employee_id', 'date']);

            // 2. REMOVED: $table->index(['employee_id', 'date']); 
            // Reason: Redundant. The unique index above already covers this combination.

            // 3. REMOVED: $table->index('date'); 
            // Reason: Duplicate. You already added ->index() on line 19.

            // 4. These are good to keep for individual lookups
            $table->index('schedule_id');
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};