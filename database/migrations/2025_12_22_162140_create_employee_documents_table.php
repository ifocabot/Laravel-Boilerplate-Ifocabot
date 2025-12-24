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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('document_category_id')->constrained('document_categories');
            $table->string('title', 200);
            $table->text('description')->nullable();

            // File Information
            $table->string('original_filename', 255);
            $table->string('stored_filename', 255)->unique();
            $table->string('file_path', 500);
            $table->string('file_extension', 10);
            $table->string('mime_type', 100);
            $table->bigInteger('file_size_bytes');
            $table->string('file_hash', 64)->nullable(); // SHA256 for integrity check

            // Document Details
            $table->date('document_date')->nullable(); // Date when document was created/issued
            $table->date('expiry_date')->nullable(); // For documents that expire
            $table->string('document_number', 100)->nullable(); // External document reference
            $table->string('issuer', 150)->nullable(); // Who issued this document

            // Status & Approval
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'expired'])->default('draft');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Privacy & Access
            $table->boolean('is_confidential')->default(false);
            $table->json('access_permissions')->nullable(); // Additional access rules

            // Notifications
            $table->boolean('notify_expiry')->default(false);
            $table->integer('notify_days_before')->nullable();

            // Versioning
            $table->integer('version')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('employee_documents');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['employee_id', 'document_category_id']);
            $table->index('status');
            $table->index('expiry_date');
            $table->index('document_date');
            $table->index('uploaded_by');
            $table->index('file_hash');
            $table->index('stored_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
