<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Phase 1: Tax Compliance - Versioned Tax Tables
     * 
     * Makes tax rules data-driven instead of hardcoded.
     * All TER rates and PTKP amounts are stored in DB with effective dates.
     */
    public function up(): void
    {
        // TER (Tarif Efektif Rata-rata) brackets per category
        Schema::create('tax_ter_brackets', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['A', 'B', 'C'])->comment('TER category based on PTKP status');
            $table->decimal('min_income', 15, 2)->default(0);
            $table->decimal('max_income', 15, 2)->nullable()->comment('NULL = unlimited');
            $table->decimal('rate', 7, 4)->comment('Rate as decimal: 0.0500 = 5%');
            $table->date('effective_from');
            $table->date('effective_to')->nullable()->comment('NULL = still active');
            $table->timestamps();

            $table->index(['category', 'effective_from']);
            $table->index(['effective_from', 'effective_to']);
        });

        // PTKP (Penghasilan Tidak Kena Pajak) rates per status
        Schema::create('tax_ptkp_rates', function (Blueprint $table) {
            $table->id();
            $table->string('status', 10)->comment('TK/0, K/0, K/1, K/2, K/3');
            $table->decimal('amount', 15, 2)->comment('Annual PTKP amount');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->unique(['status', 'effective_from']);
            $table->index('effective_from');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_ptkp_rates');
        Schema::dropIfExists('tax_ter_brackets');
    }
};
