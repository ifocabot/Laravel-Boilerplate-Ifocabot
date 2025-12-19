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
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            // Contract Information
            $table->string('contract_number', 100)->nullable();
            $table->enum('type', ['pkwt', 'pkwtt', 'internship', 'probation']);

            // Contract Period
            $table->date('start_date');
            $table->date('end_date')->nullable(); // NULL if Permanent (PKWTT)

            // Document
            $table->string('document_path')->nullable(); // Path to PDF in storage

            // Status
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('employee_id');
            $table->index('type');
            $table->index('is_active');
            $table->index(['employee_id', 'is_active']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};