<?php

namespace App\Http\Controllers\HumanResource\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PayrollSlip;
use App\Models\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollSlipController extends Controller
{
    /**
     * Show payroll slip detail
     */
    public function show($id)
    {
        $slip = PayrollSlip::with(['period', 'employee'])->findOrFail($id);

        // Parse earnings and deductions
        $earnings = collect($slip->earnings ?? []);
        $deductions = collect($slip->deductions ?? []);

        // Group by category
        $earningsByCategory = $earnings->groupBy('category');
        $deductionsByCategory = $deductions->groupBy('category');

        return view('admin.hris.payroll.slips.show', compact(
            'slip',
            'earnings',
            'deductions',
            'earningsByCategory',
            'deductionsByCategory'
        ));
    }

    /**
     * Update payroll slip
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $slip = PayrollSlip::findOrFail($id);

            // Only allow editing if period is still draft or processing
            if (!in_array($slip->period->status, ['draft', 'processing'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Slip gaji hanya dapat diedit jika periode masih draft atau processing.');
            }

            $validated = $request->validate([
                'working_days' => 'nullable|integer|min:0',
                'actual_days' => 'nullable|integer|min:0',
                'absent_days' => 'nullable|integer|min:0',
                'leave_days' => 'nullable|integer|min:0',
                'notes' => 'nullable|string',
            ]);

            $slip->update($validated);

            // Recalculate if working days changed
            if ($request->filled('working_days') || $request->filled('actual_days')) {
                // TODO: Implement recalculation logic based on working days
            }

            DB::commit();

            Log::info('Payroll slip updated', [
                'slip_id' => $slip->id,
            ]);

            return redirect()
                ->route('hris.payroll.slips.show', $slip->id)
                ->with('success', 'Slip gaji berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payroll Slip Update Error', [
                'error' => $e->getMessage(),
                'slip_id' => $id,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete payroll slip
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $slip = PayrollSlip::findOrFail($id);
            $periodId = $slip->payroll_period_id;

            // Only allow deleting if period is still draft
            if ($slip->period->status !== 'draft') {
                return redirect()
                    ->back()
                    ->with('error', 'Slip gaji hanya dapat dihapus jika periode masih draft.');
            }

            $slip->delete();

            // Recalculate period totals
            $slip->period->calculateTotals();

            DB::commit();

            Log::info('Payroll slip deleted', [
                'slip_id' => $id,
            ]);

            return redirect()
                ->route('hris.payroll.periods.show', $periodId)
                ->with('success', 'Slip gaji berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Payroll Slip Delete Error', [
                'error' => $e->getMessage(),
                'slip_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Mark slip as paid
     */
    public function markAsPaid(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $slip = PayrollSlip::findOrFail($id);

            if ($slip->payment_status === 'paid') {
                return redirect()
                    ->back()
                    ->with('error', 'Slip gaji sudah dibayar.');
            }

            $validated = $request->validate([
                'payment_method' => 'required|string|max:50',
                'payment_reference' => 'nullable|string|max:100',
            ]);

            $slip->markAsPaid($validated['payment_method'], $validated['payment_reference']);

            DB::commit();

            Log::info('Payroll slip marked as paid', [
                'slip_id' => $slip->id,
            ]);

            return redirect()
                ->route('hris.payroll.slips.show', $slip->id)
                ->with('success', 'Slip gaji berhasil di-mark sebagai paid.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Mark Slip as Paid Error', [
                'error' => $e->getMessage(),
                'slip_id' => $id,
            ]);

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Download slip as PDF
     */
    public function downloadPdf($id)
    {
        $slip = PayrollSlip::with(['period', 'employee'])->findOrFail($id);

        // TODO: Implement PDF generation
        // You can use libraries like dompdf, mpdf, or snappy

        return redirect()
            ->back()
            ->with('info', 'Fitur download PDF akan segera tersedia.');
    }

    /**
     * Send slip via email
     */
    public function sendEmail($id)
    {
        $slip = PayrollSlip::with(['period', 'employee'])->findOrFail($id);

        // TODO: Implement email sending
        // Generate PDF and send via email to employee

        return redirect()
            ->back()
            ->with('info', 'Fitur kirim email akan segera tersedia.');
    }
}