<?php

namespace App\Http\Controllers\HumanResource\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Models\Employee;
use App\Models\PayrollSlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollPeriodController extends Controller
{
    /**
     * Display listing of payroll periods
     */
    public function index(Request $request)
    {
        $query = PayrollPeriod::query()->with(['approvedBy']);

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $periods = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        // Get available years for filter
        $years = PayrollPeriod::selectRaw('DISTINCT year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Statistics
        $totalPeriods = PayrollPeriod::count();
        $draftPeriods = PayrollPeriod::where('status', 'draft')->count();
        $approvedPeriods = PayrollPeriod::where('status', 'approved')->count();
        $paidPeriods = PayrollPeriod::where('status', 'paid')->count();

        return view('admin.hris.payroll.periods.index', compact(
            'periods',
            'years',
            'totalPeriods',
            'draftPeriods',
            'approvedPeriods',
            'paidPeriods'
        ));
    }

    /**
     * Show create form
     */
    public function create()
    {
        // Suggest next period
        $lastPeriod = PayrollPeriod::latest('year')->latest('month')->first();

        if ($lastPeriod) {
            $nextMonth = $lastPeriod->end_date->copy()->addDay();
        } else {
            $nextMonth = now()->startOfMonth();
        }

        $suggestedYear = $nextMonth->year;
        $suggestedMonth = $nextMonth->month;
        $suggestedStartDate = $nextMonth->startOfMonth()->toDateString();
        $suggestedEndDate = $nextMonth->endOfMonth()->toDateString();
        $suggestedPaymentDate = $nextMonth->endOfMonth()->addDays(5)->toDateString();

        return view('admin.hris.payroll.periods.create', compact(
            'suggestedYear',
            'suggestedMonth',
            'suggestedStartDate',
            'suggestedEndDate',
            'suggestedPaymentDate'
        ));
    }

    /**
     * Store new payroll period
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2100',
                'month' => 'required|integer|min:1|max:12',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'payment_date' => 'required|date|after_or_equal:end_date',
                'notes' => 'nullable|string',
            ], [
                'year.required' => 'Tahun wajib diisi.',
                'month.required' => 'Bulan wajib diisi.',
                'start_date.required' => 'Tanggal mulai wajib diisi.',
                'end_date.required' => 'Tanggal akhir wajib diisi.',
                'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
            ]);

            // Check if period already exists
            $exists = PayrollPeriod::where('year', $validated['year'])
                ->where('month', $validated['month'])
                ->exists();

            if ($exists) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Periode payroll untuk bulan ini sudah ada.');
            }

            // Generate period code and name
            $periodCode = sprintf('%04d-%02d', $validated['year'], $validated['month']);
            $monthName = Carbon::createFromDate($validated['year'], $validated['month'], 1)->locale('id')->translatedFormat('F');
            $periodName = "Payroll {$monthName} {$validated['year']}";

            $period = PayrollPeriod::create([
                'period_code' => $periodCode,
                'period_name' => $periodName,
                'year' => $validated['year'],
                'month' => $validated['month'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'payment_date' => $validated['payment_date'],
                'status' => 'draft',
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            Log::info('Payroll period created', [
                'period_id' => $period->id,
                'period_code' => $period->period_code,
            ]);

            return redirect()
                ->route('hris.payroll.periods.show', $period->id)
                ->with('success', 'Periode payroll berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payroll Period Create Error', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show payroll period detail
     */
    public function show($id)
    {
        $period = PayrollPeriod::with([
            'slips' => function ($query) {
                $query->with('employee')->orderBy('employee_name');
            },
            'approvedBy'
        ])->findOrFail($id);

        // Statistics for this period
        $totalSlips = $period->slips->count();
        $paidSlips = $period->slips->where('payment_status', 'paid')->count();
        $pendingSlips = $period->slips->where('payment_status', 'pending')->count();

        return view('admin.hris.payroll.periods.show', compact(
            'period',
            'totalSlips',
            'paidSlips',
            'pendingSlips'
        ));
    }

    /**
     * Generate slips for all active employees
     * 
     * Flow: 
     * 1. Generate AttendancePeriodSummary for each employee (aggregate daily data)
     * 2. Lock period summaries
     * 3. Create PayrollSlip from locked summaries
     */
    public function generateSlips($id)
    {
        DB::beginTransaction();

        try {
            $period = PayrollPeriod::findOrFail($id);

            // ⭐ Guard: Prevent modifications to paid/closed periods
            $period->guardAgainstLock('regenerate slips');

            // Check if already has slips
            if ($period->slips()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Periode ini sudah memiliki slip gaji. Hapus slip yang ada terlebih dahulu.');
            }

            // Get all active employees with proper relationships
            $employees = Employee::where('status', 'active')
                ->with([
                    'activePayrollComponents.component',
                    'sensitiveData',
                    'currentCareer.department',
                    'currentCareer.position',
                    'currentCareer.level',
                ])
                ->get();

            if ($employees->count() === 0) {
                DB::rollBack();
                return redirect()
                    ->back()
                    ->with('error', 'Tidak ada karyawan aktif yang ditemukan.');
            }

            // ========================================
            // STEP 1: Generate Period Summaries for all employees
            // ========================================
            $periodSummaries = [];
            foreach ($employees as $employee) {
                $periodSummaries[$employee->id] = \App\Models\AttendancePeriodSummary::generateFromDailySummaries(
                    $employee->id,
                    $period->id,
                    $period->start_date,
                    $period->end_date,
                    auth()->id()
                );
            }

            // ========================================
            // STEP 2: Lock all period summaries
            // ========================================
            foreach ($periodSummaries as $periodSummary) {
                if (!$periodSummary->is_locked) {
                    $periodSummary->lock(auth()->id());
                }
            }

            // Lock attendance on period level
            $period->update([
                'attendance_locked' => true,
                'attendance_locked_at' => now(),
                'attendance_locked_by' => auth()->id(),
            ]);

            // ========================================
            // STEP 3: Create slips from locked period summaries
            // Using PayrollCalculator service (Phase 3: Rules Engine)
            // ========================================
            $slipsCreated = 0;
            $errors = [];
            $payrollCalculator = new \App\Services\Payroll\PayrollCalculator();

            foreach ($employees as $employee) {
                try {
                    $periodSummary = $periodSummaries[$employee->id];
                    $payrollCalculator->calculateFromPeriodSummary($period, $employee, $periodSummary);
                    $slipsCreated++;
                } catch (\Exception $e) {
                    $errors[] = "Error creating slip for {$employee->full_name}: " . $e->getMessage();
                    Log::error('Slip creation error', [
                        'employee_id' => $employee->id,
                        'employee_name' => $employee->full_name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update period totals
            $period->calculateTotals();
            $period->status = 'processing';
            $period->save();

            DB::commit();

            Log::info('Payroll slips generated', [
                'period_id' => $period->id,
                'slips_created' => $slipsCreated,
                'period_summaries_generated' => count($periodSummaries),
                'errors_count' => count($errors),
            ]);

            $message = "{$slipsCreated} slip gaji berhasil di-generate dari " . count($periodSummaries) . " period summaries.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " slip gagal dibuat. Cek log untuk detail.";
            }

            return redirect()
                ->route('hris.payroll.periods.show', $period->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Generate Slips Error', [
                'error' => $e->getMessage(),
                'period_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // ========================================
    // DEPRECATED: createSlipForEmployee removed
    // Now using only createSlipFromPeriodSummary which reads from locked AttendancePeriodSummary
    // This ensures single source of truth and consistent calculations
    // ========================================

    /**
     * Create slip from LOCKED AttendancePeriodSummary
     * This is the correct flow: reads from aggregated, locked data
     * 
     * @param PayrollPeriod $period
     * @param Employee $employee
     * @param \App\Models\AttendancePeriodSummary $periodSummary (must be locked)
     */
    private function createSlipFromPeriodSummary(
        PayrollPeriod $period,
        Employee $employee,
        \App\Models\AttendancePeriodSummary $periodSummary
    ) {
        // Get current career history
        $currentCareer = $employee->currentCareer;

        // ========================================
        // ATTENDANCE DATA FROM LOCKED PERIOD SUMMARY
        // ========================================
        $presentDays = $periodSummary->present_days;
        $lateDays = $periodSummary->late_days;
        $absentDays = $periodSummary->alpha_days;
        $leaveDays = $periodSummary->leave_days;
        $sickDays = $periodSummary->sick_days;
        $permissionDays = $periodSummary->permission_days;
        $scheduledWorkingDays = $periodSummary->scheduled_work_days;
        $totalOvertimeMinutes = $periodSummary->total_approved_overtime_minutes;
        $totalLateMinutes = $periodSummary->total_late_minutes;

        // Fallback if no schedule data
        if ($scheduledWorkingDays <= 0) {
            $scheduledWorkingDays = 22; // Default 22 working days
        }

        // ========================================
        // PAID DAYS CONCEPT
        // ========================================
        // paidDays = days where salary should be paid (for fixed monthly components)
        // presentDays = days physically present (for attendance-based components like meal/transport)
        $paidDays = $presentDays + $lateDays + $leaveDays + $sickDays + $permissionDays;
        $attendanceOnlyDays = $presentDays; // For meal, transport, etc.

        // ========================================
        // TRACK PRORATION STATUS
        // ========================================
        $basicSalaryUsesProration = false;

        // ========================================
        // EARNINGS CALCULATION
        // ========================================
        $earnings = [];
        $totalEarnings = 0;

        foreach ($employee->activePayrollComponents as $empComponent) {
            $component = $empComponent->component;

            if ($component->type === 'earning') {
                $amount = $empComponent->amount;

                // Calculate attendance rate for checks
                $attendanceRate = $scheduledWorkingDays > 0
                    ? ($presentDays / $scheduledWorkingDays) * 100
                    : 100;

                // CHECK: Minimum attendance requirement
                if ($component->min_attendance_percent && $attendanceRate < $component->min_attendance_percent) {
                    $amount = 0; // Tidak memenuhi minimum attendance
                }

                // CHECK: Forfeit on alpha (any absent day = 0)
                if ($component->forfeit_on_alpha && $absentDays > 0) {
                    $amount = 0;
                }

                // CHECK: Forfeit on late (any late day = 0)
                if ($component->forfeit_on_late && $lateDays > 0) {
                    $amount = 0;
                }

                // Apply proration based on proration_type (only if not forfeited)
                if ($amount > 0) {
                    // SPECIAL CASE: calculation_type = 'daily_rate' uses rate_per_day × presentDays
                    // This is for components like MEAL, TRANSPORT that have a fixed daily rate
                    if ($component->calculation_type === 'daily_rate' && $component->rate_per_day > 0) {
                        $amount = round($component->rate_per_day * $attendanceOnlyDays, 0);
                    } else {
                        // Otherwise use proration_type for components with monthly amount
                        switch ($component->proration_type) {
                            case 'daily':
                                // Prorate per working day using attendanceOnlyDays (physically present)
                                if ($scheduledWorkingDays > 0) {
                                    $dailyRate = $empComponent->amount / $scheduledWorkingDays;
                                    $amount = round($dailyRate * $attendanceOnlyDays, 0);
                                }
                                // Track if basic salary uses proration
                                if ($component->code === 'BASIC_SALARY') {
                                    $basicSalaryUsesProration = true;
                                }
                                break;
                            case 'attendance':
                                // Prorate based on paid days ratio (for fixed monthly salary with leave support)
                                if ($scheduledWorkingDays > 0) {
                                    $paidRatio = $paidDays / $scheduledWorkingDays;
                                    $amount = round($empComponent->amount * $paidRatio, 0);
                                }
                                // Track if basic salary uses proration
                                if ($component->code === 'BASIC_SALARY') {
                                    $basicSalaryUsesProration = true;
                                }
                                break;
                            case 'none':
                            default:
                                // Full amount (no proration) - keep original amount
                                break;
                        }
                    }
                }

                $earnings[] = [
                    'code' => $component->code,
                    'name' => $component->name,
                    'category' => $component->category,
                    'type' => 'earning',
                    'amount' => $amount,
                    'is_taxable' => $component->is_taxable,
                ];
                $totalEarnings += $amount;
            }
        }

        // Add overtime earnings
        if ($totalOvertimeMinutes > 0) {
            // 1. Get MASTER Basic Salary from employee components (not prorated amount)
            // Overtime should always be calculated from FULL salary, not prorated
            $masterBasicSalaryComponent = $employee->activePayrollComponents
                ->first(fn($c) => $c->component->code === 'BASIC_SALARY');

            $masterBasicSalary = $masterBasicSalaryComponent?->amount ?? 0;

            // 2. Calculate hourly rate from FULL salary (173 hours/month standard)
            $hourlyRate = $masterBasicSalary > 0 ? $masterBasicSalary / 173 : 0;

            // 3. Calculate overtime amount (1.5x multiplier for regular overtime)
            $overtimeAmount = round(($totalOvertimeMinutes / 60) * $hourlyRate * 1.5, 0);

            $earnings[] = [
                'code' => 'OVERTIME',
                'name' => 'Lembur (' . round($totalOvertimeMinutes / 60, 1) . ' jam)',
                'category' => 'variable_allowance',
                'type' => 'earning',
                'amount' => $overtimeAmount,
                'is_taxable' => true,
            ];
            $totalEarnings += $overtimeAmount;
        }

        // ========================================
        // DEDUCTIONS CALCULATION (Using Services)
        // ========================================

        // Calculate BPJS using service
        $bpjsBase = collect($earnings)
            ->filter(fn($earning) => $earning['category'] === 'basic_salary')
            ->sum('amount');

        $bpjsCalculator = new \App\Services\Payroll\BpjsCalculator();
        $bpjs = $bpjsCalculator->calculate($bpjsBase);

        // Calculate Tax using service
        $taxableIncome = collect($earnings)->where('is_taxable', true)->sum('amount');
        $taxStatus = $employee->sensitiveData?->tax_status ?? 'TK/0';
        $hasNpwp = !empty($employee->sensitiveData?->npwp);

        $taxCalculator = new \App\Services\Payroll\TaxCalculator();
        $taxResult = $taxCalculator->calculatePph21($taxableIncome, $taxStatus, $hasNpwp);

        // Build deductions array
        $deductions = [];

        // PPh 21
        if ($taxResult['tax_amount'] > 0) {
            $deductions[] = $taxCalculator->getDeductionItem($taxResult);
        }

        // BPJS items (detailed breakdown)
        $deductions = array_merge($deductions, $bpjsCalculator->getDeductionItems($bpjs));

        // Add late deduction
        if ($totalLateMinutes > 0) {
            $lateDeduction = round($totalLateMinutes * 1000, 0);
            $deductions[] = [
                'code' => 'LATE_DEDUCTION',
                'name' => 'Potongan Terlambat (' . $totalLateMinutes . ' menit)',
                'category' => 'other_deduction',
                'type' => 'deduction',
                'amount' => $lateDeduction,
            ];
        }

        // Add absent deduction ONLY if basic salary is NOT prorated (fix double penalty)
        // If proration is used, salary is already reduced - don't apply additional deduction
        if ($absentDays > 0 && !$basicSalaryUsesProration) {
            $basicSalaryAmount = collect($earnings)->where('code', 'BASIC_SALARY')->first()['amount'] ?? 0;
            $dailyRate = $scheduledWorkingDays > 0 ? $basicSalaryAmount / $scheduledWorkingDays : 0;
            $absentDeduction = round($dailyRate * $absentDays, 0);

            $deductions[] = [
                'code' => 'ABSENT_DEDUCTION',
                'name' => 'Potongan Alpha (' . $absentDays . ' hari)',
                'category' => 'other_deduction',
                'type' => 'deduction',
                'amount' => $absentDeduction,
            ];
        }

        // Add other deductions from employee components
        // Skip components that are already calculated by services (BPJS, Tax)
        $autoCalculatedCodes = ['BPJS_TK', 'BPJS_KES', 'PPH21', 'BPJS_JHT', 'BPJS_JP', 'TAX_PPH21'];

        foreach ($employee->activePayrollComponents as $component) {
            if ($component->component->type === 'deduction') {
                // Skip if already calculated by service
                if (in_array($component->component->code, $autoCalculatedCodes)) {
                    continue;
                }

                $deductions[] = [
                    'code' => $component->component->code,
                    'name' => $component->component->name,
                    'category' => $component->component->category,
                    'type' => 'deduction',
                    'amount' => $component->amount,
                ];
            }
        }

        $totalDeductions = collect($deductions)->sum('amount');
        $netSalary = $totalEarnings - $totalDeductions;

        // Generate slip number
        $slipNumber = PayrollSlip::generateSlipNumber($period->period_code, $employee->nik);

        // Combine leave days for slip storage
        $totalLeaveDays = $leaveDays + $sickDays + $permissionDays;

        // ========================================
        // CALCULATION SNAPSHOT (Phase 1: Freeze & Audit)
        // Stores all data used for calculation for audit trail
        // ========================================
        $calculationSnapshot = [
            'generated_at' => now()->toIso8601String(),
            'generated_by' => auth()->id(),
            'period_summary' => [
                'id' => $periodSummary->id,
                'scheduled_working_days' => $scheduledWorkingDays,
                'present_days' => $presentDays,
                'late_days' => $lateDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'sick_days' => $sickDays,
                'permission_days' => $permissionDays,
                'total_overtime_minutes' => $totalOvertimeMinutes,
                'total_late_minutes' => $totalLateMinutes,
            ],
            'components_used' => $employee->activePayrollComponents->map(fn($c) => [
                'component_id' => $c->component_id,
                'code' => $c->component->code,
                'name' => $c->component->name,
                'amount' => $c->amount,
                'calculation_type' => $c->component->calculation_type,
                'rate_per_day' => $c->component->rate_per_day,
                'proration_type' => $c->component->proration_type,
            ])->toArray(),
            'calculation_params' => [
                'paid_days' => $paidDays,
                'attendance_only_days' => $attendanceOnlyDays,
                'basic_salary_uses_proration' => $basicSalaryUsesProration,
                'bpjs_base' => $bpjsBase,
                'taxable_income' => $taxableIncome,
            ],
            'services_used' => [
                'bpjs_calculator' => 'BpjsCalculator',
                'tax_calculator' => 'TaxCalculator (TER method)',
            ],
        ];

        // Create slip with data from LOCKED period summary
        return PayrollSlip::create([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'slip_number' => $slipNumber,
            'slip_date' => $period->end_date,

            // Employee snapshot
            'employee_nik' => $employee->nik,
            'employee_name' => $employee->full_name,
            'department' => $currentCareer?->department?->name,
            'position' => $currentCareer?->position?->name,
            'level' => $currentCareer?->level?->grade_code,

            // Working days from LOCKED period summary
            'working_days' => $scheduledWorkingDays,
            'actual_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $totalLeaveDays,

            // Components
            'earnings' => $earnings,
            'deductions' => $deductions,

            // Totals
            'gross_salary' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => max(0, $netSalary),

            // Tax (from service result)
            'tax_status' => $taxStatus,
            'taxable_income' => $taxableIncome,
            'tax_amount' => $taxResult['tax_amount'],

            // BPJS (from service result - legacy format)
            'bpjs_tk_company' => $bpjs['bpjs_tk_company'],
            'bpjs_tk_employee' => $bpjs['bpjs_tk_employee'],
            'bpjs_kes_company' => $bpjs['bpjs_kes_company'],
            'bpjs_kes_employee' => $bpjs['bpjs_kes_employee'],

            // Payment
            'payment_status' => 'pending',

            // Bank snapshot
            'bank_name' => $employee->sensitiveData?->bank_name,
            'bank_account_number' => $employee->sensitiveData?->bank_account_number,
            'bank_account_holder' => $employee->sensitiveData?->bank_account_holder,

            // Audit (Phase 1: Freeze & Audit)
            'calculation_snapshot' => $calculationSnapshot,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);

        // ========================================
        // PHASE 2: Create Normalized Slip Items for Reporting
        // ========================================
        $displayOrder = 0;

        // Create earning items
        foreach ($earnings as $earning) {
            \App\Models\PayrollSlipItem::create([
                'payroll_slip_id' => $slip->id,
                'payroll_component_id' => $earning['component_id'] ?? null,
                'component_code' => $earning['code'],
                'component_name' => $earning['name'],
                'type' => 'earning',
                'category' => $earning['category'],
                'base_amount' => $earning['base_amount'] ?? $earning['amount'],
                'final_amount' => $earning['amount'],
                'meta' => $earning['meta'] ?? null,
                'display_order' => ++$displayOrder,
                'is_taxable' => $earning['is_taxable'] ?? false,
            ]);
        }

        // Create deduction items
        foreach ($deductions as $deduction) {
            \App\Models\PayrollSlipItem::create([
                'payroll_slip_id' => $slip->id,
                'payroll_component_id' => $deduction['component_id'] ?? null,
                'component_code' => $deduction['code'],
                'component_name' => $deduction['name'],
                'type' => 'deduction',
                'category' => $deduction['category'],
                'base_amount' => $deduction['base_amount'] ?? $deduction['amount'],
                'final_amount' => $deduction['amount'],
                'meta' => $deduction['meta'] ?? null,
                'display_order' => ++$displayOrder,
                'is_taxable' => false,
            ]);
        }

        return $slip;
    }

    // ========================================
    // DEPRECATED: calculateTax removed
    // Tax calculation now handled by \App\Services\Payroll\TaxCalculator
    // Uses TER (Tarif Efektif Rata-rata) method per PP 58/2023
    // ========================================

    /**
     * Approve payroll period
     */
    public function approve($id)
    {
        DB::beginTransaction();

        try {
            $period = PayrollPeriod::findOrFail($id);

            if ($period->status !== 'processing') {
                return redirect()
                    ->back()
                    ->with('error', 'Hanya periode dengan status "Processing" yang dapat diapprove.');
            }

            $period->approve(auth()->user());

            DB::commit();

            Log::info('Payroll period approved', [
                'period_id' => $period->id,
                'approved_by' => auth()->id(),
            ]);

            return redirect()
                ->route('hris.payroll.periods.show', $period->id)
                ->with('success', 'Periode payroll berhasil di-approve.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Approve Period Error', [
                'error' => $e->getMessage(),
                'period_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mark period as paid
     */
    public function markAsPaid($id)
    {
        DB::beginTransaction();

        try {
            $period = PayrollPeriod::findOrFail($id);

            if ($period->status !== 'approved') {
                return redirect()
                    ->back()
                    ->with('error', 'Hanya periode yang sudah di-approve yang dapat di-mark as paid.');
            }

            $period->markAsPaid(auth()->user());

            DB::commit();

            Log::info('Payroll period marked as paid', [
                'period_id' => $period->id,
            ]);

            return redirect()
                ->route('hris.payroll.periods.show', $period->id)
                ->with('success', 'Periode payroll berhasil di-mark sebagai paid.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Mark as Paid Error', [
                'error' => $e->getMessage(),
                'period_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete payroll period
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $period = PayrollPeriod::findOrFail($id);

            if ($period->status !== 'draft') {
                return redirect()
                    ->back()
                    ->with('error', 'Hanya periode dengan status "Draft" yang dapat dihapus.');
            }

            $periodName = $period->period_name;
            $period->delete();

            DB::commit();

            Log::info('Payroll period deleted', [
                'period_id' => $id,
                'period_name' => $periodName,
            ]);

            return redirect()
                ->route('hris.payroll.periods.index')
                ->with('success', "Periode payroll \"{$periodName}\" berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Delete Period Error', [
                'error' => $e->getMessage(),
                'period_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}