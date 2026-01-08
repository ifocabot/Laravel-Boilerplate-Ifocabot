@extends('layouts.admin')

@section('title', 'Detail Adjustment')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <a href="{{ route('hris.payroll.adjustments.index') }}" class="hover:text-indigo-600">Adjustments</a>
                    <span>/</span>
                    <span class="text-gray-900">Detail</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">Detail Adjustment #{{ $adjustment->id }}</h1>
            </div>

            @if($adjustment->status === 'pending')
                <div class="flex items-center gap-3">
                    <form action="{{ route('hris.payroll.adjustments.approve', $adjustment->id) }}" method="POST"
                        class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Approve adjustment ini?')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Approve
                        </button>
                    </form>

                    <button type="button" onclick="showRejectModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reject
                    </button>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Informasi Adjustment</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $adjustment->status_badge_class }} mt-1">
                                {{ $adjustment->status_label }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tipe</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $adjustment->type_label }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Periode Tujuan</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                {{ $adjustment->payrollPeriod->period_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal Sumber</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                {{ $adjustment->source_date?->format('d M Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <hr class="my-6">

                    <h3 class="text-md font-semibold text-gray-900 mb-4">Amount</h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Menit</p>
                            <p class="text-lg font-bold text-gray-900">{{ $adjustment->formatted_amount_minutes }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Hari</p>
                            <p class="text-lg font-bold text-gray-900">{{ $adjustment->amount_days ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-500">Rupiah</p>
                            <p class="text-lg font-bold text-gray-900">{{ $adjustment->formatted_amount_money }}</p>
                        </div>
                    </div>

                    <hr class="my-6">

                    <div>
                        <p class="text-sm text-gray-500 mb-2">Alasan</p>
                        <p class="text-sm text-gray-900 bg-gray-50 rounded-lg p-4">{{ $adjustment->reason }}</p>
                    </div>

                    @if($adjustment->notes)
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 mb-2">Catatan</p>
                            <p class="text-sm text-gray-900 bg-gray-50 rounded-lg p-4">{{ $adjustment->notes }}</p>
                        </div>
                    @endif

                    @if($adjustment->rejection_reason)
                        <div class="mt-4">
                            <p class="text-sm text-red-500 mb-2">Alasan Penolakan</p>
                            <p class="text-sm text-red-900 bg-red-50 rounded-lg p-4">{{ $adjustment->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Employee Info --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-md font-bold text-gray-900 mb-4">Karyawan</h3>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-sm font-bold text-indigo-600">
                                {{ strtoupper(substr($adjustment->employee->full_name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $adjustment->employee->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $adjustment->employee->nik }}</p>
                        </div>
                    </div>
                </div>

                {{-- Audit Info --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-md font-bold text-gray-900 mb-4">Audit</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500">Dibuat oleh</p>
                            <p class="text-sm font-medium text-gray-900">{{ $adjustment->createdByUser->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $adjustment->created_at->format('d M Y H:i') }}</p>
                        </div>
                        @if($adjustment->approved_at)
                            <div>
                                <p class="text-xs text-gray-500">
                                    {{ $adjustment->status === 'approved' ? 'Disetujui' : 'Ditolak' }} oleh</p>
                                <p class="text-sm font-medium text-gray-900">{{ $adjustment->approvedByUser->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $adjustment->approved_at->format('d M Y H:i') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="hideRejectModal()"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Adjustment</h3>

                <form action="{{ route('hris.payroll.adjustments.reject', $adjustment->id) }}" method="POST">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan *</label>
                        <textarea name="rejection_reason" rows="3" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
                            placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                            class="flex-1 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-medium rounded-xl">
                            Reject
                        </button>
                        <button type="button" onclick="hideRejectModal()"
                            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showRejectModal() {
                document.getElementById('rejectModal').classList.remove('hidden');
            }
            function hideRejectModal() {
                document.getElementById('rejectModal').classList.add('hidden');
            }
        </script>
    @endpush
@endsection