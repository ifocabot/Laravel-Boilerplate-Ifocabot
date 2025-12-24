@extends('layouts.admin')

@section('title', 'Riwayat Slip Gaji')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Riwayat Slip Gaji</h1>
            <p class="text-sm text-gray-500 mt-1">Lihat dan unduh slip gaji Anda</p>
        </div>

        @if($currentSlip)
            {{-- Current Month Summary --}}
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm">Slip Gaji Terakhir</p>
                        <h2 class="text-xl font-bold mt-1">{{ $currentSlip->payrollPeriod->name ?? 'Periode Terbaru' }}</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-green-100 text-sm">Take Home Pay</p>
                        <p class="text-3xl font-bold">Rp {{ number_format($currentSlip->net_salary, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-green-500 flex items-center justify-between">
                    <div class="flex gap-6">
                        <div>
                            <p class="text-green-200 text-xs">Penghasilan</p>
                            <p class="font-semibold">Rp {{ number_format($currentSlip->gross_salary, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-green-200 text-xs">Potongan</p>
                            <p class="font-semibold">Rp {{ number_format($currentSlip->total_deductions, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('ess.payroll.download', $currentSlip->id) }}"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>
        @endif

        {{-- Payroll History --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="font-semibold text-gray-900">Riwayat Slip Gaji</h2>
            </div>
            @if($payrollSlips->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Periode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Penghasilan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Potongan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Take Home Pay</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($payrollSlips as $slip)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $slip->payrollPeriod->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">Rp {{ number_format($slip->gross_salary, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-red-600">- Rp
                                    {{ number_format($slip->total_deductions, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-green-600">Rp
                                    {{ number_format($slip->net_salary, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $slip->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $slip->status === 'paid' ? 'Dibayar' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('ess.payroll.download', $slip->id) }}"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm">Download</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $payrollSlips->links() }}</div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-gray-500">Belum ada data slip gaji</p>
                </div>
            @endif
        </div>
    </div>
@endsection