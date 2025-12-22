<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->json('working_days')->nullable()->after('is_overnight')
                ->comment('Array of working days: 0=Sunday, 1=Monday, ..., 6=Saturday. Example: [1,2,3,4,5] for Mon-Fri');
        });

        // Set default working days (Mon-Fri) for existing shifts
        DB::table('shifts')->update([
            'working_days' => json_encode([1, 2, 3, 4, 5])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('working_days');
        });
    }
};
