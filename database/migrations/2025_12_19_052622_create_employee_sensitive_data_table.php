<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_sensitive_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained('employees')->cascadeOnDelete();

            // Identity Numbers (Encrypted)
            $table->text('id_card_number')->nullable(); // KTP - ENCRYPTED
            $table->text('npwp_number')->nullable();    // NPWP - ENCRYPTED
            $table->text('bpjs_tk_number')->nullable(); // BPJS Ketenagakerjaan - ENCRYPTED
            $table->text('bpjs_kes_number')->nullable(); // BPJS Kesehatan - ENCRYPTED

            // Bank Information (Encrypted)
            $table->text('bank_name')->nullable();
            $table->text('bank_account_number')->nullable(); // ENCRYPTED
            $table->text('bank_account_holder')->nullable();

            // Tax Information
            $table->enum('tax_status', ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'])->nullable();
            $table->text('tax_calculation_method')->nullable();

            // Emergency Contact (Encrypted)
            $table->text('emergency_contact_name')->nullable();
            $table->text('emergency_contact_relationship')->nullable();
            $table->text('emergency_contact_phone')->nullable(); // ENCRYPTED
            $table->text('emergency_contact_address')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_sensitive_data');
    }
};