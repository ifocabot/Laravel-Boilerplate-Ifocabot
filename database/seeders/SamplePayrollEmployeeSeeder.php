<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\PayrollComponent;
use App\Models\EmployeePayrollComponent;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\Level;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SamplePayrollEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creating Sample Employee with Payroll Data...');
        $this->command->newLine();

        // Step 1: Create/Update Payroll Components
        $this->command->info('📋 Creating Payroll Components...');
        $this->createPayrollComponents();

        // Step 2: Create Sample Employee
        $this->command->info('👤 Creating Sample Employee...');
        $employee = $this->createSampleEmployee();

        // Step 3: Assign Payroll Components to Employee
        $this->command->info('💰 Assigning Payroll Components...');
        $this->assignPayrollComponents($employee);

        // Summary
        $this->command->newLine();
        $this->showPayrollSummary($employee);

        $this->command->info('✨ Sample employee with payroll data created successfully!');
    }

    private function createPayrollComponents(): void
    {
        $components = [
            // ========== EARNINGS ==========
            [
                'code' => 'BASIC_SALARY',
                'name' => 'Gaji Pokok',
                'type' => 'earning',
                'category' => 'basic_salary',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'TUNJ_KPI',
                'name' => 'Tunjangan KPI',
                'type' => 'earning',
                'category' => 'variable_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'display_order' => 2,
            ],
            [
                'code' => 'TUNJ_JABATAN',
                'name' => 'Tunjangan Jabatan',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => true,
                'display_order' => 3,
            ],
            [
                'code' => 'TUNJ_OPERASIONAL',
                'name' => 'Tunjangan Operasional',
                'type' => 'earning',
                'category' => 'fixed_allowance',
                'calculation_type' => 'fixed',
                'is_taxable' => true,
                'is_bpjs_base' => false,
                'display_order' => 4,
            ],

            // ========== DEDUCTIONS (Employee) ==========
            [
                'code' => 'JHT_EMPLOYEE',
                'name' => 'JHT Karyawan',
                'type' => 'deduction',
                'category' => 'statutory',
                'calculation_type' => 'percentage',
                'percentage_value' => 2.00,
                'calculation_notes' => '2% dari BPJS Base',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 10,
            ],
            [
                'code' => 'JP_EMPLOYEE',
                'name' => 'Jaminan Pensiun Karyawan',
                'type' => 'deduction',
                'category' => 'statutory',
                'calculation_type' => 'percentage',
                'percentage_value' => 1.00,
                'calculation_notes' => '1% dari BPJS Base',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 11,
            ],
            [
                'code' => 'PPH21',
                'name' => 'PPh 21',
                'type' => 'deduction',
                'category' => 'statutory',
                'calculation_type' => 'fixed', // Will be calculated by controller
                'calculation_notes' => 'Dihitung berdasarkan penghasilan kena pajak (calculated by system)',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 12,
            ],

            // ========== BENEFITS (Company) - stored as earnings but flagged ==========
            [
                'code' => 'JKK_COMPANY',
                'name' => 'JKK Perusahaan',
                'type' => 'earning', // Company benefit shown as earning
                'category' => 'fixed_allowance',
                'calculation_type' => 'percentage',
                'percentage_value' => 0.24,
                'calculation_notes' => '0.24% dari BPJS Base (ditanggung perusahaan)',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 20,
            ],
            [
                'code' => 'JKM_COMPANY',
                'name' => 'JKM Perusahaan',
                'type' => 'earning', // Company benefit shown as earning
                'category' => 'fixed_allowance',
                'calculation_type' => 'percentage',
                'percentage_value' => 0.30,
                'calculation_notes' => '0.3% dari BPJS Base (ditanggung perusahaan)',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 21,
            ],
            [
                'code' => 'JHT_COMPANY',
                'name' => 'JHT Perusahaan',
                'type' => 'earning', // Company benefit shown as earning
                'category' => 'fixed_allowance',
                'calculation_type' => 'percentage',
                'percentage_value' => 3.70,
                'calculation_notes' => '3.7% dari BPJS Base (ditanggung perusahaan)',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 22,
            ],
            [
                'code' => 'JP_COMPANY',
                'name' => 'Jaminan Pensiun Perusahaan',
                'type' => 'earning', // Company benefit shown as earning
                'category' => 'fixed_allowance',
                'calculation_type' => 'percentage',
                'percentage_value' => 2.00,
                'calculation_notes' => '2% dari BPJS Base (ditanggung perusahaan)',
                'is_taxable' => false,
                'is_bpjs_base' => false,
                'display_order' => 23,
            ],
        ];

        foreach ($components as $data) {
            $component = PayrollComponent::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, [
                    'is_active' => true,
                    'show_on_slip' => true,
                ])
            );

            $status = $component->wasRecentlyCreated ? '✅ Created' : '🔄 Updated';
            $this->command->info("  {$status}: {$component->name} ({$component->code})");
        }
    }

    private function createSampleEmployee(): Employee
    {
        // Get existing department or create one with correct fields
        $department = Department::first();
        if (!$department) {
            $department = Department::create([
                'name' => 'Information Technology',
            ]);
        }

        // Get existing position or create with correct fields (no 'code' field)
        $position = Position::first();
        if (!$position) {
            $position = Position::create([
                'department_id' => $department->id,
                'name' => 'Senior Developer',
            ]);
        }

        $level = Level::first();

        // Create User first
        $user = User::updateOrCreate(
            ['email' => 'sample.payroll@company.com'],
            [
                'name' => 'Sample Payroll Employee',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Create Employee with correct field names
        $employee = Employee::updateOrCreate(
            ['nik' => 'EMP-2024-SAMPLE'],
            [
                'user_id' => $user->id,
                'full_name' => 'Sample Payroll Employee',
                'email_corporate' => 'sample.payroll@company.com',
                'phone_number' => '081234567890',
                'gender' => 'male',
                'date_of_birth' => '1990-01-15',
                'place_of_birth' => 'Jakarta',
                'religion' => 'islam',
                'marital_status' => 'married',
                'join_date' => '2022-01-01',
                'status' => 'active',
            ]
        );

        $this->command->info("  ✅ Employee: {$employee->full_name} ({$employee->nik})");

        return $employee;
    }

    private function assignPayrollComponents(Employee $employee): void
    {
        $effectiveFrom = Carbon::now()->startOfMonth();

        $assignments = [
            // Earnings
            ['code' => 'BASIC_SALARY', 'amount' => 4588500],
            ['code' => 'TUNJ_KPI', 'amount' => 250000],
            ['code' => 'TUNJ_JABATAN', 'amount' => 1000000],
            ['code' => 'TUNJ_OPERASIONAL', 'amount' => 1000000],

            // Deductions (amount = 0 for percentage-based, will be calculated)
            ['code' => 'JHT_EMPLOYEE', 'amount' => 0],
            ['code' => 'JP_EMPLOYEE', 'amount' => 0],
            ['code' => 'PPH21', 'amount' => 0],

            // Benefits (amount = 0 for percentage-based)
            ['code' => 'JKK_COMPANY', 'amount' => 0],
            ['code' => 'JKM_COMPANY', 'amount' => 0],
            ['code' => 'JHT_COMPANY', 'amount' => 0],
            ['code' => 'JP_COMPANY', 'amount' => 0],
        ];

        foreach ($assignments as $assignment) {
            $component = PayrollComponent::where('code', $assignment['code'])->first();

            if (!$component) {
                $this->command->warn("  ⚠️ Component not found: {$assignment['code']}");
                continue;
            }

            // Deactivate existing component of same type
            EmployeePayrollComponent::where('employee_id', $employee->id)
                ->where('component_id', $component->id)
                ->where('is_active', true)
                ->update(['is_active' => false, 'effective_to' => $effectiveFrom->copy()->subDay()]);

            // Create new assignment
            $empComponent = EmployeePayrollComponent::create([
                'employee_id' => $employee->id,
                'component_id' => $component->id,
                'amount' => $assignment['amount'],
                'effective_from' => $effectiveFrom,
                'effective_to' => null,
                'is_active' => true,
                'is_recurring' => true,
            ]);

            $amountDisplay = $assignment['amount'] > 0
                ? 'Rp ' . number_format($assignment['amount'], 0, ',', '.')
                : 'Auto-calculated';

            $this->command->info("  ✅ {$component->name}: {$amountDisplay}");
        }
    }

    private function showPayrollSummary(Employee $employee): void
    {
        $this->command->info('📊 Payroll Summary:');
        $this->command->newLine();

        // Calculate expected values
        $basicSalary = 4588500;
        $bpjsBase = 5550000; // As per user's calculation

        $earnings = [
            ['Gaji Pokok', 4588500],
            ['Tunjangan KPI', 250000],
            ['Tunjangan Jabatan', 1000000],
            ['Tunjangan Operasional', 1000000],
        ];
        $totalEarnings = array_sum(array_column($earnings, 1));

        $deductions = [
            ['JHT Karyawan (2%)', round($bpjsBase * 0.02)],
            ['JP Karyawan (1%)', round($bpjsBase * 0.01)],
            ['PPh 21', 92019], // From user's data
        ];
        $totalDeductions = array_sum(array_column($deductions, 1));

        $benefits = [
            ['JKK Perusahaan (0.24%)', round($bpjsBase * 0.0024)],
            ['JKM Perusahaan (0.3%)', round($bpjsBase * 0.003)],
            ['JHT Perusahaan (3.7%)', round($bpjsBase * 0.037)],
            ['JP Perusahaan (2%)', round($bpjsBase * 0.02)],
        ];

        $this->command->table(
            ['EARNINGS', 'Amount'],
            array_map(fn($e) => [$e[0], 'Rp ' . number_format($e[1], 0, ',', '.')], $earnings)
        );

        $this->command->table(
            ['DEDUCTIONS', 'Amount'],
            array_map(fn($d) => [$d[0], 'Rp ' . number_format($d[1], 0, ',', '.')], $deductions)
        );

        $this->command->table(
            ['BENEFITS (Company)', 'Amount'],
            array_map(fn($b) => [$b[0], 'Rp ' . number_format($b[1], 0, ',', '.')], $benefits)
        );

        $netSalary = $totalEarnings - $totalDeductions;

        $this->command->newLine();
        $this->command->info("📈 Total Earnings: Rp " . number_format($totalEarnings, 0, ',', '.'));
        $this->command->info("📉 Total Deductions: Rp " . number_format($totalDeductions, 0, ',', '.'));
        $this->command->info("💵 Net Salary: Rp " . number_format($netSalary, 0, ',', '.'));
    }
}
