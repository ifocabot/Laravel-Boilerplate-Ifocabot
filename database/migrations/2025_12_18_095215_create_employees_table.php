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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('nik', 20)->unique();
            $table->string('full_name', 150);
            $table->string('email_corporate', 100)->unique()->nullable();
            $table->string('phone_number', 20)->nullable();

            // Personal Information
            $table->string('place_of_birth', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'widow', 'widower'])->nullable();
            $table->string('religion', 50)->nullable();

            // Identity Numbers
            $table->string('id_card_number', 20)->nullable(); // KTP
            $table->string('npwp_number', 20)->nullable(); // Tax
            $table->string('bpjs_tk_number', 20)->nullable(); // BPJS Ketenagakerjaan
            $table->string('bpjs_kes_number', 20)->nullable(); // BPJS Kesehatan

            // Employment Information
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            // Dates
            $table->date('join_date');
            $table->date('resign_date')->nullable();

            // Status
            $table->enum('status', ['active', 'resigned', 'terminated'])->default('active');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('nik');
            $table->index('full_name');
            $table->index('email_corporate');
            $table->index('status');
            $table->index('department_id');
            $table->index('position_id');
            $table->index('join_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};