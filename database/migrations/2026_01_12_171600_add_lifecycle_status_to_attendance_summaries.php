<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add lifecycle status to attendance_summaries for explicit state machine.
     */
    public function up(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->string('lifecycle_status', 20)
                ->default('pending')
                ->after('status')
                ->index();

            // Track who reviewed and when
            $table->foreignId('reviewed_by')->nullable()->after('locked_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });

        // Migrate existing data: set status based on current state
        DB::statement("
            UPDATE attendance_summaries 
            SET lifecycle_status = CASE 
                WHEN is_locked_for_payroll = 1 THEN 'locked'
                WHEN clock_out_at IS NOT NULL THEN 'calculated'
                ELSE 'pending'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->dropColumn(['lifecycle_status', 'reviewed_by', 'reviewed_at']);
        });
    }
};
