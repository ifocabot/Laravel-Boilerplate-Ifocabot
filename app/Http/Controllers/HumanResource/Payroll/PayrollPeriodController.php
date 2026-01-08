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
            $nextMonth = $lastPeriod->end_date->addDay();
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
            // ========================================
            $slipsCreated = 0;
            $errors = [];

            foreach ($employees as $employee) {
                try {
                    $periodSummary = $periodSummaries[$employee->id];
                    $this->createSlipFromPeriodSummary($period, $employee, $periodSummary);
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

    /**
     * Create slip for single employee
     */
    private function createSlipForEmployee(PayrollPeriod $period, Employee $employee)
    {
        // Get current career history
        $currentCareer = $employee->currentCareer;

        // ========================================
        // ATTENDANCE DATA FROM SUMMARIES
        // ========================================
        $attendanceSummaries = \App\Models\AttendanceSummary::where('employee_id', $employee->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->get();

        // Calculate attendance statistics
        $presentDays = $attendanceSummaries->whereIn('status', ['present', 'late'])->count();
        $lateDays = $attendanceSummaries->where('status', 'late')->count();
        $absentDays = $attendanceSummaries->whereIn('status', ['absent', 'alpha'])->count();
        $leaveDays = $attendanceSummaries->whereIn('status', ['leave', 'sick', 'permission'])->count();
        $offDays = $attendanceSummaries->whereIn('status', ['offday', 'holiday'])->count();

        // Total scheduled working days (exclude offday/holiday)
        $scheduledWorkingDays = $attendanceSummaries->whereNotIn('status', ['offday', 'holiday'])->count();

        // If no attendance data, calculate from schedules
        if ($scheduledWorkingDays === 0) {
            $scheduledWorkingDays = \App\Models\EmployeeSchedule::where('employee_id', $employee->id)
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->where('is_day_off', false)
                ->where('is_holiday', false)
                ->count();
        }

        // Overtime data
        $totalOvertimeMinutes = $attendanceSummaries->sum('approved_overtime_minutes') ?: 0;
        $totalLateMinutes = $attendanceSummaries->sum('late_minutes') ?: 0;

        // ========================================
        // EARNINGS CALCULATION
        // ========================================
        $earnings = [];
        $totalEarnings = 0;

        foreach ($employee->activePayrollComponents as $component) {
            if ($component->component->type === 'earning') {
                $amount = $component->amount;

                // Prorate based on attendance for basic salary
                if ($component->component->code === 'BASIC_SALARY' && $scheduledWorkingDays > 0) {
                    // Prorate: (present days / scheduled days) * amount
                    $attendanceRatio = $presentDays / $scheduledWorkingDays;
                    $amount = round($component->amount * $attendanceRatio, 0);
                }

                // Daily allowances like MEAL should be per present day
                if (in_array($component->component->code, ['MEAL', 'TRANSPORT'])) {
                    // If stored as monthly, prorate to present days
                    // Assuming 22 working days per month as standard
                    $dailyRate = $component->amount / 22;
                    $amount = round($dailyRate * $presentDays, 0);
                }

                $earnings[] = [
                    'code' => $component->component->code,
                    'name' => $component->component->name,
                    'category' => $component->component->category,
                    'type' => 'earning',
                    'amount' => $amount,
                    'is_taxable' => $component->component->is_taxable,
                ];
                $totalEarnings += $amount;
            }
        }

        // Add overtime earnings
        if ($totalOvertimeMinutes > 0) {
            $basicSalary = collect($earnings)->where('code', 'BASIC_SALARY')->first()['amount'] ?? 0;
            $hourlyRate = $basicSalary > 0 ? $basicSalary / 173 : 0; // 173 hours/month standard
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
        // DEDUCTIONS CALCULATION
        // ========================================
        $taxableIncome = collect($earnings)->where('is_taxable', true)->sum('amount');
        $taxStatus = $employee->sensitiveData?->tax_status ?? 'TK/0';
        $taxAmount = $this->calculateTax($taxableIncome, $taxStatus);

        // Calculate BPJS from base salary
        $bpjsBase = collect($earnings)
            ->filter(fn($earning) => $earning['category'] === 'basic_salary')
            ->sum('amount');

        $bpjsTkEmployee = round($bpjsBase * 0.02, 0);
        $bpjsKesEmployee = round($bpjsBase * 0.01, 0);
        $bpjsTkCompany = round($bpjsBase * 0.0374, 0);
        $bpjsKesCompany = round($bpjsBase * 0.04, 0);

        // Deductions array
        $deductions = [
            [
                'code' => 'TAX_PPH21',
                'name' => 'PPh 21',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => round($taxAmount, 0),
            ],
            [
                'code' => 'BPJS_TK_EMPLOYEE',
                'name' => 'BPJS Ketenagakerjaan',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $bpjsTkEmployee,
            ],
            [
                'code' => 'BPJS_KES_EMPLOYEE',
                'name' => 'BPJS Kesehatan',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $bpjsKesEmployee,
            ],
        ];

        // Add late deduction
        if ($totalLateMinutes > 0) {
            $lateDeduction = round($totalLateMinutes * 1000, 0); // Rp1.000/menit telat
            $deductions[] = [
                'code' => 'LATE_DEDUCTION',
                'name' => 'Potongan Terlambat (' . $totalLateMinutes . ' menit)',
                'category' => 'other_deduction',
                'type' => 'deduction',
                'amount' => $lateDeduction,
            ];
        }

        // Add absent deduction
        if ($absentDays > 0) {
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
        foreach ($employee->activePayrollComponents as $component) {
            if ($component->component->type === 'deduction') {
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

        // Create slip
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

            // Working days from attendance
            'working_days' => $scheduledWorkingDays,
            'actual_days' => $presentDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,

            // Components
            'earnings' => $earnings,
            'deductions' => $deductions,

            // Totals
            'gross_salary' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => max(0, $netSalary),

            // Tax
            'tax_status' => $taxStatus,
            'taxable_income' => $taxableIncome,
            'tax_amount' => round($taxAmount, 0),

            // BPJS
            'bpjs_tk_company' => $bpjsTkCompany,
            'bpjs_tk_employee' => $bpjsTkEmployee,
            'bpjs_kes_company' => $bpjsKesCompany,
            'bpjs_kes_employee' => $bpjsKesEmployee,

            // Payment
            'payment_status' => 'pending',

            // Bank snapshot
            'bank_name' => $employee->sensitiveData?->bank_name,
            'bank_account_number' => $employee->sensitiveData?->bank_account_number,
            'bank_account_holder' => $employee->sensitiveData?->bank_account_holder,
        ]);
    }

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
        $leaveDays = $periodSummary->leave_days + $periodSummary->sick_days + $periodSummary->permission_days;
        $scheduledWorkingDays = $periodSummary->scheduled_work_days;
        $totalOvertimeMinutes = $periodSummary->total_approved_overtime_minutes;
        $totalLateMinutes = $periodSummary->total_late_minutes;

        // Fallback if no schedule data
        if ($scheduledWorkingDays <= 0) {
            $scheduledWorkingDays = 22; // Default 22 working days
        }

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
                    switch ($component->proration_type) {
                        case 'daily':
                            // Prorate per working day
                            if ($scheduledWorkingDays > 0) {
                                $dailyRate = $empComponent->amount / $scheduledWorkingDays;
                                $amount = round($dailyRate * $presentDays, 0);
                            }
                            break;
                        case 'attendance':
                            // Prorate based on attendance ratio
                            if ($scheduledWorkingDays > 0) {
                                $attendanceRatio = $presentDays / $scheduledWorkingDays;
                                $amount = round($empComponent->amount * $attendanceRatio, 0);
                            }
                            break;
                        case 'none':
                        default:
                            // Full amount (no proration) - keep original amount
                            break;
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
            $basicSalary = collect($earnings)->where('code', 'BASIC_SALARY')->first()['amount'] ?? 0;
            $hourlyRate = $basicSalary > 0 ? $basicSalary / 173 : 0;
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
        // DEDUCTIONS CALCULATION
        // ========================================
        $taxableIncome = collect($earnings)->where('is_taxable', true)->sum('amount');
        $taxStatus = $employee->sensitiveData?->tax_status ?? 'TK/0';
        $taxAmount = $this->calculateTax($taxableIncome, $taxStatus);

        // Calculate BPJS from base salary
        $bpjsBase = collect($earnings)
            ->filter(fn($earning) => $earning['category'] === 'basic_salary')
            ->sum('amount');

        $bpjsTkEmployee = round($bpjsBase * 0.02, 0);
        $bpjsKesEmployee = round($bpjsBase * 0.01, 0);
        $bpjsTkCompany = round($bpjsBase * 0.0374, 0);
        $bpjsKesCompany = round($bpjsBase * 0.04, 0);

        // Deductions array
        $deductions = [
            [
                'code' => 'TAX_PPH21',
                'name' => 'PPh 21',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => round($taxAmount, 0),
            ],
            [
                'code' => 'BPJS_TK_EMPLOYEE',
                'name' => 'BPJS Ketenagakerjaan',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $bpjsTkEmployee,
            ],
            [
                'code' => 'BPJS_KES_EMPLOYEE',
                'name' => 'BPJS Kesehatan',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $bpjsKesEmployee,
            ],
        ];

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

        // Add absent deduction
        if ($absentDays > 0) {
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
        foreach ($employee->activePayrollComponents as $component) {
            if ($component->component->type === 'deduction') {
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
            'leave_days' => $leaveDays,

            // Components
            'earnings' => $earnings,
            'deductions' => $deductions,

            // Totals
            'gross_salary' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => max(0, $netSalary),

            // Tax
            'tax_status' => $taxStatus,
            'taxable_income' => $taxableIncome,
            'tax_amount' => round($taxAmount, 0),

            // BPJS
            'bpjs_tk_company' => $bpjsTkCompany,
            'bpjs_tk_employee' => $bpjsTkEmployee,
            'bpjs_kes_company' => $bpjsKesCompany,
            'bpjs_kes_employee' => $bpjsKesEmployee,

            // Payment
            'payment_status' => 'pending',

            // Bank snapshot
            'bank_name' => $employee->sensitiveData?->bank_name,
            'bank_account_number' => $employee->sensitiveData?->bank_account_number,
            'bank_account_holder' => $employee->sensitiveData?->bank_account_holder,
        ]);
    }

    /**
     * Simplified tax calculation (PPh21)
     * This is a placeholder - implement proper PPh21 calculation
     */
    private function calculateTax(float $taxableIncome, ?string $taxStatus): float
    {
        // PTKP (Penghasilan Tidak Kena Pajak) 2024
        $ptkp = match ($taxStatus) {
            'TK/0' => 54000000,
            'TK/1' => 58500000,
            'TK/2' => 63000000,
            'TK/3' => 67500000,
            'K/0' => 58500000,
            'K/1' => 63000000,
            'K/2' => 67500000,
            'K/3' => 72000000,
            default => 54000000,
        };

        // Annual taxable income
        $annualIncome = $taxableIncome * 12;
        $pkp = $annualIncome - $ptkp;

        if ($pkp <= 0) {
            return 0;
        }

        // Progressive tax rates (2024)
        $tax = 0;

        if ($pkp <= 60000000) {
            $tax = $pkp * 0.05;
        } elseif ($pkp <= 250000000) {
            $tax = (60000000 * 0.05) + (($pkp - 60000000) * 0.15);
        } elseif ($pkp <= 500000000) {
            $tax = (60000000 * 0.05) + (190000000 * 0.15) + (($pkp - 250000000) * 0.25);
        } else {
            $tax = (60000000 * 0.05) + (190000000 * 0.15) + (250000000 * 0.25) + (($pkp - 500000000) * 0.30);
        }

        // Monthly tax
        return $tax / 12;
    }

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

            $period->markAsPaid();

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