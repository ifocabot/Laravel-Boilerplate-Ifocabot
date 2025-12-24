<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUsersToEmployees extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:sync-employees 
                            {--dry-run : Show what would be synced without making changes}
                            {--force : Force sync even if employee exists}';

    /**
     * The console command description.
     */
    protected $description = 'Sync all Users to Employee records (create missing Employee for each User)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        $users = User::all();
        $this->info("Found {$users->count()} users to process...");

        $created = 0;
        $skipped = 0;
        $linked = 0;

        $this->withProgressBar($users, function ($user) use ($dryRun, $force, &$created, &$skipped, &$linked) {
            // Check if user already has employee
            $existingEmployee = Employee::where('user_id', $user->id)->first();

            if ($existingEmployee && !$force) {
                $skipped++;
                return;
            }

            // Check if employee exists by email (needs linking)
            $employeeByEmail = Employee::where('email_corporate', $user->email)
                ->whereNull('user_id')
                ->first();

            if ($employeeByEmail) {
                if (!$dryRun) {
                    $employeeByEmail->update(['user_id' => $user->id]);
                }
                $linked++;
                return;
            }

            // Create new employee record
            if (!$dryRun) {
                Employee::create([
                    'user_id' => $user->id,
                    'full_name' => $user->name,
                    'email_corporate' => $user->email,
                    'nik' => Employee::generateNik(),
                    'status' => 'active',
                    'join_date' => $user->created_at ?? now(),
                ]);
            }
            $created++;
        });

        $this->newLine(2);
        $this->info('✅ Sync completed!');
        $this->table(
            ['Action', 'Count'],
            [
                ['Created', $created],
                ['Linked', $linked],
                ['Skipped', $skipped],
            ]
        );

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
