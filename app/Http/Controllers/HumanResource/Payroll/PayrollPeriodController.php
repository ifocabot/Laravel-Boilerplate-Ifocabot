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

            $slipsCreated = 0;
            $errors = [];

            foreach ($employees as $employee) {
                try {
                    // Generate slip for this employee
                    $this->createSlipForEmployee($period, $employee);
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
                'errors_count' => count($errors),
            ]);

            $message = "{$slipsCreated} slip gaji berhasil di-generate.";
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

        // Prepare earnings array
        $earnings = [];
        $totalEarnings = 0;

        foreach ($employee->activePayrollComponents as $component) {
            if ($component->component->type === 'earning') {
                $earnings[] = [
                    'code' => $component->component->code,
                    'name' => $component->component->name,
                    'category' => $component->component->category,
                    'type' => 'earning',
                    'amount' => $component->amount,
                    'is_taxable' => $component->component->is_taxable,
                ];
                $totalEarnings += $component->amount;
            }
        }

        // Calculate tax (simplified PPh21 - you should implement proper calculation)
        $taxableIncome = collect($earnings)->where('is_taxable', true)->sum('amount');

        // Get tax status from sensitive data or default
        $taxStatus = $employee->sensitiveData?->tax_status ?? 'TK/0';
        $taxAmount = $this->calculateTax($taxableIncome, $taxStatus);

        // Calculate BPJS
        $bpjsBase = collect($earnings)
            ->filter(function ($earning) {
                return $earning['category'] === 'basic_salary';
            })
            ->sum('amount');

        $bpjsTkEmployee = $bpjsBase * 0.02; // 2%
        $bpjsKesEmployee = $bpjsBase * 0.01; // 1%
        $bpjsTkCompany = $bpjsBase * 0.0374; // 3.74%
        $bpjsKesCompany = $bpjsBase * 0.04; // 4%

        // Prepare deductions array
        $deductions = [
            [
                'code' => 'TAX_PPH21',
                'name' => 'PPh 21',
                'category' => 'statutory',
                'type' => 'deduction',
                'amount' => $taxAmount,
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

        // Calculate working days
        $workingDays = $period->start_date->diffInDays($period->end_date) + 1;

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

            // Working days (simplified - should be calculated from attendance)
            'working_days' => $workingDays,
            'actual_days' => $workingDays,
            'absent_days' => 0,
            'leave_days' => 0,

            // Components
            'earnings' => $earnings,
            'deductions' => $deductions,

            // Totals
            'gross_salary' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary,

            // Tax
            'tax_status' => $taxStatus,
            'taxable_income' => $taxableIncome,
            'tax_amount' => $taxAmount,

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