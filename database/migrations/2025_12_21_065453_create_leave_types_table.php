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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('code', 10)->unique();
            $table->unsignedSmallInteger('default_quota')->default(0)->comment('Default quota per year');
            $table->boolean('requires_attachment')->default(false);
            $table->unsignedSmallInteger('max_consecutive_days')->nullable()->comment('Max consecutive days allowed');
            $table->boolean('is_paid')->default(true)->comment('Paid leave?');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
