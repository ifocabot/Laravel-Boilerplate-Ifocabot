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
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['internal', 'external', 'online', 'hybrid'])->default('internal');
            $table->string('provider', 200)->nullable()->comment('External provider if applicable');
            $table->foreignId('trainer_id')->nullable()->constrained('trainers')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('location', 200)->nullable();
            $table->integer('max_participants')->nullable();
            $table->decimal('cost_per_person', 15, 2)->default(0);
            $table->decimal('total_budget', 15, 2)->default(0);
            $table->integer('duration_hours')->nullable()->comment('Total program duration in hours');
            $table->enum('status', ['draft', 'open', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('objectives')->nullable()->comment('Learning objectives');
            $table->text('prerequisites')->nullable()->comment('Prerequisites for participants');
            $table->timestamps();

            $table->index('status');
            $table->index('type');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_programs');
    }
};
