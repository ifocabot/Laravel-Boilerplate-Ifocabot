<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_payroll_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('payroll_components')->cascadeOnDelete();

            // Amount
            $table->decimal('amount', 15, 2); // Fixed amount or base for percentage
            $table->string('unit', 50)->nullable(); // 'IDR', 'day', 'hour', '%'

            // Effective Period
            $table->date('effective_from');
            $table->date('effective_to')->nullable(); // NULL = no end date

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_recurring')->default(true); // Recurring or one-time?

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('employee_id');
            $table->index('component_id');
            $table->index('is_active');
            $table->index(['employee_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_components');
    }
};