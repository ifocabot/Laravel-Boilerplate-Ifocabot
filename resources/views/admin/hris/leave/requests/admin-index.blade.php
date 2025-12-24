@extends('layouts.admin')

@section('title', 'Approval Cuti')

@section('content')
    <div class="space-y-6" x-data="{ 
            showApproveModal: false, 
            showRejectModal: false, 
            selectedId: null,
            selectedEmployee: '',
            selectedDates: ''
        }">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Approval Cuti</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola persetujuan pengajuan cuti karyawan</p>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Menunggu Persetujuan</p>
                        <h3 class="text-2xl font-bold text-yellow-600 mt-2">
                            {{ $leaveRequests->where('status', 'pending')->count() }}</h3>
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
                        <p class="text-sm font-medium text-gray-500">Disetujui Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-green-600 mt-2">{{ $approvedThisMonth }}</h3>
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
                        <p class="text-sm font-medium text-gray-500">Ditolak Bulan Ini</p>
                        <h3 class="text-2xl font-bold text-red-600 mt-2">{{ $rejectedThisMonth }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Leave Requests --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Pengajuan Menunggu Persetujuan</h2>
                <form method="GET" class="flex items-center gap-3">
                    <select name="status" onchange="this.form.submit()"
                        class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </form>
            </div>

            @if($leaveRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe Cuti</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Alasan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($leaveRequests as $request)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $request->employee->full_name ?? 'N/A' }}
                                        </p>
                                        <p class="text-xs text-gray-500">{{ $request->employee->employee_id ?? '' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                            {{ $request->leaveType->code ?? '' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $request->formatted_date_range }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $request->total_days }} hari</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $request->reason ?? '-' }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $request->status_badge_class }}">
                                            {{ $request->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($request->status === 'pending')
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button"
                                                    @click="showApproveModal = true; selectedId = {{ $request->id }}; selectedEmployee = '{{ $request->employee->full_name ?? 'Karyawan' }}'; selectedDates = '{{ $request->formatted_date_range }}'"
                                                    class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Setujui
                                                </button>
                                                <button type="button"
                                                    @click="showRejectModal = true; selectedId = {{ $request->id }}; selectedEmployee = '{{ $request->employee->full_name ?? 'Karyawan' }}'; selectedDates = '{{ $request->formatted_date_range }}'"
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Tolak
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">
                                                {{ $request->approver?->name ?? '-' }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $leaveRequests->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Tidak ada pengajuan cuti yang perlu diproses</p>
                </div>
            @endif
        </div>

        {{-- Approve Modal --}}
        <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-150">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" @click.away="showApproveModal = false">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Setujui Cuti</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Setujui pengajuan cuti <strong x-text="selectedEmployee"></strong>
                    untuk tanggal <strong x-text="selectedDates"></strong>?
                </p>
                <p class="text-sm text-yellow-600 bg-yellow-50 p-3 rounded-lg mb-4">
                    ⚠️ Saldo cuti karyawan akan otomatis dikurangi dan status kehadiran akan diubah menjadi CUTI.
                </p>
                <form :action="'/hris/leave/requests/' + selectedId + '/approve'" method="POST">
                    @csrf
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showApproveModal = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Ya, Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-150">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" @click.away="showRejectModal = false">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Tolak Cuti</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Tolak pengajuan cuti <strong x-text="selectedEmployee"></strong>?
                </p>
                <form :action="'/hris/leave/requests/' + selectedId + '/reject'" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alasan Penolakan *</label>
                        <textarea name="rejection_reason" rows="3" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent"
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showRejectModal = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Toast --}}
        @if(session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-white rounded-xl shadow-lg border">
                <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                            {{ session('success') ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ session('success') ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}" />
                    </svg>
                </div>
                <div class="ml-3 text-sm font-medium text-gray-700">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        @endif
    </div>
@endsection