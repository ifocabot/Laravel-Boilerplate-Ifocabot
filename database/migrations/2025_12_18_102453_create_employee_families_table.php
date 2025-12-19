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
        Schema::create('employee_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name', 150);
            $table->enum('relation', ['spouse', 'child', 'parent', 'sibling']);
            $table->boolean('is_emergency_contact')->default(false);
            $table->string('phone', 20)->nullable();
            $table->boolean('is_bpjs_dependent')->default(false); // Tanggungan BPJS
            $table->timestamps();

            $table->index('employee_id');
            $table->index('relation');
            $table->index('is_emergency_contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_families');
    }
};