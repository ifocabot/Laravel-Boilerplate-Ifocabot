<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Proficiency Levels (1-5):
     * 1 = Novice - Basic theoretical knowledge, requires close supervision
     * 2 = Beginner - Can perform simple tasks with guidance
     * 3 = Competent - Works independently on standard tasks
     * 4 = Proficient - Handles complex situations, can mentor others
     * 5 = Expert - Master level, drives innovation and best practices
     */
    public function up(): void
    {
        Schema::create('skill_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->foreignId('assessor_id')->constrained('users')->cascadeOnDelete()->comment('User who performed assessment');
            $table->date('assessment_date');
            $table->tinyInteger('proficiency_level')->comment('1-5 scale');
            $table->decimal('proficiency_score', 5, 2)->nullable()->comment('Optional detailed score 0-100');
            $table->enum('assessment_type', ['self', 'manager', 'peer', '360'])->default('manager');
            $table->text('evidence')->nullable()->comment('Supporting evidence/examples');
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_assessment_date')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'skill_id']);
            $table->index('assessment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_assessments');
    }
};
