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
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->onDelete('cascade');
            $table->morphs('requestable'); // requestable_type, requestable_id
            $table->foreignId('requester_id')->constrained('employees')->onDelete('cascade');
            $table->unsignedSmallInteger('current_step')->default(1);
            $table->string('status', 20)->default('pending')->comment('pending, approved, rejected, cancelled');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            // Note: morphs() already creates index on requestable_type + requestable_id
            $table->index('requester_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
