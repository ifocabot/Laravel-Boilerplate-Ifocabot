<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seedPermissions();
        $this->seedRoles();
        $this->seedUsers();

        $this->command->info('✅ Users, Roles & Permissions seeded successfully!');
    }

    /**
     * Seed permissions
     */
    protected function seedPermissions(): void
    {
        $permissions = [
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Role Management
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Employee Management
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',
            'employees.view_sensitive',
            'employees.export',

            // Department Management
            'departments.view',
            'departments.create',
            'departments.edit',
            'departments.delete',

            // Position Management
            'positions.view',
            'positions.create',
            'positions.edit',
            'positions.delete',

            // Level Management
            'levels.view',
            'levels.create',
            'levels.edit',
            'levels.delete',

            // Location Management
            'locations.view',
            'locations.create',
            'locations.edit',
            'locations.delete',

            // Leave Management
            'leaves.view',
            'leaves.create',
            'leaves.edit',
            'leaves.delete',
            'leaves.approve',
            'leaves.view_all',

            // Attendance Management
            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.delete',
            'attendance.view_all',
            'attendance.process',

            // Schedule Management
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'schedules.delete',

            // Overtime Management
            'overtime.view',
            'overtime.create',
            'overtime.approve',
            'overtime.view_all',

            // Payroll Management
            'payroll.view',
            'payroll.create',
            'payroll.edit',
            'payroll.delete',
            'payroll.process',
            'payroll.lock',
            'payroll.view_all',

            // Approval Workflow
            'workflows.view',
            'workflows.create',
            'workflows.edit',
            'workflows.delete',

            // Reports
            'reports.view',
            'reports.export',

            // Settings
            'settings.view',
            'settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('  🔑 Permissions: ' . count($permissions) . ' created');
    }

    /**
     * Seed roles with permissions
     */
    protected function seedRoles(): void
    {
        // Super Admin - Full access
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - Most access except sensitive settings
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'roles.view',
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.view_sensitive',
            'employees.export',
            'departments.view',
            'departments.create',
            'departments.edit',
            'positions.view',
            'positions.create',
            'positions.edit',
            'levels.view',
            'levels.create',
            'levels.edit',
            'locations.view',
            'locations.create',
            'locations.edit',
            'leaves.view',
            'leaves.create',
            'leaves.edit',
            'leaves.approve',
            'leaves.view_all',
            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.view_all',
            'attendance.process',
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'overtime.view',
            'overtime.create',
            'overtime.approve',
            'overtime.view_all',
            'payroll.view',
            'payroll.create',
            'payroll.edit',
            'payroll.process',
            'payroll.view_all',
            'workflows.view',
            'workflows.create',
            'workflows.edit',
            'reports.view',
            'reports.export',
            'settings.view',
        ]);

        // HR Manager
        $hrManager = Role::firstOrCreate(['name' => 'hr-manager', 'guard_name' => 'web']);
        $hrManager->syncPermissions([
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.view_sensitive',
            'employees.export',
            'departments.view',
            'positions.view',
            'levels.view',
            'locations.view',
            'leaves.view',
            'leaves.create',
            'leaves.edit',
            'leaves.approve',
            'leaves.view_all',
            'attendance.view',
            'attendance.create',
            'attendance.edit',
            'attendance.view_all',
            'attendance.process',
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'overtime.view',
            'overtime.approve',
            'overtime.view_all',
            'payroll.view',
            'payroll.view_all',
            'reports.view',
            'reports.export',
        ]);

        // Manager - Department Manager
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'employees.view',
            'departments.view',
            'positions.view',
            'levels.view',
            'locations.view',
            'leaves.view',
            'leaves.create',
            'leaves.approve',
            'attendance.view',
            'attendance.view_all',
            'schedules.view',
            'overtime.view',
            'overtime.create',
            'overtime.approve',
            'reports.view',
        ]);

        // Supervisor
        $supervisor = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $supervisor->syncPermissions([
            'employees.view',
            'leaves.view',
            'leaves.create',
            'leaves.approve',
            'attendance.view',
            'schedules.view',
            'overtime.view',
            'overtime.create',
        ]);

        // Employee - Basic access
        $employee = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employee->syncPermissions([
            'leaves.view',
            'leaves.create',
            'attendance.view',
            'schedules.view',
            'overtime.view',
            'overtime.create',
        ]);

        $this->command->info('  👤 Roles: 6 created (super-admin, admin, hr-manager, manager, supervisor, employee)');
    }

    /**
     * Seed default users
     */
    protected function seedUsers(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@company.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        // Regular Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->syncRoles(['admin']);

        // HR Manager
        $hr = User::firstOrCreate(
            ['email' => 'hr@company.com'],
            [
                'name' => 'HR Manager',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $hr->syncRoles(['hr-manager']);

        $this->command->info('  👥 Users: 3 created (superadmin, admin, hr)');
        $this->command->info('     📧 Login: superadmin@company.com / password');
        $this->command->info('     📧 Login: admin@company.com / password');
        $this->command->info('     📧 Login: hr@company.com / password');
    }
}
