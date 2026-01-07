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
        // ========================================
        // 1. Update approval_workflow_steps table
        // ========================================
        Schema::table('approval_workflow_steps', function (Blueprint $table) {
            // Conditions for step activation (JSON rules)
            $table->json('conditions')->nullable()->after('can_skip_if_same')
                ->comment('JSON conditions for step activation');

            // What to do when resolver fails
            $table->enum('on_resolution_fail', ['fail_request', 'skip_step'])
                ->default('fail_request')
                ->after('conditions')
                ->comment('Action when approver resolution fails');

            // Message template for failure
            $table->string('failure_message', 255)->nullable()->after('on_resolution_fail')
                ->comment('Human-readable failure message template');
        });

        // ========================================
        // 2. Update approval_requests table
        // ========================================
        Schema::table('approval_requests', function (Blueprint $table) {
            // Context snapshot at submission time
            $table->json('context')->nullable()->after('status')
                ->comment('Snapshot of approval context at submission');

            // Failure tracking
            $table->string('failure_code', 50)->nullable()->after('context')
                ->comment('System failure code: NO_APPROVER_RESOLVED, INVALID_CONDITION, etc');

            $table->string('failure_reason', 255)->nullable()->after('failure_code')
                ->comment('Human-readable failure reason');
        });

        // Update status column to support new values
        // Note: MySQL doesn't allow ALTER ENUM easily, so we change to VARCHAR
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        // ========================================
        // 3. Update approval_request_steps table
        // ========================================
        Schema::table('approval_request_steps', function (Blueprint $table) {
            // Link to master workflow step
            $table->foreignId('workflow_step_id')->nullable()->after('approval_request_id')
                ->constrained('approval_workflow_steps')->onDelete('set null');

            // Snapshot of rule at creation time (for audit)
            $table->string('approver_type', 50)->nullable()->after('step_order')
                ->comment('Snapshot of approver_type at creation');

            $table->string('approver_value', 100)->nullable()->after('approver_type')
                ->comment('Snapshot of approver_value at creation');

            $table->json('conditions_snapshot')->nullable()->after('approver_value')
                ->comment('Snapshot of conditions at creation');

            // Resolution tracking
            $table->string('resolver_type', 50)->nullable()->after('conditions_snapshot')
                ->comment('Which resolver class was used');

            $table->string('skip_reason', 50)->nullable()->after('resolver_type')
                ->comment('condition_not_met, same_approver, approver_not_found');

            $table->timestamp('resolved_at')->nullable()->after('skip_reason')
                ->comment('When the approver was resolved');
        });

        // Add unique constraint for idempotency
        Schema::table('approval_request_steps', function (Blueprint $table) {
            $table->unique(['approval_request_id', 'step_order'], 'unique_request_step_order');
        });

        // ========================================
        // 4. Create approval_events table
        // ========================================
        Schema::create('approval_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')
                ->constrained('approval_requests')
                ->onDelete('cascade');

            // Optional links for detailed audit
            $table->foreignId('approval_request_step_id')->nullable()
                ->constrained('approval_request_steps')
                ->onDelete('set null');

            $table->foreignId('workflow_step_id')->nullable()
                ->constrained('approval_workflow_steps')
                ->onDelete('set null');

            // Event details
            $table->string('event_type', 50)
                ->comment('created, step_created, step_skipped, approved, rejected, failed_to_resolve, etc');

            $table->foreignId('actor_id')->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User who triggered the event');

            // Rich payload for audit trail
            $table->json('payload')->nullable()
                ->comment('from_status, to_status, reason, resolved_approver_ids, context_snapshot');

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop approval_events table
        Schema::dropIfExists('approval_events');

        // Remove unique constraint from approval_request_steps
        Schema::table('approval_request_steps', function (Blueprint $table) {
            $table->dropUnique('unique_request_step_order');
        });

        // Remove new columns from approval_request_steps
        Schema::table('approval_request_steps', function (Blueprint $table) {
            $table->dropForeign(['workflow_step_id']);
            $table->dropColumn([
                'workflow_step_id',
                'approver_type',
                'approver_value',
                'conditions_snapshot',
                'resolver_type',
                'skip_reason',
                'resolved_at',
            ]);
        });

        // Remove new columns from approval_requests
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn(['context', 'failure_code', 'failure_reason']);
        });

        // Remove new columns from approval_workflow_steps
        Schema::table('approval_workflow_steps', function (Blueprint $table) {
            $table->dropColumn(['conditions', 'on_resolution_fail', 'failure_message']);
        });
    }
};
