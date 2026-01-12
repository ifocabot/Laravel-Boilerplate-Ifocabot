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
        Schema::create('leave_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_leave_balance_id')->constrained('employee_leave_balances')->onDelete('cascade');
            $table->foreignId('leave_request_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type', 30)->comment('allocation, deduction, reversal, carry_forward, adjustment');
            $table->decimal('amount', 5, 1)->comment('positive = credit, negative = debit');
            $table->decimal('balance_after', 5, 1)->comment('running balance after transaction');
            $table->string('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('type');
            $table->index('employee_leave_balance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance_transactions');
    }
};
