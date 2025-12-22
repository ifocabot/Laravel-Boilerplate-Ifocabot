<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null')
                ->comment('Reference shift used for calculation');

            // Status Final (Important for Payroll)
            $table->enum('status', [
                'present',        // Hadir normal
                'late',          // Hadir tapi terlambat
                'absent',        // Tidak hadir (alpha)
                'leave',         // Cuti
                'sick',          // Sakit
                'permission',    // Izin
                'wfh',          // Work From Home
                'business_trip', // Dinas luar
                'alpha'         // Tidak hadir tanpa keterangan
            ])->default('absent');

            // Time Calculations (All in minutes for precision)
            $table->integer('total_work_minutes')->default(0)
                ->comment('Total actual work duration');
            $table->integer('late_minutes')->default(0)
                ->comment('Late arrival duration');
            $table->integer('early_leave_minutes')->default(0)
                ->comment('Early departure duration');
            $table->integer('overtime_minutes')->default(0)
                ->comment('System calculated overtime');
            $table->integer('approved_overtime_minutes')->default(0)
                ->comment('Approved overtime for payroll');

            // Additional flags
            $table->boolean('has_overtime_request')->default(false)
                ->comment('Employee submitted overtime request');
            $table->boolean('overtime_approved')->default(false)
                ->comment('Overtime approved by manager');

            // Notes
            $table->text('notes')->nullable()
                ->comment('Manual notes from HR/Manager');
            $table->text('system_notes')->nullable()
                ->comment('Auto-generated system notes');

            $table->timestamps();

            // Indexes
            $table->unique(['employee_id', 'date']);
            $table->index(['employee_id', 'date']);
            $table->index('date');
            $table->index('status');
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summaries');
    }
};