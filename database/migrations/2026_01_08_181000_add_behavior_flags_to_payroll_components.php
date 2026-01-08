<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add behavior flags to payroll_components for dynamic calculation rules
     */
    public function up(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            // Proration behavior
            $table->enum('proration_type', ['none', 'daily', 'attendance'])->default('none')
                ->comment('none=full amount, daily=per working day, attendance=per present day');

            // Forfeit on alpha (absent without notice)
            $table->boolean('forfeit_on_alpha')->default(false)
                ->comment('If true, amount becomes 0 when employee has any alpha days');

            // Forfeit on late
            $table->boolean('forfeit_on_late')->default(false)
                ->comment('If true, amount becomes 0 when employee has any late days');

            // Minimum attendance percentage required
            $table->unsignedTinyInteger('min_attendance_percent')->nullable()
                ->comment('Minimum attendance % to receive this component (e.g., 80)');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_components', function (Blueprint $table) {
            $table->dropColumn([
                'proration_type',
                'forfeit_on_alpha',
                'forfeit_on_late',
                'min_attendance_percent',
            ]);
        });
    }
};
