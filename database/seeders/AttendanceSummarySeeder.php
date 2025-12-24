<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceSummary;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AttendanceSummary::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🚀 Starting Attendance Summaries Seeding...');

        // Get all attendance logs
        $logs = AttendanceLog::with('shift')->get();

        if ($logs->isEmpty()) {
            $this->command->error('❌ No attendance logs found. Please run AttendanceLogSeeder first.');
            return;
        }

        $this->command->info("📊 Found {$logs->count()} attendance logs to process");

        $progressBar = $this->command->getOutput()->createProgressBar($logs->count());
        $progressBar->start();

        $summariesCreated = 0;
        $summariesWithOvertime = 0;

        foreach ($logs as $log) {
            // Generate summary from log
            $summary = AttendanceSummary::generateFromLog($log);
            $summariesCreated++;

            // Add some realistic variations
            $this->addRealisticVariations($summary);

            if ($summary->overtime_minutes > 0) {
                $summariesWithOvertime++;

                // 80% chance overtime is approved
                if (rand(1, 100) <= 80) {
                    // Sometimes approved overtime is less than actual
                    $approvedMinutes = rand(1, 100) <= 90
                        ? $summary->overtime_minutes
                        : (int) ($summary->overtime_minutes * 0.75);

                    $summary->approveOvertime($approvedMinutes);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine(2);

        $this->command->info("✅ Successfully seeded {$summariesCreated} attendance summaries!");
        $this->command->info("   💼 {$summariesWithOvertime} summaries with overtime");

        // Show statistics
        $this->showStatistics();
    }

    /**
     * Add realistic variations to summaries
     */
    private function addRealisticVariations(AttendanceSummary $summary): void
    {
        // Add some manual notes occasionally (10% chance)
        if (rand(1, 100) <= 10) {
            $notes = [
                'Meeting dengan client',
                'Training karyawan baru',
                'Koordinasi dengan tim',
                'Presentasi ke manajemen',
                'Troubleshooting server',
                'Site visit',
            ];
            $summary->notes = $notes[array_rand($notes)];
        }

        // Sometimes mark as WFH (5% chance)
        if (rand(1, 100) <= 5 && $summary->status === 'present') {
            $summary->status = 'wfh';
        }

        // Sometimes mark as business trip (2% chance)
        if (rand(1, 100) <= 2 && $summary->status === 'present') {
            $summary->status = 'business_trip';
            $summary->notes = 'Dinas ke kantor cabang';
        }

        $summary->save();
    }

    /**
     * Show statistics after seeding
     */
    private function showStatistics(): void
    {
        $this->command->info("\n📊 Attendance Summaries Statistics:");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $total = AttendanceSummary::count();
        $present = AttendanceSummary::present()->count();
        $absent = AttendanceSummary::absent()->count();
        $late = AttendanceSummary::late()->count();
        $hasOvertime = AttendanceSummary::hasOvertime()->count();
        $approvedOvertime = AttendanceSummary::approvedOvertime()->count();
        $pendingApproval = AttendanceSummary::pendingOvertimeApproval()->count();

        $this->command->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Total Summaries', $total, '100%'],
                ['Present (Hadir)', $present, $this->percentage($present, $total)],
                ['Absent (Tidak Hadir)', $absent, $this->percentage($absent, $total)],
                ['Late (Terlambat)', $late, $this->percentage($late, $total)],
                ['Has Overtime', $hasOvertime, $this->percentage($hasOvertime, $total)],
                ['Approved Overtime', $approvedOvertime, $this->percentage($approvedOvertime, $total)],
                ['Pending Approval', $pendingApproval, $this->percentage($pendingApproval, $total)],
            ]
        );

        // Status breakdown
        $this->command->info("\n📋 Status Breakdown:");
        $statuses = AttendanceSummary::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('total', 'desc')
            ->get();

        $statusData = [];
        foreach ($statuses as $status) {
            $statusData[] = [
                ucfirst($status->status),
                $status->total,
                $this->percentage($status->total, $total)
            ];
        }

        $this->command->table(['Status', 'Count', 'Percentage'], $statusData);

        // Time statistics
        $avgWorkHours = AttendanceSummary::avg('total_work_minutes') / 60;
        $totalOvertimeHours = AttendanceSummary::sum('approved_overtime_minutes') / 60;

        $this->command->info("\n⏱️  Time Statistics:");
        $this->command->info("   Average Work Hours: " . round($avgWorkHours, 2) . " hours/day");
        $this->command->info("   Total Approved Overtime: " . round($totalOvertimeHours, 2) . " hours");

        // Sample summaries
        $this->command->info("\n📄 Sample Summaries:");
        AttendanceSummary::with('employee')
            ->latest('date')
            ->limit(5)
            ->get()
            ->each(function ($summary) {
                $status = $summary->status_label;
                $overtime = $summary->overtime_minutes > 0 ? " | OT: {$summary->formatted_overtime}" : '';
                $approved = $summary->overtime_approved ? ' ✓' : '';

                $this->command->info(
                    "   {$summary->employee->full_name} - {$summary->formatted_date} | " .
                    "{$status} | {$summary->formatted_total_work}{$overtime}{$approved}"
                );
            });

        $this->command->info("\n✨ Seeding completed successfully!");
    }

    /**
     * Calculate percentage
     */
    private function percentage($value, $total): string
    {
        if ($total == 0)
            return '0%';
        return round(($value / $total) * 100, 1) . '%';
    }
}