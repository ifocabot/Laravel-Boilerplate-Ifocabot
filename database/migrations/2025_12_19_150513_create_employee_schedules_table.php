<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->date('date');

            // Schedule types
            $table->boolean('is_day_off')->default(false)
                ->comment('Jatah libur/off karyawan');
            $table->boolean('is_holiday')->default(false)
                ->comment('Hari libur nasional/tanggal merah');

            // Additional info
            $table->string('notes', 255)->nullable()
                ->comment('Catatan tambahan (tukar shift, dll)');

            $table->timestamps();

            // Indexes
            $table->unique(['employee_id', 'date']);
            $table->index('date');
            $table->index(['employee_id', 'date']);
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_schedules');
    }
};