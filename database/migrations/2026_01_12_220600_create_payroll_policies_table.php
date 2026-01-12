<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Phase 2: Hierarchical Policy Engine
     * 
     * Policies can be scoped to: company → branch → department → level → employee
     * Most specific scope wins (employee overrides level, level overrides dept, etc.)
     */
    public function up(): void
    {
        Schema::create('payroll_policies', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->comment('Policy key: overtime.multiplier, late.penalty_per_minute, etc.');
            $table->text('value')->comment('Policy value (JSON for complex values)');
            $table->enum('scope_type', ['company', 'branch', 'department', 'level', 'employee']);
            $table->unsignedBigInteger('scope_id')->nullable()->comment('ID of the scoped entity, null for company-wide');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Unique: same key + scope + effective date can't exist twice
            $table->unique(['key', 'scope_type', 'scope_id', 'effective_from'], 'payroll_policies_unique');

            // Indexes for fast lookup
            $table->index(['key', 'effective_from']);
            $table->index(['scope_type', 'scope_id']);

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_policies');
    }
};
