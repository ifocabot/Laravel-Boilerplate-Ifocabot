<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->unsignedInteger('approval_order')->default(0)->after('grade_code')
                ->comment('Urutan hierarki approval (1=terendah, 6=tertinggi)');
        });

        // Update existing levels with default approval_order based on grade_code
        $this->seedApprovalOrder();
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn('approval_order');
        });
    }

    protected function seedApprovalOrder(): void
    {
        $levelOrders = [
            'JR' => 1,   // Junior Staff
            'SR' => 2,   // Senior Staff
            'SPV' => 3,  // Supervisor
            'MGR' => 4,  // Manager
            'GM' => 5,   // General Manager
            'DIR' => 6,  // Direktur
        ];

        foreach ($levelOrders as $gradeCode => $order) {
            \DB::table('levels')
                ->where('grade_code', $gradeCode)
                ->update(['approval_order' => $order]);
        }
    }
};
