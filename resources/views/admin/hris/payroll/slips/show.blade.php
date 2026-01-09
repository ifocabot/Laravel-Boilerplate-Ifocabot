@extends('layouts.admin')

@section('title', 'Detail Slip Gaji - ' . $slip->employee_name)

@section('content')
    <div class="space-y-6" x-data="slipDetail()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.payroll.periods.show', $slip->payroll_period_id) }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Slip Gaji</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $slip->slip_number }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hris.payroll.slips.download-pdf', $slip->id) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </a>

                @if($slip->payment_status === 'pending')
                    <button @click="openMarkAsPaidModal()" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Mark as Paid
                    </button>
                @endif
            </div>
        </div>

        {{-- Slip Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Company Header --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-8 text-white">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-1">PT. HAHAHIHI BANGKRUT BERSAMA</h2>
                        <p class="text-indigo-100 text-sm">Jl. Alamat Perusahaan No. 123, Jakarta</p>
                        <p class="text-indigo-100 text-sm">Phone: (021) 1234-5678 | Email: hr@company.com</p>
                    </div>
                    <div class="text-right">
                        <p class="text-indigo-100 text-xs mb-1">Slip Number</p>
                        <p class="text-xl font-bold">{{ $slip->slip_number }}</p>
                    </div>
                </div>
            </div>

            {{-- Employee & Period Info --}}
            <div class="p-6 border-b border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Employee Info --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Karyawan
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Nama</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->employee_name }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">NIK</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->employee_nik }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Departemen</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->department ?? '-' }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Posisi</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->position ?? '-' }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Level</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->level ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Period & Payment Info --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Periode</h3>
                        <div class="space-y-2">
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Periode</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->period->period_name }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Tanggal Slip</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">:
                                    {{ $slip->slip_date->format('d F Y') }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Hari Kerja</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">:
                                    {{ $slip->actual_days }}/{{ $slip->working_days }} hari</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Status Pajak</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->tax_status ?? '-' }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-sm text-gray-500 w-32">Status Bayar</p>
                                <p class="text-sm font-semibold flex-1">:
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $slip->payment_status_badge_class }}">
                                        {{ $slip->payment_status_label }}
                                    </span>
                                </p>
                            </div>
                            @if($slip->generated_by)
                            <div class="flex items-start mt-3 pt-3 border-t border-gray-100">
                                <p class="text-sm text-gray-500 w-32">Di-generate oleh</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->generatedBy?->name ?? 'System' }}
                                    <span class="text-xs text-gray-500 ml-1">({{ $slip->generated_at?->format('d M Y H:i') }})</span>
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Earnings & Deductions --}}
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Earnings Section --}}
                    <div>
                        <div class="bg-green-50 rounded-xl p-4 mb-4">
                            <h3 class="text-sm font-bold text-green-900 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Pendapatan (Earnings)
                            </h3>
                        </div>

                        <div class="space-y-3">
                            @foreach($earningsByCategory as $category => $items)
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">
                                        {{ ucwords(str_replace('_', ' ', $category)) }}
                                    </p>
                                    @foreach($items as $earning)
                                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">{{ $earning['name'] }}</p>
                                                @if($earning['is_taxable'])
                                                    <p class="text-xs text-orange-600">Taxable</p>
                                                @endif
                                            </div>
                                            <p class="text-sm font-bold text-gray-900">
                                                Rp {{ number_format($earning['amount'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            {{-- Gross Salary Total --}}
                            <div
                                class="flex items-center justify-between py-3 bg-green-50 rounded-lg px-4 border-2 border-green-200">
                                <p class="text-sm font-bold text-green-900">Total Pendapatan (Gross)</p>
                                <p class="text-lg font-bold text-green-900">
                                    {{ $slip->formatted_gross_salary }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Deductions Section --}}
                    <div>
                        <div class="bg-red-50 rounded-xl p-4 mb-4">
                            <h3 class="text-sm font-bold text-red-900 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                                Potongan (Deductions)
                            </h3>
                        </div>

                        <div class="space-y-3">
                            @foreach($deductionsByCategory as $category => $items)
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">
                                        {{ ucwords(str_replace('_', ' ', $category)) }}
                                    </p>
                                    @foreach($items as $deduction)
                                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                            <p class="text-sm font-medium text-gray-900 flex-1">{{ $deduction['name'] }}</p>
                                            <p class="text-sm font-bold text-gray-900">
                                                Rp {{ number_format($deduction['amount'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach

                            {{-- Total Deductions --}}
                            <div
                                class="flex items-center justify-between py-3 bg-red-50 rounded-lg px-4 border-2 border-red-200">
                                <p class="text-sm font-bold text-red-900">Total Potongan</p>
                                <p class="text-lg font-bold text-red-900">
                                    {{ $slip->formatted_total_deductions }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Net Salary (Take Home Pay) --}}
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
                <div class="flex items-center justify-between">
                    <div class="text-white">
                        <p class="text-sm text-indigo-100 mb-1">Gaji Bersih / Take Home Pay</p>
                        <p class="text-4xl font-bold">{{ $slip->formatted_net_salary }}</p>
                        <p class="text-xs text-indigo-100 mt-2">
                            Terbilang: {{ $slip->net_salary > 0 ? ucwords(terbilang($slip->net_salary)) . ' Rupiah' : '-' }}
                        </p>
                    </div>
                    <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- BPJS Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Informasi BPJS
                </h3>

                <div class="space-y-3">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-blue-900 mb-2">BPJS Ketenagakerjaan</p>
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-xs text-blue-700">Iuran Perusahaan</p>
                            <p class="text-sm font-bold text-blue-900">Rp
                                {{ number_format($slip->bpjs_tk_company, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-blue-700">Iuran Karyawan</p>
                            <p class="text-sm font-bold text-blue-900">Rp
                                {{ number_format($slip->bpjs_tk_employee, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-green-900 mb-2">BPJS Kesehatan</p>
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-xs text-green-700">Iuran Perusahaan</p>
                            <p class="text-sm font-bold text-green-900">Rp
                                {{ number_format($slip->bpjs_kes_company, 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-green-700">Iuran Karyawan</p>
                            <p class="text-sm font-bold text-green-900">Rp
                                {{ number_format($slip->bpjs_kes_employee, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tax & Bank Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Informasi Pajak & Bank
                </h3>

                <div class="space-y-4">
                    {{-- Tax Info --}}
                    <div class="bg-orange-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-orange-900 mb-2">Perhitungan PPh 21</p>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-orange-700">Penghasilan Kena Pajak</p>
                                <p class="text-sm font-bold text-orange-900">Rp
                                    {{ number_format($slip->taxable_income, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-orange-700">PPh 21 Dipotong</p>
                                <p class="text-sm font-bold text-orange-900">Rp
                                    {{ number_format($slip->tax_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Bank Info --}}
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs font-semibold text-gray-900 mb-2">Informasi Rekening</p>
                        <div class="space-y-1">
                            <div class="flex items-start">
                                <p class="text-xs text-gray-600 w-24">Bank</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">: {{ $slip->bank_name ?? '-' }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-xs text-gray-600 w-24">No. Rekening</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">:
                                    {{ $slip->bank_account_number ?? '-' }}</p>
                            </div>
                            <div class="flex items-start">
                                <p class="text-xs text-gray-600 w-24">Atas Nama</p>
                                <p class="text-sm font-semibold text-gray-900 flex-1">:
                                    {{ $slip->bank_account_holder ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        @if($slip->payment_status === 'paid')
            <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-green-900 mb-1">Gaji Sudah Dibayarkan</h4>
                        <div class="text-xs text-green-700 space-y-1">
                            <p>Tanggal Pembayaran: <strong>{{ $slip->payment_date?->format('d F Y H:i') }}</strong></p>
                            <p>Metode: <strong>{{ ucfirst($slip->payment_method ?? '-') }}</strong></p>
                            @if($slip->payment_reference)
                                <p>Referensi: <strong>{{ $slip->payment_reference }}</strong></p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Notes --}}
        @if($slip->notes)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Catatan</h3>
                <p class="text-sm text-gray-900">{{ $slip->notes }}</p>
            </div>
        @endif

        {{-- Footer Note --}}
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-600 text-center">
                Slip gaji ini digenerate secara otomatis oleh sistem. Untuk pertanyaan, hubungi HR Department.
            </p>
        </div>

        {{-- Mark as Paid Modal --}}
        <div x-show="showMarkAsPaidModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="closeMarkAsPaidModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showMarkAsPaidModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    @click="closeMarkAsPaidModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showMarkAsPaidModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form action="{{ route('hris.payroll.slips.mark-as-paid', $slip->id) }}" method="POST">
                        @csrf

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Mark as Paid</h3>
                                        <p class="text-sm text-green-100">{{ $slip->employee_name }}</p>
                                    </div>
                                </div>
                                <button @click="closeMarkAsPaidModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-6">
                            {{-- Info --}}
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-green-900 mb-1">Konfirmasi Pembayaran</p>
                                        <p class="text-xs text-green-700">
                                            Pastikan gaji sebesar <strong>{{ $slip->formatted_net_salary }}</strong> sudah
                                            ditransfer ke rekening karyawan.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment Method --}}
                            <div>
                                <label for="payment_method" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Metode Pembayaran <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method" id="payment_method" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="">Pilih Metode</option>
                                    <option value="transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>

                            {{-- Payment Reference --}}
                            <div>
                                <label for="payment_reference" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nomor Referensi / Transaction ID
                                </label>
                                <input type="text" name="payment_reference" id="payment_reference"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Contoh: TRX123456789">
                                <p class="mt-1 text-xs text-gray-500">Opsional - Nomor referensi dari bank</p>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeMarkAsPaidModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Konfirmasi Pembayaran
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function slipDetail() {
                return {
                    showMarkAsPaidModal: false,

                    openMarkAsPaidModal() {
                        this.showMarkAsPaidModal = true;
                    },

                    closeMarkAsPaidModal() {
                        this.showMarkAsPaidModal = false;
                    }
                }
            }
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
@endsection