<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating/Updating Leave Types...');

        $leaveTypes = [
            [
                'code' => 'ANNUAL',
                'name' => 'Cuti Tahunan',
                'default_quota' => 12,
                'max_consecutive_days' => 14,
                'requires_attachment' => false,
                'is_paid' => true,
                'is_active' => true,
                'description' => 'Cuti tahunan regular sesuai undang-undang ketenagakerjaan',
            ],
            [
                'code' => 'SICK',
                'name' => 'Cuti Sakit',
                'default_quota' => 0, // Unlimited with doctor's note
                'max_consecutive_days' => 0, // No limit
                'requires_attachment' => true,
                'is_paid' => true,
                'is_active' => true,
                'description' => 'Cuti sakit dengan surat keterangan dokter',
            ],
            [
                'code' => 'UNPAID',
                'name' => 'Cuti Tidak Dibayar',
                'default_quota' => 0, // Unlimited but unpaid
                'max_consecutive_days' => 30,
                'requires_attachment' => false,
                'is_paid' => false,
                'is_active' => true,
                'description' => 'Cuti tanpa dibayar untuk keperluan pribadi',
            ],
            [
                'code' => 'MATERNITY',
                'name' => 'Cuti Melahirkan',
                'default_quota' => 90, // 3 months
                'max_consecutive_days' => 90,
                'requires_attachment' => true,
                'is_paid' => true,
                'is_active' => true,
                'description' => 'Cuti melahirkan untuk karyawan wanita (3 bulan)',
            ],
            [
                'code' => 'PATERNITY',
                'name' => 'Cuti Kelahiran Anak',
                'default_quota' => 2,
                'max_consecutive_days' => 2,
                'requires_attachment' => true,
                'is_paid' => true,
                'is_active' => true,
                'description' => 'Cuti untuk karyawan pria saat istri melahirkan',
            ],
            [
                'code' => 'MARRIAGE',
                'name' => 'Cuti Menikah',
                'default_quota' => 3,
                'max_consecutive_days' => 3,
                'requires_attachment' => true,
                'is_paid' => true,
                'is_active' => true,
                'description' => 'Cuti pernikahan karyawan',
            ],
            [
                'code' => 'BEREAVE',
                'name' => 'Cuti Duka',
                'default_quota' => 3,
                'max_consecutive_days' => 3,
                'requires_attachment' => false,
                'is_paid' => true,
                'is_active' => true,
                'description' => 'Cuti duka atas meninggalnya anggota keluarga',
            ],
            [
                'code' => 'RELIGIOUS',
                'name' => 'Cuti Hari Raya',
                'default_quota' => 2,
                'max_consecutive_days' => 7,
                'requires_attachment' => false,
                'is_paid' => true,
                'is_active' => true,
                'description' => 'Cuti hari raya keagamaan (Idul Fitri, Natal, dll)',
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($leaveTypes as $data) {
            $leaveType = LeaveType::updateOrCreate(
                ['code' => $data['code']],
                $data
            );

            if ($leaveType->wasRecentlyCreated) {
                $created++;
                $this->command->info("  ✅ Created: {$leaveType->name} ({$leaveType->code})");
            } else {
                $updated++;
                $this->command->warn("  🔄 Updated: {$leaveType->name} ({$leaveType->code})");
            }
        }

        $this->command->newLine();
        $this->command->info("📊 Summary: {$created} created, {$updated} updated");

        $this->command->newLine();
        $this->command->table(
            ['Code', 'Name', 'Quota', 'Max Days', 'Paid', 'Active'],
            LeaveType::all()->map(fn($lt) => [
                $lt->code,
                $lt->name,
                $lt->default_quota ?: 'Unlimited',
                $lt->max_consecutive_days ?: 'No limit',
                $lt->is_paid ? '✓' : '✗',
                $lt->is_active ? '✓' : '✗',
            ])
        );

        $this->command->info('✨ Leave types seeded successfully!');
    }
}
