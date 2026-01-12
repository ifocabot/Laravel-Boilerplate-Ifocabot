<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Attendance Events: Append-only ledger for all attendance changes.
     * This is the source of truth for audit/compliance.
     */
    public function up(): void
    {
        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date')->index();

            // Event type (see AttendanceEventType enum)
            $table->string('event_type', 50)->index();

            // Event-specific data (flexible JSON payload)
            $table->json('payload')->nullable();

            // Polymorphic source (LeaveRequest, OvertimeRequest, AttendanceLog, etc.)
            $table->nullableMorphs('source');

            // Who triggered this event
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Immutable timestamp
            $table->timestamp('created_at')->useCurrent();

            // Composite index for efficient rebuild queries
            $table->index(['employee_id', 'date', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_events');
    }
};
