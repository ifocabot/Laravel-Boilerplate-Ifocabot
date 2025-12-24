<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OvertimeRequest;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OvertimeRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        OvertimeRequest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🚀 Starting Overtime Requests Seeding...');

        $employees = Employee::where('status', 'active')->get();

        if ($employees->isEmpty()) {
            $this->command->error('❌ No active employees found.');
            return;
        }

        // Get first user as approver
        $approver = User::first();

        if (!$approver) {
            $this->command->error('❌ No users found for approver.');
            return;
        }

        $this->command->info("📊 Found {$employees->count()} active employees");

        $requestsCreated = 0;

        // Create overtime requests for current month and next month
        $months = [
            Carbon::now(),
            Carbon::now()->addMonth(),
        ];

        foreach ($months as $month) {
            $this->command->info("📅 Creating requests for {$month->format('F Y')}...");

            foreach ($employees as $employee) {
                // Each employee has 2-4 overtime requests per month
                $requestCount = rand(2, 4);

                for ($i = 0; $i < $requestCount; $i++) {
                    // Random date in the month
                    $day = rand(1, $month->daysInMonth);
                    $date = $month->copy()->day($day);

                    // Skip weekends
                    if ($date->isWeekend()) {
                        continue;
                    }

                    // Skip past dates for current month
                    if ($date->isPast() && $month->isCurrentMonth()) {
                        continue;
                    }

                    // Random overtime schedule
                    $startHour = rand(17, 19); // 5 PM - 7 PM
                    $durationHours = rand(2, 4); // 2-4 hours

                    $start = Carbon::parse($date->format('Y-m-d') . " {$startHour}:00");
                    $end = $start->copy()->addHours($durationHours);

                    $durationMinutes = $start->diffInMinutes($end);

                    // Random status with weights
                    $statusRand = rand(1, 100);
                    if ($statusRand <= 30) {
                        $status = 'pending';
                        $approverId = null;
                        $approvedAt = null;
                        $approvedMinutes = 0;
                    } elseif ($statusRand <= 85) {
                        $status = 'approved';
                        $approverId = $approver->id;
                        $approvedAt = $date->copy()->subDays(rand(1, 3));
                        $approvedMinutes = $durationMinutes; // 90% full approval
                    } elseif ($statusRand <= 95) {
                        $status = 'rejected';
                        $approverId = $approver->id;
                        $approvedAt = null;
                        $approvedMinutes = 0;
                    } else {
                        $status = 'cancelled';
                        $approverId = null;
                        $approvedAt = null;
                        $approvedMinutes = 0;
                    }

                    $reasons = [
                        'Deploy production urgent',
                        'Deadline project client',
                        'Meeting dengan client',
                        'Maintenance server',
                        'Troubleshooting bug critical',
                        'Preparation untuk presentation',
                        'Training karyawan baru',
                        'Dokumentasi project',
                    ];

                    OvertimeRequest::create([
                        'employee_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                        'start_at' => $start->format('H:i'),
                        'end_at' => $end->format('H:i'),
                        'duration_minutes' => $durationMinutes,
                        'reason' => $reasons[array_rand($reasons)],
                        'work_description' => 'Detail pekerjaan yang akan dilakukan selama overtime',
                        'status' => $status,
                        'approver_id' => $approverId,
                        'approved_at' => $approvedAt,
                        'approved_duration_minutes' => $approvedMinutes,
                        'rejection_note' => $status === 'rejected' ? 'Overtime tidak sesuai dengan kebutuhan' : null,
                        'cancelled_by' => $status === 'cancelled' ? $approver->id : null,
                        'cancelled_at' => $status === 'cancelled' ? now() : null,
                        'cancellation_reason' => $status === 'cancelled' ? 'Pekerjaan sudah selesai' : null,
                    ]);

                    $requestsCreated++;
                }
            }
        }

        $this->command->info("✅ Successfully seeded {$requestsCreated} overtime requests!");
        $this->showStatistics();
    }

    private function showStatistics(): void
    {
        $this->command->info("\n📊 Overtime Requests Statistics:");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $total = OvertimeRequest::count();
        $pending = OvertimeRequest::pending()->count();
        $approved = OvertimeRequest::approved()->count();
        $rejected = OvertimeRequest::rejected()->count();
        $cancelled = OvertimeRequest::cancelled()->count();

        $this->command->table(
            ['Status', 'Count', 'Percentage'],
            [
                ['Total Requests', $total, '100%'],
                ['Pending', $pending, $this->percentage($pending, $total)],
                ['Approved', $approved, $this->percentage($approved, $total)],
                ['Rejected', $rejected, $this->percentage($rejected, $total)],
                ['Cancelled', $cancelled, $this->percentage($cancelled, $total)],
            ]
        );

        $totalHours = OvertimeRequest::sum('duration_minutes') / 60;
        $approvedHours = OvertimeRequest::approved()->sum('approved_duration_minutes') / 60;

        $this->command->info("\n⏱️  Hours Statistics:");
        $this->command->info("   Total Requested: " . round($totalHours, 1) . " hours");
        $this->command->info("   Total Approved: " . round($approvedHours, 1) . " hours");
    }

    private function percentage($value, $total): string
    {
        if ($total == 0)
            return '0%';
        return round(($value / $total) * 100, 1) . '%';
    }
}