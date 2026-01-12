<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\EmployeeCareer;
use App\Models\EmployeeContract;
use App\Models\EmployeeFamily;
use App\Models\EmployeeSensitiveData;
use App\Models\EmployeeLeaveBalance;
use App\Models\EmployeePayrollComponent;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;
use App\Models\Location;
use App\Models\Shift;
use App\Models\LeaveType;
use App\Models\PayrollComponent;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * Demo Seeder - Creates a single demo account with data for all modules
 * 
 * Usage:
 *   php artisan migrate:fresh --seed --seeder=DemoSeeder
 *   
 * Or reset only demo data:
 *   php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    /**
     * Demo employee configuration
     */
    protected array $demoEmployee = [
        'email' => 'demo@company.com',
        'password' => 'password123',
        'name' => 'Demo Employee',
        'nik' => 'EMP00001',
        'gender' => 'male',
        'phone' => '081234567890',
        'place_of_birth' => 'Jakarta',
        'religion' => 'islam',
        'marital_status' => 'married',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('🚀 Starting Demo Seeder...');
        $this->command->info('================================');

        // 1. Seed master data (departments, positions, levels, locations, holidays)
        $this->call(MasterDataSeeder::class);

        // 2. Seed users, roles & permissions
        $this->call(UserRoleSeeder::class);

        // 3. Seed shifts
        $this->call(ShiftSeeder::class);

        // 4. Seed leave types
        $this->call(LeaveTypeSeeder::class);

        // 5. Seed approval workflows
        $this->call(ApprovalWorkflowSeeder::class);

        // 6. Seed payroll components
        $this->call(PayrollComponentSeeder::class);

        // 7. Seed document categories
        $this->call(DocumentCategorySeeder::class);

        // 8. Seed training data (skills, certifications)
        $this->call(TrainingSeeder::class);

        // 9. Create demo employee with full data
        $employee = $this->createDemoEmployee();

        // 10. Create attendance data
        $this->call(DemoAttendanceSeeder::class);

        $this->command->info('');
        $this->command->info('================================');
        $this->command->info('✅ Demo Seeder completed!');
        $this->command->info('');
        $this->command->info('📧 Demo Login:');
        $this->command->info('   Email: ' . $this->demoEmployee['email']);
        $this->command->info('   Password: ' . $this->demoEmployee['password']);
        $this->command->info('');
    }

    /**
     * Create the demo employee with all related data
     */
    protected function createDemoEmployee(): Employee
    {
        $this->command->info('👤 Creating demo employee...');

        // Get master data
        $itDept = Department::where('code', 'IT')->first();
        $position = Position::where('name', 'Software Engineer')->first();
        $staffLevel = Level::where('grade_code', 'STF')->first();
        $headOffice = Location::where('code', 'JKT-HQ')->first();

        // Create user
        $user = User::updateOrCreate(
            ['email' => $this->demoEmployee['email']],
            [
                'name' => $this->demoEmployee['name'],
                'password' => Hash::make($this->demoEmployee['password']),
                'is_active' => true,
            ]
        );
        $user->syncRoles(['employee']);

        // Create employee
        $joinDate = Carbon::now()->subYears(2)->subMonths(3);
        $employee = Employee::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nik' => $this->demoEmployee['nik'],
                'full_name' => $this->demoEmployee['name'],
                'email_corporate' => $this->demoEmployee['email'],
                'phone_number' => $this->demoEmployee['phone'],
                'gender' => $this->demoEmployee['gender'],
                'place_of_birth' => $this->demoEmployee['place_of_birth'],
                'date_of_birth' => Carbon::now()->subYears(28)->subDays(rand(0, 365)),
                'religion' => $this->demoEmployee['religion'],
                'marital_status' => $this->demoEmployee['marital_status'],
                'join_date' => $joinDate,
                'status' => 'active',
            ]
        );

        $this->command->info('   ✅ User & Employee created');

        // Create career record
        EmployeeCareer::updateOrCreate(
            ['employee_id' => $employee->id, 'is_current' => true],
            [
                'department_id' => $itDept?->id,
                'position_id' => $position?->id,
                'level_id' => $staffLevel?->id,
                'branch_id' => $headOffice?->id,
                'start_date' => $joinDate,
                'is_active' => true,
                'notes' => 'Demo employee - initial placement',
            ]
        );
        $this->command->info('   ✅ Career record created');

        // Create contract
        EmployeeContract::updateOrCreate(
            ['employee_id' => $employee->id, 'is_active' => true],
            [
                'contract_number' => 'CTR-' . date('Y') . '-00001',
                'type' => 'pkwtt',
                'start_date' => $joinDate,
                'end_date' => null, // Permanent
                'is_active' => true,
                'notes' => 'Karyawan tetap',
            ]
        );
        $this->command->info('   ✅ Contract created');

        // Create family member
        EmployeeFamily::updateOrCreate(
            ['employee_id' => $employee->id, 'relation' => 'spouse'],
            [
                'name' => 'Demo Spouse',
                'relation' => 'spouse',
                'phone' => '081234567891',
                'is_emergency_contact' => true,
                'is_bpjs_dependent' => true,
            ]
        );
        $this->command->info('   ✅ Family member created');

        // Create sensitive data
        EmployeeSensitiveData::updateOrCreate(
            ['employee_id' => $employee->id],
            [
                'id_card_number' => '3171234567890001',
                'npwp_number' => '12.345.678.9-012.345',
                'bpjs_tk_number' => 'BPJSTK123456789',
                'bpjs_kes_number' => 'BPJSKES123456789',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_holder' => $this->demoEmployee['name'],
            ]
        );
        $this->command->info('   ✅ Sensitive data created');

        // Create leave balances
        $leaveTypes = LeaveType::where('is_active', true)->get();
        foreach ($leaveTypes as $leaveType) {
            EmployeeLeaveBalance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'leave_type_id' => $leaveType->id,
                    'year' => Carbon::now()->year,
                ],
                [
                    'quota' => $leaveType->default_quota,
                    'used' => 0,
                    'carry_forward' => 0,
                ]
            );
        }
        $this->command->info('   ✅ Leave balances created');

        // Assign payroll components
        $this->assignPayrollComponents($employee);

        $this->command->info('   ✅ Demo employee fully configured!');

        return $employee;
    }

    /**
     * Assign payroll components to employee
     */
    protected function assignPayrollComponents(Employee $employee): void
    {
        $components = [
            'BASIC_SALARY' => 8000000, // Gaji pokok 8 juta
            'MEAL' => null,            // Use default rate
            'TRANSPORT' => null,       // Use default rate
            'HEALTH' => 500000,        // Tunjangan kesehatan
            'COMM' => 200000,          // Tunjangan komunikasi
        ];

        foreach ($components as $code => $amount) {
            $component = PayrollComponent::where('code', $code)->first();
            if ($component) {
                EmployeePayrollComponent::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'component_id' => $component->id,
                        'is_active' => true,
                    ],
                    [
                        'amount' => $amount ?? $component->default_amount ?? $component->rate_per_day ?? 0,
                        'effective_from' => $employee->join_date,
                        'is_recurring' => true,
                        'is_override' => $amount !== null,
                        'original_amount' => $component->default_amount,
                    ]
                );
            }
        }

        $this->command->info('   ✅ Payroll components assigned');
    }
}
