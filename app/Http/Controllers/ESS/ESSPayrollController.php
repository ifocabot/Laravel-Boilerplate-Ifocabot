<?php

namespace App\Http\Controllers\ESS;

use App\Http\Controllers\Controller;
use App\Models\PayrollSlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ESSPayrollController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('ess.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        // Get payroll history
        $payrollSlips = PayrollSlip::with('payrollPeriod')
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Current/latest slip
        $currentSlip = PayrollSlip::with(['payrollPeriod', 'details.component'])
            ->where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return view('admin.ess.payroll.index', compact('employee', 'payrollSlips', 'currentSlip'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $slip = PayrollSlip::with(['payrollPeriod', 'details.component'])
            ->where('employee_id', $employee->id)
            ->findOrFail($id);

        return view('admin.ess.payroll.show', compact('employee', 'slip'));
    }

    public function download($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $slip = PayrollSlip::with(['payrollPeriod', 'details.component', 'employee'])
            ->where('employee_id', $employee->id)
            ->findOrFail($id);

        $pdf = Pdf::loadView('admin.ess.payroll.pdf', compact('slip', 'employee'));

        $filename = 'slip-gaji-' . $slip->payrollPeriod->name . '-' . $employee->nik . '.pdf';

        return $pdf->download($filename);
    }
}
