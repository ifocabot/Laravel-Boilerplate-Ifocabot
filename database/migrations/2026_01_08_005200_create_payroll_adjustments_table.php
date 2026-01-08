<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_period_id')->constrained()
                ->comment('Period receiving this adjustment');
            $table->foreignId('source_period_id')->nullable()->constrained('payroll_periods')
                ->comment('Original period where change originated');
            $table->date('source_date')->nullable()
                ->comment('Specific date related to this adjustment');

            // Type of adjustment
            $table->enum('type', [
                'overtime',           // Late overtime approval
                'leave_correction',   // Leave approved after lock
                'attendance_correction', // Attendance status fixed
                'late_correction',    // Late minutes corrected
                'schedule_change',    // Schedule changed after lock
                'manual',             // HR manual adjustment
                'other'
            ]);

            // Amount (use the relevant one based on type)
            $table->integer('amount_minutes')->nullable()
                ->comment('For overtime/late corrections');
            $table->decimal('amount_days', 5, 2)->nullable()
                ->comment('For leave corrections (can be half days)');
            $table->decimal('amount_money', 15, 2)->nullable()
                ->comment('Direct money adjustment');

            // Description
            $table->text('reason');
            $table->text('notes')->nullable();

            // Reference to source record
            $table->string('reference_type')->nullable()->comment('Model class');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('Model ID');

            // Workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['employee_id', 'payroll_period_id'], 'idx_employee_period');
            $table->index('payroll_period_id');
            $table->index('status');
            $table->index('type');
            $table->index(['reference_type', 'reference_id'], 'idx_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};
