<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            $table->decimal('basic_salary', 15, 2);
            $table->decimal('fixed_allowance', 15, 2)->default(0); // Optional: other fixed components

            $table->date('effective_date'); // When it starts
            $table->date('end_date')->nullable(); // NULL = current salary

            $table->boolean('is_active')->default(true);
            $table->string('reason')->nullable(); // "Annual Review 2024", "Adjustment"

            $table->timestamps();

            // Indexes
            $table->index('employee_id');
            $table->index('is_active');
            $table->index(['employee_id', 'is_active']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};