<?php

namespace App\Services\Payroll;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\PayrollSlip;
use App\Models\PayrollSlipItem;
use App\Models\AttendancePeriodSummary;
use Illuminate\Support\Collection;

/**
 * PayrollCalculator Service
 * 
 * Phase 3: Rules Engine - Centralized payroll calculation logic
 * Controller orchestrates, this service calculates
 */
class PayrollCalculator
{
    private BpjsCalculator $bpjsCalculator;
    private TaxCalculator $taxCalculator;

    // Calculation context
    private Employee $employee;
    private PayrollPeriod $period;
    private AttendancePeriodSummary $periodSummary;

    // Attendance data
    private int $scheduledWorkingDays = 0;
    private int $presentDays = 0;
    private int $lateDays = 0;
    private int $absentDays = 0;
    private int $leaveDays = 0;
    private int $sickDays = 0;
    private int $permissionDays = 0;
    private int $paidDays = 0;
    private int $attendanceOnlyDays = 0;
    private int $totalOvertimeMinutes = 0;
    private int $totalLateMinutes = 0;

    // Calculation results
    private array $earnings = [];
    private array $deductions = [];
    private float $totalEarnings = 0;
    private float $totalDeductions = 0;
    private bool $basicSalaryUsesProration = false;

    public function __construct()
    {
        $this->bpjsCalculator = new BpjsCalculator();
        $this->taxCalculator = new TaxCalculator();
    }

    /**
     * Calculate payroll for employee from locked period summary
     */
    public function calculateFromPeriodSummary(
        PayrollPeriod $period,
        Employee $employee,
        AttendancePeriodSummary $periodSummary
    ): PayrollSlip {
        $this->period = $period;
        $this->employee = $employee;
        $this->periodSummary = $periodSummary;

        // Extract attendance data
        $this->extractAttendanceData();

        // Calculate earnings
        $this->calculateEarnings();

        // Calculate overtime
        $this->calculateOvertime();

        // Calculate deductions
        $this->calculateDeductions();

        // Create and return slip
        return $this->createSlip();
    }

    /**
     * Extract attendance data from period summary
     */
    private function extractAttendanceData(): void
    {
        $this->scheduledWorkingDays = $this->periodSummary->scheduled_working_days ?? 22;
        $this->presentDays = $this->periodSummary->present_days ?? 0;
        $this->lateDays = $this->periodSummary->late_days ?? 0;
        $this->absentDays = $this->periodSummary->absent_days ?? 0;
        $this->leaveDays = $this->periodSummary->leave_days ?? 0;
        $this->sickDays = $this->periodSummary->sick_days ?? 0;
        $this->permissionDays = $this->periodSummary->permission_days ?? 0;
        $this->totalOvertimeMinutes = $this->periodSummary->total_overtime_minutes ?? 0;
        $this->totalLateMinutes = $this->periodSummary->total_late_minutes ?? 0;

        // paidDays = all days employee should be paid (including paid leave)
        $this->paidDays = $this->presentDays + $this->lateDays +
            $this->leaveDays + $this->sickDays + $this->permissionDays;

        // attendanceOnlyDays = only physically present days (for meal/transport)
        $this->attendanceOnlyDays = $this->presentDays;
    }

    /**
     * Calculate all earnings from employee components
     */
    private function calculateEarnings(): void
    {
        $this->earnings = [];
        $this->totalEarnings = 0;

        foreach ($this->employee->activePayrollComponents as $empComponent) {
            $component = $empComponent->component;

            if ($component->type !== 'earning') {
                continue;
            }

            $amount = $this->calculateEarningAmount($empComponent, $component);

            if ($amount > 0) {
                $this->earnings[] = [
                    'component_id' => $component->id,
                    'code' => $component->code,
                    'name' => $component->name,
                    'category' => $component->category,
                    'type' => 'earning',
                    'base_amount' => $empComponent->amount,
                    'amount' => $amount,
                    'is_taxable' => $component->is_taxable,
                    'meta' => $this->buildEarningMeta($component, $empComponent->amount, $amount),
                ];
                $this->totalEarnings += $amount;
            }
        }
    }

