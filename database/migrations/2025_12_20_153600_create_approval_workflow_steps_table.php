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
        Schema::create('approval_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->onDelete('cascade');
            $table->unsignedSmallInteger('step_order')->comment('Order: 1, 2, 3...');
            $table->string('approver_type', 50)->comment('direct_supervisor, position_level, specific_user');
            $table->string('approver_value', 100)->nullable()->comment('Level ID, User ID, or null');
            $table->boolean('is_required')->default(true);
            $table->boolean('can_skip_if_same')->default(true)->comment('Skip if same approver as previous step');
            $table->timestamps();

            $table->index(['workflow_id', 'step_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_steps');
    }
};
