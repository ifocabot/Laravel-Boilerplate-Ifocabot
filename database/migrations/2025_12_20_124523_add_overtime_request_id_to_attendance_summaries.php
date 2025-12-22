<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            // Add foreign key to overtime_requests
            $table->foreignId('overtime_request_id')
                ->nullable()
                ->after('shift_id')
                ->constrained('overtime_requests')
                ->onDelete('set null')
                ->comment('Link to approved overtime request if exists');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_summaries', function (Blueprint $table) {
            $table->dropForeign(['overtime_request_id']);
            $table->dropColumn('overtime_request_id');
        });
    }
};