    /**
     * Calculate single earning amount based on component rules
     * 
     * ERP Hierarchy for amounts:
     * 1. If employee is_override=true → use employee.amount
     * 2. For daily_rate → use component.rate_per_day (or employee.amount if override)
     * 3. Otherwise → use component.default_amount, fallback to employee.amount
     */
    private function calculateEarningAmount($empComponent, $component): float
    {
        // Determine base amount using ERP hierarchy
        $baseAmount = $this->resolveEffectiveAmount($empComponent, $component);

        // Calculate attendance rate
        $attendanceRate = $this->scheduledWorkingDays > 0
            ? ($this->presentDays / $this->scheduledWorkingDays) * 100
            : 100;

        // CHECK: Minimum attendance requirement
        if ($component->min_attendance_percent && $attendanceRate < $component->min_attendance_percent) {
            return 0;
        }

        // CHECK: Forfeit on alpha
        if ($component->forfeit_on_alpha && $this->absentDays > 0) {
            return 0;
        }

        // CHECK: Forfeit on late
        if ($component->forfeit_on_late && $this->lateDays > 0) {
            return 0;
        }

        // SPECIAL CASE: daily_rate calculation
        if ($component->calculation_type === 'daily_rate') {
            $dailyRate = $this->resolveDailyRate($empComponent, $component);
            return round($dailyRate * $this->attendanceOnlyDays, 0);
        }

        // Apply proration based on proration_type
        switch ($component->proration_type) {
            case 'daily':
                if ($this->scheduledWorkingDays > 0) {
                    $dailyRate = $baseAmount / $this->scheduledWorkingDays;
                    $baseAmount = round($dailyRate * $this->attendanceOnlyDays, 0);
                }
                if ($component->code === 'BASIC_SALARY') {
                    $this->basicSalaryUsesProration = true;
                }
                break;

            case 'attendance':
                if ($this->scheduledWorkingDays > 0) {
                    $paidRatio = $this->paidDays / $this->scheduledWorkingDays;
                    $baseAmount = round($baseAmount * $paidRatio, 0);
                }
                if ($component->code === 'BASIC_SALARY') {
                    $this->basicSalaryUsesProration = true;
                }
                break;

            case 'none':
            default:
                // Full amount
                break;
        }

        return $baseAmount;
    }

    /**
     * Resolve effective amount using ERP hierarchy
     * Priority: override → component.default_amount → employee.amount
     */
    private function resolveEffectiveAmount($empComponent, $component): float
    {
        // 1. If employee has override, use their amount
        if ($empComponent->is_override) {
            return (float) $empComponent->amount;
        }

        // 2. Use component's default_amount if available
        if ($component->default_amount > 0) {
            return (float) $component->default_amount;
        }

        // 3. Fallback to employee's amount
        return (float) $empComponent->amount;
    }

    /**
     * Resolve daily rate using ERP hierarchy
     */
    private function resolveDailyRate($empComponent, $component): float
    {
        // 1. If employee has override, use their amount as daily rate
        if ($empComponent->is_override && $empComponent->amount > 0) {
            return (float) $empComponent->amount;
        }

        // 2. Use component's rate_per_day if available
        if ($component->rate_per_day > 0) {
            return (float) $component->rate_per_day;
        }

        // 3. Use component's default_amount if available
        if ($component->default_amount > 0) {
            return (float) $component->default_amount;
        }

        // 4. Fallback to employee's amount
        return (float) $empComponent->amount;
    }

    /**
     * Build metadata for earning item
     */
    private function buildEarningMeta($component, $baseAmount, $finalAmount): ?array
    {
        if ($component->calculation_type === 'daily_rate') {
            $rateUsed = $component->rate_per_day > 0
                ? $component->rate_per_day
                : $baseAmount;
            $rateSource = $component->rate_per_day > 0 ? 'component' : 'employee';

            return [
                'calculation_type' => 'daily_rate',
                'rate_per_day' => $rateUsed,
                'rate_source' => $rateSource, // 'component' or 'employee'
                'days' => $this->attendanceOnlyDays,
            ];
        }

        if ($component->proration_type !== 'none') {
            return [
                'proration_type' => $component->proration_type,
                'scheduled_days' => $this->scheduledWorkingDays,
                'paid_days' => $this->paidDays,
                'attendance_days' => $this->attendanceOnlyDays,
            ];
        }

        return null;
    }

