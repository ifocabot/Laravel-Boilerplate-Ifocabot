<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Regular Day Shift',
                'code' => 'SH001',
                'type' => 'fixed',
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'work_hours_required' => 480, // 8 hours
                'late_tolerance_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
                'description' => 'Shift reguler pagi-sore, Senin-Jumat',
            ],
            [
                'name' => 'Morning Shift',
                'code' => 'SH002',
                'type' => 'fixed',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'break_start' => '10:00:00',
                'break_end' => '10:30:00',
                'work_hours_required' => 450, // 7.5 hours
                'late_tolerance_minutes' => 10,
                'is_overnight' => false,
                'is_active' => true,
                'description' => 'Shift pagi untuk produksi',
            ],
            [
                'name' => 'Afternoon Shift',
                'code' => 'SH003',
                'type' => 'fixed',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'break_start' => '18:00:00',
                'break_end' => '18:30:00',
                'work_hours_required' => 450, // 7.5 hours
                'late_tolerance_minutes' => 10,
                'is_overnight' => false,
                'is_active' => true,
                'description' => 'Shift sore untuk produksi',
            ],
            [
                'name' => 'Night Shift',
                'code' => 'SH004',
                'type' => 'fixed',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'break_start' => '02:00:00',
                'break_end' => '02:30:00',
                'work_hours_required' => 450, // 7.5 hours
                'late_tolerance_minutes' => 10,
                'is_overnight' => true,
                'is_active' => true,
                'description' => 'Shift malam untuk produksi (melewati tengah malam)',
            ],
            [
                'name' => 'Flexible Office Hours',
                'code' => 'SH005',
                'type' => 'flexible',
                'start_time' => '07:00:00', // Earliest allowed
                'end_time' => '19:00:00', // Latest allowed
                'break_start' => null,
                'break_end' => null,
                'work_hours_required' => 480, // Must work 8 hours
                'late_tolerance_minutes' => 0, // No late concept
                'is_overnight' => false,
                'is_active' => true,
                'description' => 'Jam fleksibel untuk staff kantor, minimal 8 jam kerja',
            ],
            [
                'name' => 'Part Time Morning',
                'code' => 'SH006',
                'type' => 'fixed',
                'start_time' => '08:00:00',
                'end_time' => '13:00:00',
                'break_start' => null,
                'break_end' => null,
                'work_hours_required' => 300, // 5 hours
                'late_tolerance_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
                'description' => 'Part time pagi 5 jam',
            ],
            [
                'name' => 'Part Time Afternoon',
                'code' => 'SH007',
                'type' => 'fixed',
                'start_time' => '13:00:00',
                'end_time' => '18:00:00',
                'break_start' => null,
                'break_end' => null,
                'work_hours_required' => 300, // 5 hours
                'late_tolerance_minutes' => 15,
                'is_overnight' => false,
                'is_active' => true,
                'description' => 'Part time sore 5 jam',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }

        $this->command->info('✅ Default shifts created successfully!');
    }
}