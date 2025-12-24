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
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['internal', 'external'])->default('internal');
            // For internal trainers - link to employee
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            // For external trainers
            $table->string('name', 200)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('organization', 200)->nullable()->comment('Company/Institution for external trainers');
            $table->text('expertise')->nullable()->comment('Areas of expertise');
            $table->text('bio')->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable()->comment('Rate per hour for external trainers');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