    /**
     * Calculate overtime earnings
     */
    private function calculateOvertime(): void
    {
        if ($this->totalOvertimeMinutes <= 0) {
            return;
        }

        // Get MASTER basic salary (not prorated)
        $masterBasicSalaryComponent = $this->employee->activePayrollComponents
            ->first(fn($c) => $c->component->code === 'BASIC_SALARY');

        $masterBasicSalary = $masterBasicSalaryComponent?->amount ?? 0;

        // Calculate hourly rate (173 hours/month standard)
        $hourlyRate = $masterBasicSalary > 0 ? $masterBasicSalary / 173 : 0;

        // 1.5x multiplier for regular overtime
        $overtimeAmount = round(($this->totalOvertimeMinutes / 60) * $hourlyRate * 1.5, 0);

        $this->earnings[] = [
            'component_id' => null,
            'code' => 'OVERTIME',
            'name' => 'Lembur (' . round($this->totalOvertimeMinutes / 60, 1) . ' jam)',
            'category' => 'variable_allowance',
            'type' => 'earning',
            'base_amount' => $masterBasicSalary,
            'amount' => $overtimeAmount,
            'is_taxable' => true,
            'meta' => [
                'minutes' => $this->totalOvertimeMinutes,
                'hours' => round($this->totalOvertimeMinutes / 60, 2),
                'hourly_rate' => $hourlyRate,
                'multiplier' => 1.5,
            ],
        ];

        $this->totalEarnings += $overtimeAmount;
    }

    /**
     * Calculate all deductions
     */
    private function calculateDeductions(): void
    {
        $this->deductions = [];
        $this->totalDeductions = 0;

        // Calculate BPJS
        $this->calculateBpjs();

        // Calculate Tax
        $this->calculateTax();

        // Calculate late deduction
        $this->calculateLateDeduction();

        // Calculate absent deduction (only if no proration)
        $this->calculateAbsentDeduction();

        // Add other deductions from employee components
        $this->addOtherDeductions();

        $this->totalDeductions = collect($this->deductions)->sum('amount');
    }

    /**
     * Calculate BPJS deductions using service
     */
    private function calculateBpjs(): void
    {
        $bpjsBase = collect($this->earnings)
            ->filter(fn($e) => $e['category'] === 'basic_salary')
            ->sum('amount');

        $jkkRiskClass = $this->employee->sensitiveData?->jkk_risk_class ?? 1;
        $bpjs = $this->bpjsCalculator->calculate($bpjsBase, $jkkRiskClass);

        $bpjsItems = $this->bpjsCalculator->getDeductionItems($bpjs);
        foreach ($bpjsItems as $item) {
            $item['component_id'] = null;
            $this->deductions[] = $item;
        }
    }

    /**
     * Calculate tax using TER method
     */
    private function calculateTax(): void
    {
        $taxableIncome = collect($this->earnings)
            ->where('is_taxable', true)
            ->sum('amount');

        $taxStatus = $this->employee->sensitiveData?->tax_status ?? 'TK/0';
        $hasNpwp = !empty($this->employee->sensitiveData?->npwp);

        $taxResult = $this->taxCalculator->calculatePph21($taxableIncome, $taxStatus, $hasNpwp);

        if ($taxResult['tax_amount'] > 0) {
            $taxItem = $this->taxCalculator->getDeductionItem($taxResult);
            $taxItem['component_id'] = null;
            $this->deductions[] = $taxItem;
        }
    }

