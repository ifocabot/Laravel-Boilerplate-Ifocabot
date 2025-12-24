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
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('document_categories')->onDelete('set null');
            $table->string('name', 100);
            $table->string('code', 50)->unique()->nullable();
            $table->text('description')->nullable();
            $table->json('allowed_file_types')->nullable(); // ['pdf', 'doc', 'jpg', 'png']
            $table->integer('max_file_size_mb')->default(10);
            $table->boolean('is_required_for_employees')->default(false);
            $table->boolean('is_confidential')->default(false);
            $table->json('access_roles')->nullable(); // Which roles can access
            $table->integer('retention_period_months')->nullable(); // Document retention policy
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('parent_id');
            $table->index('code');
            $table->index('is_active');
            $table->index('is_required_for_employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_categories');
    }
};
