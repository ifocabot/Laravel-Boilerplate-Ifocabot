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
        Schema::create('employee_careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('position_id')->constrained('positions')->restrictOnDelete();
            $table->foreignId('level_id')->constrained('levels')->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            // Contract Information
            $table->enum('contract_type', ['pkwt', 'pkwtt', 'internship']);
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();

            // Salary
            $table->decimal('basic_salary', 15, 2);

            // Effective Period
            $table->date('start_date'); // Tanggal Efektif SK
            $table->date('end_date')->nullable(); // NULL = Aktif

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('employee_id');
            $table->index('department_id');
            $table->index('position_id');
            $table->index('is_active');
            $table->index(['employee_id', 'is_active']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_careers');
    }
};