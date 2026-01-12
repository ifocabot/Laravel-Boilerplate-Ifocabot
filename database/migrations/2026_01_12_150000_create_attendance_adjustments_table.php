<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Attendance Adjustments Ledger
     * Tracks all modifications to attendance: leave, overtime, manual corrections
     * Makes summary regeneration safe (adjustments persist across regen)
     */
    public function up(): void
    {
        Schema::create('attendance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');

            // Adjustment type
            $table->string('type', 30); // leave, sick, permission, overtime_add, overtime_cancel, late_waive, manual_override

            // Values
            $table->integer('adjustment_minutes')->default(0)->comment('Minutes affected');
            $table->string('status_override', 20)->nullable()->comment('Override daily status');

            // Reference to source document
            $table->string('source_type')->nullable(); // LeaveRequest, OvertimeRequest, etc.
            $table->unsignedBigInteger('source_id')->nullable();

            // Audit
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'date']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_adjustments');
    }
};
