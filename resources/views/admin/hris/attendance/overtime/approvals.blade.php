@extends('layouts.admin')

@section('title', 'Overtime Approval Dashboard')

@section('content')
    <div class="space-y-6" x-data="overtimeApprovalDashboard()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.attendance.overtime.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Overtime Approval Dashboard</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Review dan approve overtime requests</p>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pending</p>
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
                        <p class="text-sm font-medium text-gray-500">Rejected</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['rejected'] }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Jam Pending</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_pending_hours'], 1) }}</h3>
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
                    <a href="{{ route('hris.attendance.overtime.approvals', ['status' => 'pending']) }}"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $status === 'pending' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Pending ({{ $stats['pending'] }})
                    </a>
                    <a href="{{ route('hris.attendance.overtime.approvals', ['status' => 'approved']) }}"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $status === 'approved' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Approved ({{ $stats['approved'] }})
                    </a>
                    <a href="{{ route('hris.attendance.overtime.approvals', ['status' => 'rejected']) }}"
                        class="px-6 py-4 text-sm font-medium border-b-2 transition-colors {{ $status === 'rejected' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Rejected ({{ $stats['rejected'] }})
                    </a>
                </nav>
            </div>

            @if($requests->count() > 0)
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
                                    Waktu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Durasi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Alasan
                                </th>
                                @if($status === 'approved')
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Approver
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Approved
                                    </th>
                                @elseif($status === 'rejected')
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Alasan Ditolak
                                    </th>
                                @endif
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($requests as $request)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    @if($status === 'pending')
                                        <td class="px-6 py-4">
                                            <input type="checkbox" value="{{ $request->id }}"
                                                @change="toggleSelect({{ $request->id }})"
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
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm font-bold text-indigo-600">
                                                    {{ strtoupper(substr($request->employee->full_name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $request->employee->full_name }}</p>
                                                <p class="text-xs text-gray-500">{{ $request->employee->nik }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $request->time_range }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-bold text-orange-600">{{ $request->formatted_duration }}</p>
                                        <p class="text-xs text-gray-500">{{ $request->duration_hours }} jam</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ Str::limit($request->reason, 50) }}</p>
                                    </td>
                                    @if($status === 'approved')
                                        <td class="px-6 py-4">
                                            @if($request->approver)
                                                <p class="text-sm font-semibold text-gray-900">{{ $request->approver->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $request->approved_at->diffForHumans() }}</p>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-bold text-green-600">{{ $request->formatted_approved_duration }}</p>
                                        </td>
                                    @elseif($status === 'rejected')
                                        <td class="px-6 py-4">
                                            <p class="text-sm text-gray-900">{{ $request->rejection_note ?? '-' }}</p>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($status === 'pending')
                                                <button @click="openApproveModal({{ $request->id }}, {{ $request->duration_minutes }})" type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                                    title="Approve">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </button>
                                                <button @click="openRejectModal({{ $request->id }})" type="button"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="Reject">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            @endif
                                            <a href="{{ route('hris.attendance.overtime.show', $request->id) }}"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                title="Detail">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium mb-1">
                        Tidak ada overtime {{ $status === 'pending' ? 'yang perlu diapprove' : ($status === 'approved' ? 'yang sudah diapprove' : 'yang ditolak') }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Modals --}}
        @include('admin.hris.attendance.overtime._approve_modal')
        @include('admin.hris.attendance.overtime._reject_modal')
    </div>

    @push('scripts')
        <script>
            function overtimeApprovalDashboard() {
                return {
                    selectedIds: [],
                    showApproveModal: false,
                    showRejectModal: false,
                    currentRequestId: null,
                    approveForm: {
                        approved_minutes: 0,
                        system_duration: 0,
                        notes: ''
                    },
                    rejectForm: {
                        reason: ''
                    },

                    toggleSelectAll(event) {
                        if (event.target.checked) {
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

                    openApproveModal(requestId, durationMinutes) {
                        this.currentRequestId = requestId;
                        this.approveForm = {
                            approved_minutes: durationMinutes,
                            system_duration: durationMinutes,
                            notes: ''
                        };
                        this.showApproveModal = true;
                    },

                    closeApproveModal() {
                        this.showApproveModal = false;
                        setTimeout(() => {
                            this.currentRequestId = null;
                            this.approveForm = { approved_minutes: 0, system_duration: 0, notes: '' };
                        }, 300);
                    },

                    async approveRequest() {
                        if (!this.currentRequestId) return;

                        try {
                            const response = await fetch(`/hris/attendance/overtime/${this.currentRequestId}/approve`, {
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
                            alert('Terjadi kesalahan saat approve request');
                        }
                    },

                    openRejectModal(requestId) {
                        this.currentRequestId = requestId;
                        this.rejectForm.reason = '';
                        this.showRejectModal = true;
                    },

                    closeRejectModal() {
                        this.showRejectModal = false;
                        setTimeout(() => {
                            this.currentRequestId = null;
                            this.rejectForm.reason = '';
                        }, 300);
                    },

                    async rejectRequest() {
                        if (!this.currentRequestId || !this.rejectForm.reason) return;

                        try {
                            const response = await fetch(`/hris/attendance/overtime/${this.currentRequestId}/reject`, {
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
                            alert('Terjadi kesalahan saat reject request');
                        }
                    },

                    async bulkApprove() {
                        if (this.selectedIds.length === 0) {
                            alert('Pilih minimal 1 request untuk diapprove');
                            return;
                        }

                        if (!confirm(`Approve ${this.selectedIds.length} overtime requests?`)) {
                            return;
                        }

                        try {
                            const response = await fetch('/hris/attendance/overtime/bulk-approve', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    request_ids: this.selectedIds
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
                        toast.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg ${
                            type === 'success' ? 'bg-green-600' : 'bg-red-600'
                        } text-white text-sm font-medium`;
                        toast.textContent = message;
                        document.body.appendChild(toast);
                        
                        setTimeout(() => toast.remove(), 3000);
                    }
                }
            }
        </script>

        <style>
            [x-cloak] { display: none !important; }
        </style>
    @endpush
@endsection