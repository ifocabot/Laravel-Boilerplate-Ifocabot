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
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('duration_hours', 5, 2)->default(1);
            $table->integer('sequence')->default(1)->comment('Order of course in program');
            $table->string('materials_path', 500)->nullable()->comment('Path to course materials');
            $table->integer('passing_score')->default(70)->comment('Minimum score to pass (0-100)');
            $table->boolean('is_mandatory')->default(true);
            $table->text('learning_outcomes')->nullable();
            $table->timestamps();

            $table->index('training_program_id');
            $table->index('sequence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_courses');
    }
};
