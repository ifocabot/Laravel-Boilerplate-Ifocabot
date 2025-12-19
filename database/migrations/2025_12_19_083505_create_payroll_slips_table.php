<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_slips', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            // Slip Info
            $table->string('slip_number', 50)->unique(); // PS-2024-12-001
            $table->date('slip_date');

            // Employee Info Snapshot (at the time of payroll)
            $table->string('employee_nik', 20);
            $table->string('employee_name', 150);
            $table->string('department', 100)->nullable();
            $table->string('position', 100)->nullable();
            $table->string('level', 50)->nullable();

            // Working Days
            $table->integer('working_days')->default(0); // Hari kerja dalam bulan
            $table->integer('actual_days')->default(0); // Hari kerja aktual
            $table->integer('absent_days')->default(0);
            $table->integer('leave_days')->default(0);

            // Salary Components (JSON for flexibility)
            $table->json('earnings')->nullable(); // Array of earnings with amount
            $table->json('deductions')->nullable(); // Array of deductions with amount

            // Totals
            $table->decimal('gross_salary', 15, 2); // Total pendapatan
            $table->decimal('total_deductions', 15, 2); // Total potongan
            $table->decimal('net_salary', 15, 2); // Take home pay

            // Tax Calculation
            $table->string('tax_status', 10)->nullable(); // TK/0, K/1, etc
            $table->decimal('taxable_income', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);

            // BPJS
            $table->decimal('bpjs_tk_company', 15, 2)->default(0);
            $table->decimal('bpjs_tk_employee', 15, 2)->default(0);
            $table->decimal('bpjs_kes_company', 15, 2)->default(0);
            $table->decimal('bpjs_kes_employee', 15, 2)->default(0);

            // Payment Info
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable(); // 'transfer', 'cash', 'check'
            $table->string('payment_reference', 100)->nullable();

            // Bank Info (Snapshot)
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_holder', 150)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('payroll_period_id');
            $table->index('employee_id');
            $table->index('slip_date');
            $table->index('payment_status');
            $table->unique(['payroll_period_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_slips');
    }
};