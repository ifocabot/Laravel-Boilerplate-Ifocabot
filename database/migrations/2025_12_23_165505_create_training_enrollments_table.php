<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->datetime('enrollment_date');
            $table->enum('status', ['pending', 'approved', 'enrolled', 'in_progress', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('approved_at')->nullable();
            $table->datetime('started_at')->nullable();
            $table->datetime('completion_date')->nullable();
            $table->decimal('final_score', 5, 2)->nullable()->comment('Final average score 0-100');
            $table->boolean('certificate_issued')->default(false);
            $table->date('certificate_issued_at')->nullable();
            $table->string('certificate_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->text('feedback')->nullable()->comment('Participant feedback');
            $table->timestamps();

            $table->unique(['training_program_id', 'employee_id']);
            $table->index('status');
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_enrollments');
    }
};
