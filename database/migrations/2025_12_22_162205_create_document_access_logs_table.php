<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_document_id')->constrained('employee_documents')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');

            // Access Details
            $table->enum('action', ['view', 'download', 'upload', 'update', 'delete', 'approve', 'reject']);
            $table->timestamp('accessed_at');

            // Request Information
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('session_id', 100)->nullable();

            // Additional Context
            $table->text('notes')->nullable(); // For admin notes or additional context
            $table->json('metadata')->nullable(); // Any additional data (file size, etc)

            // Result
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['employee_document_id', 'accessed_at']);
            $table->index(['user_id', 'accessed_at']);
            $table->index('action');
            $table->index('accessed_at');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_access_logs');
    }
};
