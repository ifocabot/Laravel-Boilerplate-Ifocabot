<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();

            // Period Info
            $table->string('period_code', 20)->unique(); // Format: YYYY-MM (2024-12)
            $table->string('period_name', 100); // "Payroll Desember 2024"
            $table->integer('year');
            $table->integer('month'); // 1-12

            // Period Dates
            $table->date('start_date'); // 1st of month
            $table->date('end_date'); // Last day of month
            $table->date('payment_date'); // Actual payment date

            // Status
            $table->enum('status', ['draft', 'processing', 'approved', 'paid', 'closed'])
                ->default('draft');
            $table->date('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');

            // Totals (Summary)
            $table->decimal('total_gross_salary', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('total_net_salary', 15, 2)->default(0);
            $table->integer('total_employees')->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['year', 'month']);
            $table->index('status');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};