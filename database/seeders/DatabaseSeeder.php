<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Run with: php artisan db:seed
     * Fresh seed: php artisan migrate:fresh --seed
     * 
     * For DEMO (single account with all modules):
     *   php artisan migrate:fresh --seed --seeder=DemoSeeder
     * 
     * To customize attendance data, edit DemoAttendanceSeeder.php $config array
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🚀 Starting Database Seeding...');
        $this->command->info('================================');

        // Run seeders in dependency order
        $this->call([
                // 1. Master Data (no dependencies)
            MasterDataSeeder::class,        // Departments, Positions, Levels, Locations, Holidays

                // 2. Users & Roles (no dependencies)
            UserRoleSeeder::class,          // Users, Roles, Permissions

                // 3. Employee Data (depends on master data)
            EmployeeSeeder::class,          // Employees with careers

                // 4. Shifts & Schedules
            ShiftSeeder::class,             // Work shifts

                // 5. Leave & Attendance
            LeaveTypeSeeder::class,         // Leave types

                // 6. Approval Workflows
            ApprovalWorkflowSeeder::class,  // Approval workflows with steps

                // 7. Payroll
            PayrollComponentSeeder::class,  // Payroll components
        ]);

        $this->command->info('');
        $this->command->info('================================');
        $this->command->info('✅ Database seeding completed!');
        $this->command->info('');
        $this->command->info('📧 Default Logins:');
        $this->command->info('   superadmin@company.com / password');
        $this->command->info('   admin@company.com / password');
        $this->command->info('   hr@company.com / password');
        $this->command->info('');
    }
}
