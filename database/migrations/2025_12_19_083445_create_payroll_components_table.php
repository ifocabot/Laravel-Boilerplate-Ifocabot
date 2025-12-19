<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_components', function (Blueprint $table) {
            $table->id();

            // Component Info
            $table->string('code', 50)->unique(); // BASIC_SALARY, TRANSPORT, TAX, etc
            $table->string('name', 150); // "Gaji Pokok", "Tunjangan Transport"
            $table->text('description')->nullable();

            // Type
            $table->enum('type', ['earning', 'deduction']); // Pendapatan / Potongan

            // Category
            $table->enum('category', [
                'basic_salary',      // Gaji pokok
                'fixed_allowance',   // Tunjangan tetap
                'variable_allowance',// Tunjangan variabel (overtime, bonus)
                'statutory',         // Potongan wajib (tax, BPJS)
                'other_deduction'    // Potongan lain (loan, cooperative)
            ]);

            // Calculation
            $table->enum('calculation_type', ['fixed', 'percentage', 'formula'])
                ->default('fixed');
            $table->text('calculation_formula')->nullable(); // For complex calculations

            // Tax Treatment
            $table->boolean('is_taxable')->default(true); // Kena PPh21?
            $table->boolean('is_bpjs_base')->default(false); // Masuk perhitungan BPJS?

            // Display
            $table->integer('display_order')->default(0);
            $table->boolean('show_on_slip')->default(true);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_components');
    }
};