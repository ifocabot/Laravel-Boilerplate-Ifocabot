<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date');

            // Planned Overtime (Request)
            $table->time('start_at')->comment('Planned overtime start time');
            $table->time('end_at')->comment('Planned overtime end time');
            $table->integer('duration_minutes')->comment('Requested duration in minutes');

            $table->text('reason')->comment('Reason for overtime request');
            $table->text('work_description')->nullable()->comment('Description of work to be done');

            // Workflow Approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                ->default('pending');

            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Manager who approved/rejected');
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('approval_notes')->nullable()->comment('Notes from approver');
            $table->text('rejection_note')->nullable()->comment('Reason for rejection');

            // Actual overtime (will be filled after attendance)
            $table->integer('actual_duration_minutes')->default(0)
                ->comment('Actual overtime from attendance log');
            $table->integer('approved_duration_minutes')->default(0)
                ->comment('Final approved duration for payroll');

            // Cancellation
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'date']);
            $table->index(['employee_id', 'status']);
            $table->index('status');
            $table->index('approver_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};