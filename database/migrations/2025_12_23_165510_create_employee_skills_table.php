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
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->tinyInteger('current_level')->default(1)->comment('Current proficiency 1-5');
            $table->tinyInteger('target_level')->nullable()->comment('Target proficiency 1-5');
            $table->date('last_assessed_at')->nullable();
            $table->enum('acquired_from', ['training', 'certification', 'experience', 'assessment', 'self_declared'])->default('self_declared');
            $table->boolean('is_primary')->default(false)->comment('Primary/main skill for employee');
            $table->integer('years_experience')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'skill_id']);
            $table->index('current_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
    }
};
