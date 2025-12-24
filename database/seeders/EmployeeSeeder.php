<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;
use App\Models\Location;
use App\Models\EmployeeCareer;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Get master data (created by MasterDataSeeder)
        $bodDept = Department::where('code', 'BOD')->first();
        $hrDept = Department::where('code', 'HR')->first();
        $itDept = Department::where('code', 'IT')->first();
        $finDept = Department::where('code', 'FIN')->first();
        $opsDept = Department::where('code', 'OPS')->first();

        // Fallback to create if not exists (for standalone seeder run)
        if (!$hrDept) {
            $hrDept = Department::firstOrCreate(['code' => 'HR'], ['name' => 'Human Resource']);
            $itDept = Department::firstOrCreate(['code' => 'IT'], ['name' => 'Information Technology']);
            $finDept = Department::firstOrCreate(['code' => 'FIN'], ['name' => 'Finance & Accounting']);
            $opsDept = Department::firstOrCreate(['code' => 'OPS'], ['name' => 'Operations']);
            $bodDept = Department::firstOrCreate(['code' => 'BOD'], ['name' => 'Direksi']);
        }

        // Get levels
        $directorLevel = Level::where('grade_code', 'DIR')->first() ?? Level::firstOrCreate(['grade_code' => 'DIR'], ['name' => 'Director', 'approval_order' => 7]);
        $gmLevel = Level::where('grade_code', 'GM')->first() ?? Level::firstOrCreate(['grade_code' => 'GM'], ['name' => 'General Manager', 'approval_order' => 6]);
        $managerLevel = Level::where('grade_code', 'MGR')->first() ?? Level::firstOrCreate(['grade_code' => 'MGR'], ['name' => 'Manager', 'approval_order' => 5]);
        $supervisorLevel = Level::where('grade_code', 'SPV')->first() ?? Level::firstOrCreate(['grade_code' => 'SPV'], ['name' => 'Supervisor', 'approval_order' => 4]);
        $seniorLevel = Level::where('grade_code', 'SR')->first() ?? Level::firstOrCreate(['grade_code' => 'SR'], ['name' => 'Senior Staff', 'approval_order' => 3]);
        $staffLevel = Level::where('grade_code', 'STF')->first() ?? Level::firstOrCreate(['grade_code' => 'STF'], ['name' => 'Staff', 'approval_order' => 2]);
        $juniorLevel = Level::where('grade_code', 'JR')->first() ?? Level::firstOrCreate(['grade_code' => 'JR'], ['name' => 'Junior Staff', 'approval_order' => 1]);

        // Get positions (use name, not code - Position model doesn't have code)
        $dirPos = Position::where('name', 'Director')->first() ?? Position::firstOrCreate(['name' => 'Director']);
        $gmPos = Position::where('name', 'General Manager')->first() ?? Position::firstOrCreate(['name' => 'General Manager']);
        $hrManager = Position::where('name', 'HR Manager')->first() ?? Position::firstOrCreate(['name' => 'HR Manager']);
        $hrStaff = Position::where('name', 'HR Staff')->first() ?? Position::firstOrCreate(['name' => 'HR Staff']);
        $itManager = Position::where('name', 'IT Manager')->first() ?? Position::firstOrCreate(['name' => 'IT Manager']);
        $developer = Position::where('name', 'Software Engineer')->first() ?? Position::firstOrCreate(['name' => 'Software Engineer']);
        $finManager = Position::where('name', 'Finance Manager')->first() ?? Position::firstOrCreate(['name' => 'Finance Manager']);
        $accountant = Position::where('name', 'Accountant')->first() ?? Position::firstOrCreate(['name' => 'Accountant']);
        $opsManager = Position::where('name', 'Operations Manager')->first() ?? Position::firstOrCreate(['name' => 'Operations Manager']);
        $opsStaff = Position::where('name', 'Operations Staff')->first() ?? Position::firstOrCreate(['name' => 'Operations Staff']);

        // Get location
        $headOffice = Location::where('code', 'JKT-HQ')->first() ?? Location::where('code', 'HO')->first() ??
            Location::firstOrCreate(['code' => 'HO'], ['name' => 'Head Office', 'address' => 'Jakarta']);

        // ⭐ Sample employees data - including executives
        $employees = [
            // === EXECUTIVES ===
            [
                'full_name' => 'Robert Tanudisastro',
                'email' => 'robert.tanudisastro@company.com',
                'gender' => 'male',
                'department' => $bodDept ?? $hrDept,
                'position' => $dirPos,
                'level' => $directorLevel,
                'role' => 'super-admin',
                'is_executive' => true,
            ],
            [
                'full_name' => 'Christine Hartono',
                'email' => 'christine.hartono@company.com',
                'gender' => 'female',
                'department' => $bodDept ?? $hrDept,
                'position' => $gmPos,
                'level' => $gmLevel,
                'role' => 'admin',
                'is_executive' => true,
            ],

            // === HR Department ===
            [
                'full_name' => 'Ahmad Wijaya',
                'email' => 'ahmad.wijaya@company.com',
                'gender' => 'male',
                'department' => $hrDept,
                'position' => $hrManager,
                'level' => $managerLevel,
                'role' => 'hr-manager',
                'is_manager' => true,
            ],
            [
                'full_name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@company.com',
                'gender' => 'female',
                'department' => $hrDept,
                'position' => $hrStaff,
                'level' => $staffLevel,
                'role' => 'employee',
            ],

            // === IT Department ===
            [
                'full_name' => 'Budi Santoso',
                'email' => 'budi.santoso@company.com',
                'gender' => 'male',
                'department' => $itDept,
                'position' => $itManager,
                'level' => $managerLevel,
                'role' => 'manager',
                'is_manager' => true,
            ],
            [
                'full_name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@company.com',
                'gender' => 'female',
                'department' => $itDept,
                'position' => $developer,
                'level' => $seniorLevel,
                'role' => 'supervisor',
            ],
            [
                'full_name' => 'Eko Prasetyo',
                'email' => 'eko.prasetyo@company.com',
                'gender' => 'male',
                'department' => $itDept,
                'position' => $developer,
                'level' => $staffLevel,
                'role' => 'employee',
            ],
            [
                'full_name' => 'Fajar Nugroho',
                'email' => 'fajar.nugroho@company.com',
                'gender' => 'male',
                'department' => $itDept,
                'position' => $developer,
                'level' => $juniorLevel,
                'role' => 'employee',
            ],

            // === Finance Department ===
            [
                'full_name' => 'Fitri Handayani',
                'email' => 'fitri.handayani@company.com',
                'gender' => 'female',
                'department' => $finDept,
                'position' => $finManager,
                'level' => $managerLevel,
                'role' => 'manager',
                'is_manager' => true,
            ],
            [
                'full_name' => 'Gunawan Hidayat',
                'email' => 'gunawan.hidayat@company.com',
                'gender' => 'male',
                'department' => $finDept,
                'position' => $accountant,
                'level' => $seniorLevel,
                'role' => 'employee',
            ],
            [
                'full_name' => 'Herlina Susanti',
                'email' => 'herlina.susanti@company.com',
                'gender' => 'female',
                'department' => $finDept,
                'position' => $accountant,
                'level' => $staffLevel,
                'role' => 'employee',
            ],

            // === Operations Department ===
            [
                'full_name' => 'Hendra Kusuma',
                'email' => 'hendra.kusuma@company.com',
                'gender' => 'male',
                'department' => $opsDept,
                'position' => $opsManager,
                'level' => $managerLevel,
                'role' => 'manager',
                'is_manager' => true,
            ],
            [
                'full_name' => 'Indah Permata',
                'email' => 'indah.permata@company.com',
                'gender' => 'female',
                'department' => $opsDept,
                'position' => $opsStaff,
                'level' => $supervisorLevel,
                'role' => 'supervisor',
            ],
            [
                'full_name' => 'Joko Widodo',
                'email' => 'joko.widodo@company.com',
                'gender' => 'male',
                'department' => $opsDept,
                'position' => $opsStaff,
                'level' => $staffLevel,
                'role' => 'employee',
            ],
        ];

        $nikCounter = 1;
        $religions = ['islam', 'kristen', 'katolik', 'hindu', 'buddha'];
        $maritalStatuses = ['single', 'married', 'widow', 'widower'];
        $birthPlaces = ['Jakarta', 'Bandung', 'Surabaya', 'Yogyakarta', 'Semarang', 'Medan', 'Makassar'];

        foreach ($employees as $empData) {
            // Create User account
            $user = User::firstOrCreate(
                ['email' => $empData['email']],
                [
                    'name' => $empData['full_name'],
                    'password' => Hash::make('password123'),
                ]
            );

            // Assign role if available
            if (method_exists($user, 'assignRole')) {
                try {
                    $role = $empData['role'] ?? 'employee';
                    $user->syncRoles([$role]);
                } catch (\Exception $e) {
                    // Role might not exist
                }
            }

            // Create Employee
            $employee = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'nik' => 'EMP' . str_pad($nikCounter, 5, '0', STR_PAD_LEFT),
                    'full_name' => $empData['full_name'],
                    'email_corporate' => $empData['email'],
                    'phone_number' => '08' . str_pad(rand(10000000, 99999999), 10, '0', STR_PAD_LEFT),
                    'gender' => $empData['gender'],
                    'place_of_birth' => $birthPlaces[array_rand($birthPlaces)],
                    'date_of_birth' => now()->subYears(rand(25, 45))->subDays(rand(0, 365)),
                    'religion' => $religions[array_rand($religions)],
                    'marital_status' => $maritalStatuses[array_rand($maritalStatuses)],
                    'join_date' => now()->subYears(rand(1, 5))->subDays(rand(0, 365)),
                    'status' => 'active',
                ]
            );

            // Create Career record (current position)
            $department = $empData['department'];
            EmployeeCareer::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'is_current' => true,
                ],
                [
                    'department_id' => $department ? $department->id : null,
                    'position_id' => $empData['position']?->id,
                    'level_id' => $empData['level']?->id,
                    'branch_id' => $headOffice?->id,
                    'start_date' => $employee->join_date,
                    'is_active' => true,
                    'notes' => 'Initial placement',
                ]
            );

            // Set as department manager if flagged
            if (!empty($empData['is_manager']) && $department) {
                $department->update(['manager_id' => $user->id]);
            }

            $nikCounter++;
        }

        $this->command->info('  👥 Employees: ' . count($employees) . ' created with user accounts and careers');

        // Now set manager relationships (after all employees are created)
        $this->command->info('  🔗 Setting manager relationships...');

        // Get managers and assign to staff level employees
        $departmentManagers = [
            'HR' => 'Ahmad Wijaya',
            'IT' => 'Budi Santoso',
            'FIN' => 'Fitri Handayani',
            'OPS' => 'Hendra Kusuma',
        ];

        // Executives (GM) - managers report to GM
        $gm = Employee::where('full_name', 'Christine Hartono')->first();
        $director = Employee::where('full_name', 'Robert Tanudisastro')->first();

        foreach ($departmentManagers as $deptCode => $managerName) {
            $manager = Employee::where('full_name', $managerName)->first();
            $dept = Department::where('code', $deptCode)->first();

            if ($manager && $dept) {
                // Update all non-manager employees in this department - set their manager
                $staffCareers = EmployeeCareer::where('department_id', $dept->id)
                    ->where('is_current', true)
                    ->whereHas('employee', function ($q) use ($managerName) {
                        $q->where('full_name', '!=', $managerName);
                    })
                    ->get();

                foreach ($staffCareers as $career) {
                    $career->update(['manager_id' => $manager->id]);
                }

                // Manager reports to GM
                if ($gm) {
                    $managerCareer = EmployeeCareer::where('employee_id', $manager->id)
                        ->where('is_current', true)
                        ->first();
                    if ($managerCareer) {
                        $managerCareer->update(['manager_id' => $gm->id]);
                    }
                }

                $this->command->info("    └ {$staffCareers->count()} staff under {$managerName} in {$deptCode}");
            }
        }

        // GM reports to Director
        if ($gm && $director) {
            $gmCareer = EmployeeCareer::where('employee_id', $gm->id)
                ->where('is_current', true)
                ->first();
            if ($gmCareer) {
                $gmCareer->update(['manager_id' => $director->id]);
            }
            $this->command->info("    └ GM reports to Director");
        }

        $this->command->info('✅ Manager relationships configured');
    }
}
