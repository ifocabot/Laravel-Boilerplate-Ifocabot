<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Phase 3: Audit Trail System
     * 
     * Logs all payroll-related changes for compliance and debugging.
     * Every state change, value modification, and calculation is recorded.
     */
    public function up(): void
    {
        Schema::create('payroll_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 100)->comment('Model class: PayrollPeriod, PayrollSlip, etc.');
            $table->unsignedBigInteger('auditable_id');
            $table->unsignedBigInteger('actor_id')->nullable()->comment('User who performed action');
            $table->string('action', 50)->comment('created, updated, status_changed, recalculated, etc.');
            $table->json('old_values')->nullable()->comment('Previous values (for updates)');
            $table->json('new_values')->nullable()->comment('New values');
            $table->json('context')->nullable()->comment('Additional context: period_id, reason, etc.');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at');

            // Indexes
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('actor_id');
            $table->index('action');
            $table->index('created_at');

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_audit_logs');
    }
};
