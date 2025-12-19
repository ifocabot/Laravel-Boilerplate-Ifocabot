<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_careers', function (Blueprint $table) {
            // Drop contract & salary related fields
            $table->dropColumn([
                'contract_type',
                'contract_start_date',
                'contract_end_date',
                'basic_salary'
            ]);

            // Rename location_id to branch_id for clarity
            $table->renameColumn('location_id', 'branch_id');

            // Add manager_id for reporting line
            $table->foreignId('manager_id')->nullable()->after('branch_id')
                ->constrained('employees')->nullOnDelete();

            // Add notes field
            $table->text('notes')->nullable()->after('is_active');

            // Add index for manager_id
            $table->index('manager_id');
        });
    }

    public function down(): void
    {
        Schema::table('employee_careers', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['manager_id', 'notes']);

            $table->renameColumn('branch_id', 'location_id');

            $table->enum('contract_type', ['pkwt', 'pkwtt', 'internship'])->after('location_id');
            $table->date('contract_start_date')->nullable()->after('contract_type');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->decimal('basic_salary', 15, 2)->after('contract_end_date');
        });
    }
};