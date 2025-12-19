@extends('layouts.admin')

@section('title', 'Detail Periode - ' . $period->period_name)

@section('content')
    <div class="space-y-6" x-data="periodDetail()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.payroll.periods.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Periode Payroll</h1>
                </div>
            </div>
        </div>

        {{-- Period Header Card --}}
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="text-white">
                        <h2 class="text-2xl font-bold mb-2">{{ $period->period_name }}</h2>
                        <p class="text-indigo-100 text-sm mb-4">{{ $period->period_code }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div>
                                <p class="text-indigo-100 text-xs mb-1">Periode</p>
                                <p class="font-semibold">{{ $period->start_date->format('d M') }} -
                                    {{ $period->end_date->format('d M Y') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-indigo-100 text-xs mb-1">Tanggal Pembayaran</p>
                                <p class="font-semibold">{{ $period->payment_date->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-indigo-100 text-xs mb-1">Status</p>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white">
                                    {{ ucfirst($period->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($period->status === 'draft')
                            <form action="{{ route('hris.payroll.periods.generate-slips', $period->id) }}" method="POST"
                                onsubmit="return confirm('Generate slip gaji untuk semua karyawan aktif?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-xl transition-colors backdrop-blur">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Generate Slips
                                </button>
                            </form>
                        @endif

                        @if($period->status === 'processing')
                            <form action="{{ route('hris.payroll.periods.approve', $period->id) }}" method="POST"
                                onsubmit="return confirm('Approve periode payroll ini?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-500 hover:bg-green-600 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve
                                </button>
                            </form>
                        @endif

                        @if($period->status === 'approved')
                            <form action="{{ route('hris.payroll.periods.mark-as-paid', $period->id) }}" method="POST"
                                onsubmit="return confirm('Mark periode ini sebagai paid?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-500 hover:bg-purple-600 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Mark as Paid
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Karyawan</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalSlips }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Gross Salary</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">{{ $period->formatted_gross_salary }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Deductions</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">Rp
                            {{ number_format($period->total_deductions, 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Net Salary</p>
                        <h3 class="text-xl font-bold text-gray-900 mt-2">{{ $period->formatted_net_salary }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Status Summary --}}
        @if($totalSlips > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Status Pembayaran</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex items-center gap-3 p-4 bg-green-50 rounded-xl border border-green-100">
                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-green-700 font-medium">Paid</p>
                            <p class="text-2xl font-bold text-green-900">{{ $paidSlips }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-yellow-50 rounded-xl border border-yellow-100">
                        <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-yellow-700 font-medium">Pending</p>
                            <p class="text-2xl font-bold text-yellow-900">{{ $pendingSlips }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-700 font-medium">Total Slips</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalSlips }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Slips List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Daftar Slip Gaji</h2>

                @if($totalSlips > 0)
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="searchQuery" placeholder="Cari karyawan..."
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">

                        <select x-model="filterStatus"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                @endif
            </div>

            @if($period->slips->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Departemen
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Gross Salary
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Deductions
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Net Salary
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="slip in filteredSlips" :key="slip.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                                <span x-text="slip.employee_name.substring(0, 2).toUpperCase()"></span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900" x-text="slip.employee_name"></p>
                                                <p class="text-xs text-gray-500" x-text="slip.employee_nik"></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900" x-text="slip.department || '-'"></p>
                                        <p class="text-xs text-gray-500" x-text="slip.position || '-'"></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900"
                                            x-text="formatCurrency(slip.gross_salary)"></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-red-600"
                                            x-text="formatCurrency(slip.total_deductions)"></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-gray-900" x-text="formatCurrency(slip.net_salary)"></p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                            :class="{
                                                                        'bg-yellow-100 text-yellow-700': slip.payment_status === 'pending',
                                                                        'bg-green-100 text-green-700': slip.payment_status === 'paid',
                                                                        'bg-red-100 text-red-700': slip.payment_status === 'failed'
                                                                    }" x-text="slip.payment_status_label">
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a :href="`/hris/payroll/slips/${slip.id}`"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Belum ada slip gaji</p>
                    <p class="text-gray-400 text-sm mb-4">Klik tombol "Generate Slips" untuk membuat slip gaji</p>
                </div>
            @endif
        </div>

        {{-- Period Info & Notes --}}
        @if($period->notes || $period->approved_by)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Tambahan</h3>

                @if($period->approved_by)
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-1">Di-approve oleh</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $period->approvedBy->name }}</p>
                        <p class="text-xs text-gray-500">{{ $period->approved_at?->format('d F Y H:i') }}</p>
                    </div>
                @endif

                @if($period->notes)
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Catatan</p>
                        <p class="text-sm text-gray-900">{{ $period->notes }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function periodDetail() {
                return {
                    slips: @json($period->slips),
                    searchQuery: '',
                    filterStatus: '',

                    get filteredSlips() {
                        return this.slips.filter(slip => {
                            const matchesSearch = !this.searchQuery ||
                                slip.employee_name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                slip.employee_nik.toLowerCase().includes(this.searchQuery.toLowerCase());

                            const matchesStatus = !this.filterStatus ||
                                slip.payment_status === this.filterStatus;

                            return matchesSearch && matchesStatus;
                        });
                    },

                    formatCurrency(amount) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                    }
                }
            }
        </script>
    @endpush
@endsection