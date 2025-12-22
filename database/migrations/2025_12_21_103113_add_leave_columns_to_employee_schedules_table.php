<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->boolean('is_leave')->default(false)->after('is_holiday')
                ->comment('Karyawan cuti/izin pada tanggal ini');
            $table->foreignId('leave_request_id')->nullable()->after('is_leave')
                ->constrained('leave_requests')->nullOnDelete()
                ->comment('Referensi ke leave request yang di-approve');
        });
    }

    public function down(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->dropForeign(['leave_request_id']);
            $table->dropColumn(['is_leave', 'leave_request_id']);
        });
    }
};
