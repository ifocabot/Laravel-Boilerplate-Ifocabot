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
        Schema::create('leave_request_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->onDelete('cascade');
            $table->date('date')->index();
            $table->decimal('day_value', 3, 1)->default(1.0)->comment('1.0 = full day, 0.5 = half day');
            $table->string('status', 20)->default('pending')->comment('pending, approved, cancelled');
            $table->timestamps();

            $table->unique(['leave_request_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_days');
    }
};