    /**
     * Calculate late deduction
     */
    private function calculateLateDeduction(): void
    {
        if ($this->totalLateMinutes <= 0) {
            return;
        }

        $lateDeduction = round($this->totalLateMinutes * 1000, 0); // Rp 1000/menit

        $this->deductions[] = [
            'component_id' => null,
            'code' => 'LATE_DEDUCTION',
            'name' => 'Potongan Terlambat (' . $this->totalLateMinutes . ' menit)',
            'category' => 'other_deduction',
            'type' => 'deduction',
            'base_amount' => $lateDeduction,
            'amount' => $lateDeduction,
            'meta' => ['minutes' => $this->totalLateMinutes, 'rate_per_minute' => 1000],
        ];
    }

    /**
     * Calculate absent deduction (only if basic salary not prorated)
     */
    private function calculateAbsentDeduction(): void
    {
        if ($this->absentDays <= 0 || $this->basicSalaryUsesProration) {
            return;
        }

        $basicSalaryItem = collect($this->earnings)->firstWhere('code', 'BASIC_SALARY');
        $basicSalaryAmount = $basicSalaryItem['amount'] ?? 0;

        $dailyRate = $this->scheduledWorkingDays > 0
            ? $basicSalaryAmount / $this->scheduledWorkingDays
            : 0;
        $absentDeduction = round($dailyRate * $this->absentDays, 0);

        $this->deductions[] = [
            'component_id' => null,
            'code' => 'ABSENT_DEDUCTION',
            'name' => 'Potongan Alpha (' . $this->absentDays . ' hari)',
            'category' => 'other_deduction',
            'type' => 'deduction',
            'base_amount' => $absentDeduction,
            'amount' => $absentDeduction,
            'meta' => [
                'days' => $this->absentDays,
                'daily_rate' => $dailyRate,
            ],
        ];
    }

    /**
     * Add other deductions from employee components
     */
    private function addOtherDeductions(): void
    {
        // Skip auto-calculated components
        $autoCalculatedCodes = ['BPJS_TK', 'BPJS_KES', 'PPH21', 'BPJS_JHT', 'BPJS_JP', 'TAX_PPH21'];

        foreach ($this->employee->activePayrollComponents as $empComponent) {
            $component = $empComponent->component;

            if ($component->type !== 'deduction') {
                continue;
            }

            if (in_array($component->code, $autoCalculatedCodes)) {
                continue;
            }

            $this->deductions[] = [
                'component_id' => $component->id,
                'code' => $component->code,
                'name' => $component->name,
                'category' => $component->category,
                'type' => 'deduction',
                'base_amount' => $empComponent->amount,
                'amount' => $empComponent->amount,
                'meta' => null,
            ];
        }
    }

