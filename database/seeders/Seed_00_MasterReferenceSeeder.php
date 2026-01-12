<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;
use App\Models\Shift;
use App\Models\NationalHoliday;
use Illuminate\Support\Facades\DB;

/**
 * Seeder 00: Master & Reference Data
 * 
 * This is the foundation - all other seeders depend on this.
 */
class Seed_00_MasterReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // ========================================
        // DEPARTMENTS
        // ========================================
        $departments = [
            ['code' => 'HRD', 'name' => 'Human Resource Development'],
            ['code' => 'FIN', 'name' => 'Finance & Accounting'],
            ['code' => 'IT', 'name' => 'Information Technology'],
            ['code' => 'OPS', 'name' => 'Operations'],
            ['code' => 'MKT', 'name' => 'Marketing'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }

        // ========================================
        // POSITIONS (no code column, use name)
        // ========================================
        $positions = [
            ['name' => 'Director'],
            ['name' => 'Manager'],
            ['name' => 'Supervisor'],
            ['name' => 'Senior Staff'],
            ['name' => 'Staff'],
            ['name' => 'Junior Staff'],
            ['name' => 'Intern'],
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(['name' => $pos['name']], $pos);
        }

        // ========================================
        // LEVELS (Grade)
        // ========================================
        $levels = [
            ['grade_code' => 'L1', 'name' => 'Level 1 - Entry', 'min_salary' => 3000000, 'max_salary' => 5000000, 'approval_order' => 10],
            ['grade_code' => 'L2', 'name' => 'Level 2 - Junior', 'min_salary' => 4500000, 'max_salary' => 7000000, 'approval_order' => 20],
            ['grade_code' => 'L3', 'name' => 'Level 3 - Staff', 'min_salary' => 6000000, 'max_salary' => 10000000, 'approval_order' => 30],
            ['grade_code' => 'L4', 'name' => 'Level 4 - Senior', 'min_salary' => 8000000, 'max_salary' => 15000000, 'approval_order' => 40],
            ['grade_code' => 'L5', 'name' => 'Level 5 - Lead', 'min_salary' => 12000000, 'max_salary' => 20000000, 'approval_order' => 50],
            ['grade_code' => 'L6', 'name' => 'Level 6 - Manager', 'min_salary' => 18000000, 'max_salary' => 35000000, 'approval_order' => 60],
            ['grade_code' => 'L7', 'name' => 'Level 7 - Director', 'min_salary' => 30000000, 'max_salary' => 100000000, 'approval_order' => 70],
        ];

        foreach ($levels as $level) {
            Level::updateOrCreate(['grade_code' => $level['grade_code']], $level);
        }

        // ========================================
        // SHIFTS
        // ========================================
        $defaultWorkDays = [1, 2, 3, 4, 5]; // Mon-Fri

        $shifts = [
            [
                'code' => 'NORMAL',
                'name' => 'Normal Office Hours',
                'type' => 'fixed',
                'start_time' => '09:00:00',
                'end_time' => '18:00:00',
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'work_hours_required' => 8,
                'late_tolerance_minutes' => 5,
                'is_overnight' => false,
                'is_active' => true,
                'working_days' => $defaultWorkDays,
            ],
            [
                'code' => 'OVERNIGHT',
                'name' => 'Night Shift',
                'type' => 'fixed',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'break_start' => '01:00:00',
                'break_end' => '02:00:00',
                'work_hours_required' => 8,
                'late_tolerance_minutes' => 5,
                'is_overnight' => true,
                'is_active' => true,
                'working_days' => $defaultWorkDays,
            ],
            [
                'code' => 'MORNING',
                'name' => 'Morning Shift',
                'type' => 'fixed',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'break_start' => '10:00:00',
                'break_end' => '10:30:00',
                'work_hours_required' => 8,
                'late_tolerance_minutes' => 5,
                'is_overnight' => false,
                'is_active' => true,
                'working_days' => $defaultWorkDays,
            ],
            [
                'code' => 'AFTERNOON',
                'name' => 'Afternoon Shift',
                'type' => 'fixed',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'break_start' => '18:00:00',
                'break_end' => '18:30:00',
                'work_hours_required' => 8,
                'late_tolerance_minutes' => 5,
                'is_overnight' => false,
                'is_active' => true,
                'working_days' => $defaultWorkDays,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(['code' => $shift['code']], $shift);
        }

        // ========================================
        // HOLIDAYS 2026
        // ========================================
        $holidays = [
            ['date' => '2026-01-01', 'name' => 'Tahun Baru 2026', 'is_recurring' => false, 'is_active' => true],
            ['date' => '2026-01-29', 'name' => 'Tahun Baru Imlek', 'is_recurring' => false, 'is_active' => true],
            ['date' => '2026-03-20', 'name' => 'Isra Miraj', 'is_recurring' => false, 'is_active' => true],
            ['date' => '2026-03-31', 'name' => 'Hari Raya Nyepi', 'is_recurring' => false, 'is_active' => true],
            ['date' => '2026-04-03', 'name' => 'Wafat Isa Almasih', 'is_recurring' => false, 'is_active' => true],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh', 'is_recurring' => true, 'is_active' => true],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Isa Almasih', 'is_recurring' => false, 'is_active' => true],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila', 'is_recurring' => true, 'is_active' => true],
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan', 'is_recurring' => true, 'is_active' => true],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal', 'is_recurring' => true, 'is_active' => true],
        ];

        foreach ($holidays as $holiday) {
            NationalHoliday::updateOrCreate(['date' => $holiday['date']], $holiday);
        }

        $this->command->info('✅ Seed_00: Master reference data seeded');
    }
}
