@extends('layouts.admin')

@section('title', 'Approval Overtime')

@section('content')
    <div class="space-y-6" x-data="overtimeApproval()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.attendance.summaries.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Approval Overtime</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Kelola dan approve overtime karyawan</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="bulkApprove()" type="button" x-show="selectedIds.length > 0"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Approve <span x-text="selectedIds.length"></span> Terpilih
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['pending'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Approved</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['approved'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Jam Pending</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">
                            {{ number_format($stats['total_pending_hours'], 1) }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">Jam</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-100">
                <nav class="flex">
                    <a href="{{ route('hris.attendance.summaries.overtime-approvals', ['status' => 'pending']) }}"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $status === 'pending' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Pending ({{ $stats['pending'] }})
                    </a>
                    <a href="{{ route('hris.attendance.summaries.overtime-approvals', ['status' => 'approved']) }}"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $status === 'approved' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Approved ({{ $stats['approved'] }})
                    </a>
                </nav>
            </div>

            @if($overtimeRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                @if($status === 'pending')
                                    <th class="px-6 py-3 w-12">
                                        <input type="checkbox" @change="toggleSelectAll($event)"
                                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Karyawan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Shift
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Jam Kerja
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Overtime
                                </th>
                                @if($status === 'approved')
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Approved
                                    </th>
                                @endif
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($overtimeRequests as $request)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    @if($status === 'pending')
                                        <td class="px-6 py-4">
                                            <input type="checkbox" value="{{ $request->id }}" @change="toggleSelect({{ $request->id }})"
                                                :checked="selectedIds.includes({{ $request->id }})"
                                                class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                    @endif
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $request->formatted_date }}</p>
                                            <p class="text-xs text-gray-500">{{ $request->day_name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm font-bold text-indigo-600">
                                                    {{ strtoupper(substr($request->employee->full_name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $request->employee->full_name }}
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $request->employee->nik }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($request->shift)
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $request->shift->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $request->shift->time_range }}</p>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $request->formatted_total_work }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->total_work_hours }} jam</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-orange-600">{{ $request->formatted_overtime }}</p>
                                        <p class="text-xs text-gray-500">{{ round($request->overtime_minutes / 60, 2) }} jam</p>
                                    </td>
                                    @if($status === 'approved')
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-green-600">{{ $request->formatted_approved_overtime }}</p>
                                            <p class="text-xs text-gray-500">{{ $request->overtime_hours }} jam</p>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($status === 'pending')
                                                <button @click="openApproveModal({{ $request->id }}, {{ $request->overtime_minutes }})"
                                                    type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                                    title="Approve">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                                <button @click="openRejectModal({{ $request->id }})" type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Reject">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $overtimeRequests->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Tidak ada overtime
                        {{ $status === 'pending' ? 'yang perlu diapprove' : 'yang sudah diapprove' }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Approve Modal --}}
        <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="closeApproveModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showApproveModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    @click="closeApproveModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showApproveModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form @submit.prevent="approveOvertime()">
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Approve Overtime</h3>
                                        <p class="text-sm text-green-100">Setujui overtime karyawan</p>
                                    </div>
                                </div>
                                <button @click="closeApproveModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-4">
                            {{-- Overtime Minutes --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jam Overtime Disetujui <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <input type="number" x-model="approveForm.approved_minutes" required min="0"
                                        class="flex-1 px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Menit">
                                    <div class="text-sm text-gray-500">
                                        = <span x-text="(approveForm.approved_minutes / 60).toFixed(2)"></span> jam
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    System menghitung: <span x-text="(approveForm.system_overtime / 60).toFixed(2)"></span>
                                    jam
                                </p>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan (Opsional)</label>
                                <textarea x-model="approveForm.notes" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    placeholder="Catatan approval..."></textarea>
                            </div>

                            {{-- Info --}}
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-green-900 mb-1">Informasi</p>
                                        <p class="text-xs text-green-700">
                                            Overtime yang disetujui akan digunakan untuk kalkulasi payroll.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeApproveModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                Approve Overtime
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="closeRejectModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRejectModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    @click="closeRejectModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showRejectModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form @submit.prevent="rejectOvertime()">
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-red-500 to-pink-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Reject Overtime</h3>
                                        <p class="text-sm text-red-100">Tolak overtime karyawan</p>
                                    </div>
                                </div>
                                <button @click="closeRejectModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-4">
                            {{-- Reason --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alasan Penolakan <span class="text-red-500">*</span>
                                </label>
                                <textarea x-model="rejectForm.reason" rows="4" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Jelaskan alasan penolakan overtime..."></textarea>
                            </div>

                            {{-- Warning --}}
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-red-900 mb-1">Perhatian</p>
                                        <p class="text-xs text-red-700">
                                            Overtime yang ditolak tidak akan dihitung dalam payroll.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeRejectModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                Tolak Overtime
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                function overtimeApproval() {
                    return {
                        selectedIds: [],
                        showApproveModal: false,
                        showRejectModal: false,
                        currentSummaryId: null,

                        approveForm: {
                            approved_minutes: 0,
                            system_overtime: 0,
                            notes: ''
                        },

                        rejectForm: {
                            reason: ''
                        },

                        toggleSelectAll(event) {
                            if (event.target.checked) {
                                // Select all checkboxes on page
                                const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                                this.selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
                            } else {
                                this.selectedIds = [];
                            }
                        },

                        toggleSelect(id) {
                            const index = this.selectedIds.indexOf(id);
                            if (index > -1) {
                                this.selectedIds.splice(index, 1);
                            } else {
                                this.selectedIds.push(id);
                            }
                        },

                        openApproveModal(summaryId, overtimeMinutes) {
                            this.currentSummaryId = summaryId;
                            this.approveForm = {
                                approved_minutes: overtimeMinutes,
                                system_overtime: overtimeMinutes,
                                notes: ''
                            };
                            this.showApproveModal = true;
                        },

                        closeApproveModal() {
                            this.showApproveModal = false;
                            setTimeout(() => {
                                this.currentSummaryId = null;
                                this.approveForm = {
                                    approved_minutes: 0,
                                    system_overtime: 0,
                                    notes: ''
                                };
                            }, 300);
                        },

                        async approveOvertime() {
                            if (!this.currentSummaryId) return;

                            try {
                                const response = await fetch(`/hris/attendance/summaries/${this.currentSummaryId}/approve-overtime`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        approved_minutes: this.approveForm.approved_minutes,
                                        notes: this.approveForm.notes
                                    })
                                });

                                const data = await response.json();

                                if (data.success) {
                                    this.closeApproveModal();
                                    this.showToast('success', data.message);
                                    setTimeout(() => window.location.reload(), 800);
                                } else {
                                    alert(data.message || 'Terjadi kesalahan');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat approve overtime');
                            }
                        },

                        openRejectModal(summaryId) {
                            this.currentSummaryId = summaryId;
                            this.rejectForm.reason = '';
                            this.showRejectModal = true;
                        },

                        closeRejectModal() {
                            this.showRejectModal = false;
                            setTimeout(() => {
                                this.currentSummaryId = null;
                                this.rejectForm.reason = '';
                            }, 300);
                        },

                        async rejectOvertime() {
                            if (!this.currentSummaryId || !this.rejectForm.reason) return;

                            try {
                                const response = await fetch(`/hris/attendance/summaries/${this.currentSummaryId}/reject-overtime`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        reason: this.rejectForm.reason
                                    })
                                });

                                const data = await response.json();

                                if (data.success) {
                                    this.closeRejectModal();
                                    this.showToast('success', data.message);
                                    setTimeout(() => window.location.reload(), 800);
                                } else {
                                    alert(data.message || 'Terjadi kesalahan');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat reject overtime');
                            }
                        },

                        async bulkApprove() {
                            if (this.selectedIds.length === 0) {
                                alert('Pilih minimal 1 overtime untuk diapprove');
                                return;
                            }

                            if (!confirm(`Approve ${this.selectedIds.length} overtime terpilih?`)) {
                                return;
                            }

                            try {
                                const response = await fetch('/hris/attendance/summaries/bulk-approve-overtime', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        summary_ids: this.selectedIds
                                    })
                                });

                                const data = await response.json();

                                if (data.success) {
                                    this.selectedIds = [];
                                    this.showToast('success', data.message);
                                    setTimeout(() => window.location.reload(), 800);
                                } else {
                                    alert(data.message || 'Terjadi kesalahan');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat bulk approve');
                            }
                        },

                        showToast(type, message) {
                            const toast = document.createElement('div');
                            toast.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-green-600' : 'bg-red-600'
                                } text-white text-sm font-medium`;
                            toast.textContent = message;
                            document.body.appendChild(toast);

                            setTimeout(() => {
                                toast.remove();
                            }, 3000);
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