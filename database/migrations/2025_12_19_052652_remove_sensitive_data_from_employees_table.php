<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop sensitive columns
            $table->dropColumn([
                'id_card_number',
                'npwp_number',
                'bpjs_tk_number',
                'bpjs_kes_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Restore if rollback
            $table->string('id_card_number', 20)->nullable()->after('religion');
            $table->string('npwp_number', 20)->nullable()->after('id_card_number');
            $table->string('bpjs_tk_number', 20)->nullable()->after('npwp_number');
            $table->string('bpjs_kes_number', 20)->nullable()->after('bpjs_tk_number');
        });
    }
};