    /**
     * Create the payroll slip
     */
    private function createSlip(): PayrollSlip
    {
        $currentCareer = $this->employee->currentCareer;
        $netSalary = $this->totalEarnings - $this->totalDeductions;

        // Build calculation snapshot
        $calculationSnapshot = $this->buildSnapshot();

        // Get tax and BPJS totals for legacy fields
        $taxResult = $this->taxCalculator->calculatePph21(
            collect($this->earnings)->where('is_taxable', true)->sum('amount'),
            $this->employee->sensitiveData?->tax_status ?? 'TK/0',
            !empty($this->employee->sensitiveData?->npwp)
        );

        $bpjsBase = collect($this->earnings)
            ->filter(fn($e) => $e['category'] === 'basic_salary')
            ->sum('amount');
        $bpjs = $this->bpjsCalculator->calculate(
            $bpjsBase,
            $this->employee->sensitiveData?->jkk_risk_class ?? 1
        );

        $slip = PayrollSlip::create([
            'payroll_period_id' => $this->period->id,
            'employee_id' => $this->employee->id,
            'slip_number' => PayrollSlip::generateSlipNumber($this->period->period_code, $this->employee->nik),
            'slip_date' => $this->period->end_date,

            // Employee snapshot
            'employee_nik' => $this->employee->nik,
            'employee_name' => $this->employee->full_name,
            'department' => $currentCareer?->department?->name,
            'position' => $currentCareer?->position?->name,
            'level' => $currentCareer?->level?->grade_code,

            // Working days
            'working_days' => $this->scheduledWorkingDays,
            'actual_days' => $this->presentDays,
            'absent_days' => $this->absentDays,
            'leave_days' => $this->leaveDays + $this->sickDays + $this->permissionDays,

            // Components (JSON for backward compatibility)
            'earnings' => $this->earnings,
            'deductions' => $this->deductions,

            // Totals
            'gross_salary' => $this->totalEarnings,
            'total_deductions' => $this->totalDeductions,
            'net_salary' => max(0, $netSalary),

            // Tax
            'tax_status' => $this->employee->sensitiveData?->tax_status ?? 'TK/0',
            'taxable_income' => collect($this->earnings)->where('is_taxable', true)->sum('amount'),
            'tax_amount' => $taxResult['tax_amount'],

            // BPJS (legacy format)
            'bpjs_tk_company' => $bpjs['bpjs_tk_company'],
            'bpjs_tk_employee' => $bpjs['bpjs_tk_employee'],
            'bpjs_kes_company' => $bpjs['bpjs_kes_company'],
            'bpjs_kes_employee' => $bpjs['bpjs_kes_employee'],

            // Payment
            'payment_status' => 'pending',

            // Bank
            'bank_name' => $this->employee->sensitiveData?->bank_name,
            'bank_account_number' => $this->employee->sensitiveData?->bank_account_number,
            'bank_account_holder' => $this->employee->sensitiveData?->bank_account_holder,

            // Audit
            'calculation_snapshot' => $calculationSnapshot,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);

        // Create normalized slip items
        $this->createSlipItems($slip);

        return $slip;
    }

    /**
     * ⭐ Create normalized slip items for reporting
     * Uses upsertFromArray to prevent duplicates on rerun
     */
    private function createSlipItems(PayrollSlip $slip): void
    {
        $displayOrder = 0;

        foreach ($this->earnings as $earning) {
            PayrollSlipItem::upsertFromArray($slip->id, $earning, 'earning', ++$displayOrder);
        }

        foreach ($this->deductions as $deduction) {
            PayrollSlipItem::upsertFromArray($slip->id, $deduction, 'deduction', ++$displayOrder);
        }
    }

    /**
     * Build calculation snapshot for audit
     */
    private function buildSnapshot(): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'generated_by' => auth()->id(),
            'period_summary' => [
                'id' => $this->periodSummary->id,
                'scheduled_working_days' => $this->scheduledWorkingDays,
                'present_days' => $this->presentDays,
                'late_days' => $this->lateDays,
                'absent_days' => $this->absentDays,
                'leave_days' => $this->leaveDays,
                'sick_days' => $this->sickDays,
                'permission_days' => $this->permissionDays,
                'total_overtime_minutes' => $this->totalOvertimeMinutes,
                'total_late_minutes' => $this->totalLateMinutes,
            ],
            'components_used' => $this->employee->activePayrollComponents->map(fn($c) => [
                'component_id' => $c->component_id,
                'code' => $c->component->code,
                'name' => $c->component->name,
                'amount' => $c->amount,
                'calculation_type' => $c->component->calculation_type,
                'rate_per_day' => $c->component->rate_per_day,
                'proration_type' => $c->component->proration_type,
            ])->toArray(),
            'calculation_params' => [
                'paid_days' => $this->paidDays,
                'attendance_only_days' => $this->attendanceOnlyDays,
                'basic_salary_uses_proration' => $this->basicSalaryUsesProration,
            ],
            'services_used' => [
                'bpjs_calculator' => 'BpjsCalculator',
                'tax_calculator' => 'TaxCalculator (TER method)',
            ],
        ];
    }

    /**
     * Get calculation results (for testing/debugging)
     */
    public function getResults(): array
    {
        return [
            'earnings' => $this->earnings,
            'deductions' => $this->deductions,
            'total_earnings' => $this->totalEarnings,
            'total_deductions' => $this->totalDeductions,
            'net_salary' => $this->totalEarnings - $this->totalDeductions,
        ];
    }
}
