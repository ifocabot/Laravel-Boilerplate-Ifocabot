<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            $table->string('contract_number', 100)->nullable();
            $table->enum('type', ['pkwt', 'pkwtt', 'internship', 'probation']);

            $table->date('start_date');
            $table->date('end_date')->nullable(); // NULL if Permanent (PKWTT)

            $table->string('document_path')->nullable(); // Path to PDF in storage

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('employee_id');
            $table->index('type');
            $table->index('is_active');
            $table->index(['employee_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};