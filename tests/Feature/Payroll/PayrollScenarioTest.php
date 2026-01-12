<?php

namespace Tests\Feature\Payroll;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollComponent;
use App\Models\EmployeePayrollComponent;
use App\Models\AttendancePeriodSummary;
use App\Services\Payroll\PayrollCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Phase 5: Golden Payroll Scenarios
 * 
 * These tests validate payroll calculation against expected results.
 * Each scenario represents a real-world case that must pass.
 */
class PayrollScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected PayrollCalculator $calculator;
    protected PayrollPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PayrollCalculator();
        $this->seedTaxData();
    }

    /**
     * Seed required tax compliance data
     */
    protected function seedTaxData(): void
    {
        $this->artisan('db:seed', ['--class' => 'TaxComplianceSeeder']);
    }

    /**
     * Create a test period
     */
    protected function createPeriod(array $overrides = []): PayrollPeriod
    {
        return PayrollPeriod::factory()->create(array_merge([
            'year' => 2024,
            'month' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'status' => 'draft',
            'late_penalty_per_minute' => 1000,
            'standard_monthly_hours' => 173,
            'overtime_multiplier' => 1.5,
            'overtime_hourly_rate' => 10000,
        ], $overrides));
    }

    /**
     * Create test employee with components
     */
    protected function createEmployee(float $basicSalary, array $overrides = []): Employee
    {
        $employee = Employee::factory()->create(array_merge([
            'status' => 'active',
        ], $overrides));

        // Create basic salary component
        $basicComponent = PayrollComponent::firstOrCreate(
            ['code' => 'BASIC_SALARY'],
            [
                'name' => 'Gaji Pokok',
                'type' => 'earning',
                'category' => 'basic_salary',
                'is_taxable' => true,
                'proration_type' => 'none',
            ]
        );

        EmployeePayrollComponent::create([
            'employee_id' => $employee->id,
            'payroll_component_id' => $basicComponent->id,
            'amount' => $basicSalary,
            'effective_date' => '2024-01-01',
            'is_active' => true,
        ]);

        return $employee->fresh(['activePayrollComponents.component', 'sensitiveData', 'currentCareer']);
    }

    /**
     * Create period summary for employee
     */
    protected function createSummary(Employee $employee, PayrollPeriod $period, array $data): AttendancePeriodSummary
    {
        return AttendancePeriodSummary::create(array_merge([
            'employee_id' => $employee->id,
            'payroll_period_id' => $period->id,
            'scheduled_work_days' => 22,
            'present_days' => 22,
            'late_days' => 0,
            'alpha_days' => 0,
            'leave_days' => 0,
            'sick_days' => 0,
            'permission_days' => 0,
            'total_work_minutes' => 22 * 8 * 60,
            'total_late_minutes' => 0,
            'total_approved_overtime_minutes' => 0,
            'is_locked' => true,
        ], $data));
    }

    // ==========================================
    // SCENARIO 1: Full Month, No Issues
    // ==========================================

    /** @test */
    public function scenario_full_month_no_issues()
    {
        $period = $this->createPeriod();
        $employee = $this->createEmployee(5000000);
        $summary = $this->createSummary($employee, $period, [
            'present_days' => 22,
            'scheduled_work_days' => 22,
        ]);

        $slip = $this->calculator->calculateFromPeriodSummary($period, $employee, $summary);

        $this->assertEquals(5000000, $slip->gross_salary);
        $this->assertGreaterThan(0, $slip->net_salary);
        $this->assertEquals(0, $slip->absent_days);
    }

    // ==========================================
    // SCENARIO 2: Late Employee
    // ==========================================

    /** @test */
    public function scenario_late_30_minutes()
    {
        $period = $this->createPeriod(['late_penalty_per_minute' => 1000]);
        $employee = $this->createEmployee(5000000);
        $summary = $this->createSummary($employee, $period, [
            'present_days' => 21,
            'late_days' => 1,
            'total_late_minutes' => 30,
        ]);

        $slip = $this->calculator->calculateFromPeriodSummary($period, $employee, $summary);

        // Should have late deduction
        $lateDeduction = collect($slip->deductions)->firstWhere('code', 'LATE_DEDUCTION');
        $this->assertNotNull($lateDeduction);
        $this->assertEquals(30000, $lateDeduction['amount']); // 30 * 1000
    }

    // ==========================================
    // SCENARIO 3: Overtime
    // ==========================================

    /** @test */
    public function scenario_overtime_10_hours()
    {
        $period = $this->createPeriod([
            'overtime_hourly_rate' => 20000,
            'overtime_multiplier' => 1.5,
        ]);
        $employee = $this->createEmployee(5000000);
        $summary = $this->createSummary($employee, $period, [
            'total_approved_overtime_minutes' => 600, // 10 hours
        ]);

        $slip = $this->calculator->calculateFromPeriodSummary($period, $employee, $summary);

        // Should have overtime earning
        $overtime = collect($slip->earnings)->firstWhere('code', 'OVERTIME');
        $this->assertNotNull($overtime);

        // 10 hours * 20000 * 1.5 = 300000
        $this->assertEquals(300000, $overtime['amount']);
    }

    // ==========================================
    // SCENARIO 4: Absent Days (Alpha)
    // ==========================================

    /** @test */
    public function scenario_absent_2_days()
    {
        $period = $this->createPeriod();
        $employee = $this->createEmployee(5000000);
        $summary = $this->createSummary($employee, $period, [
            'present_days' => 20,
            'alpha_days' => 2,
            'scheduled_work_days' => 22,
        ]);

        $slip = $this->calculator->calculateFromPeriodSummary($period, $employee, $summary);

        // Should have absent deduction
        $absentDeduction = collect($slip->deductions)->firstWhere('code', 'ABSENT_DEDUCTION');
        $this->assertNotNull($absentDeduction);

        // 2 days * (5000000 / 22) = ~454545
        $expectedDeduction = round(2 * (5000000 / 22), 0);
        $this->assertEquals($expectedDeduction, $absentDeduction['amount']);
    }

    // ==========================================
    // SCENARIO 5: High Income Tax Bracket
    // ==========================================

    /** @test */
    public function scenario_high_income_tax()
    {
        $period = $this->createPeriod();
        $employee = $this->createEmployee(50000000); // 50 juta
        $summary = $this->createSummary($employee, $period, [
            'present_days' => 22,
        ]);

        $slip = $this->calculator->calculateFromPeriodSummary($period, $employee, $summary);

        // Should have tax deduction
        $tax = collect($slip->deductions)->firstWhere('code', 'TAX_PPH21');
        $this->assertNotNull($tax);
        $this->assertGreaterThan(0, $tax['amount']);

        // Tax should be significant for high income
        $this->assertGreaterThan(5000000, $tax['amount']);
    }

    // ==========================================
    // SCENARIO 6: Deduction Exceeds Gross
    // ==========================================

    /** @test */
    public function scenario_deduction_exceeds_gross()
    {
        $period = $this->createPeriod(['late_penalty_per_minute' => 10000]); // High penalty
        $employee = $this->createEmployee(1000000); // Low salary
        $summary = $this->createSummary($employee, $period, [
            'present_days' => 10,
            'late_days' => 12,
            'total_late_minutes' => 600, // 10 hours late total
        ]);

        $slip = $this->calculator->calculateFromPeriodSummary($period, $employee, $summary);

        // Net should be capped at 0, not negative
        $this->assertEquals(0, $slip->net_salary);

        // Excess should be tracked
        $this->assertGreaterThan(0, $slip->excess_deduction);
    }

    // ==========================================
    // SCENARIO 7: Leave Days (Paid)
    // ==========================================

    /** @test */
    public function scenario_paid_leave()
    {
        $period = $this->createPeriod();
        $employee = $this->createEmployee(5000000);
        $summary = $this->createSummary($employee, $period, [
            'present_days' => 17,
            'leave_days' => 5,
            'scheduled_work_days' => 22,
        ]);

        $slip = $this->calculator->calculateFromPeriodSummary($period, $employee, $summary);

        // With proration_type = 'none', should get full salary
        $this->assertEquals(5000000, $slip->gross_salary);
        $this->assertEquals(5, $slip->leave_days);
    }
}
