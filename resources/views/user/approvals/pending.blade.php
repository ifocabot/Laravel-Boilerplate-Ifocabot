@extends('layouts.admin')

@section('title', 'Pending Approvals')

@section('content')
    <div class="space-y-6" x-data="approvalPage()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pending Approvals</h1>
                <p class="text-sm text-gray-500 mt-1">Daftar pengajuan yang menunggu persetujuan Anda</p>
            </div>
            <a href="{{ route('approvals.history') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                History
            </a>
        </div>

        {{-- Pending List --}}
        @if($pendingApprovals->count() > 0)
            <div class="space-y-4">
                @foreach($pendingApprovals as $approval)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                    {{ $approval->workflow->type === 'leave' ? 'bg-blue-100 text-blue-700' : '' }}
                                                    {{ $approval->workflow->type === 'overtime' ? 'bg-orange-100 text-orange-700' : '' }}
                                                    {{ $approval->workflow->type === 'reimbursement' ? 'bg-purple-100 text-purple-700' : '' }}">
                                        {{ ucfirst($approval->workflow->type) }}
                                    </span>
                                    <span class="text-sm text-gray-500">Step {{ $approval->current_step }}</span>
                                </div>

                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    {{ $approval->workflow->name }}
                                </h3>

                                <p class="text-sm text-gray-600">
                                    Diajukan oleh: <span class="font-medium">{{ $approval->requester->full_name }}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $approval->submitted_at->format('d M Y, H:i') }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button @click="openApproveModal({{ $approval->id }})"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Setujui
                                </button>
                                <button @click="openRejectModal({{ $approval->id }})"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-gray-500 text-sm font-medium mb-1">Tidak ada approval pending</p>
                <p class="text-gray-400 text-sm">Semua pengajuan sudah diproses</p>
            </div>
        @endif

        {{-- Approve Modal --}}
        <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showApproveModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Setujui Pengajuan</h3>
                    <form :action="'/approvals/' + selectedId + '/approve'" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                            <textarea name="notes" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Tambahkan catatan..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="showApproveModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                                Setujui
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showRejectModal = false"></div>
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Tolak Pengajuan</h3>
                    <form :action="'/approvals/' + selectedId + '/reject'" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="notes" rows="3" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Jelaskan alasan penolakan..."></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" @click="showRejectModal = false"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                                Tolak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Toast --}}
        @if(session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-white rounded-xl shadow-lg border">
                <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                            {{ session('success') ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' }}">
                    @if(session('success'))
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @endif
                </div>
                <div class="ml-3 text-sm font-medium text-gray-700">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function approvalPage() {
                return {
                    showApproveModal: false,
                    showRejectModal: false,
                    selectedId: null,

                    openApproveModal(id) {
                        this.selectedId = id;
                        this.showApproveModal = true;
                    },

                    openRejectModal(id) {
                        this.selectedId = id;
                        this.showRejectModal = true;
                    }
                }
            }
        </script>
    @endpush
@endsection