@extends('layouts.admin')

@section('title', 'Detail Overtime Request')

@section('content')
    <div class="space-y-6" x-data="overtimeDetailActions()">
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
                    <h1 class="text-2xl font-bold text-gray-900">Detail Overtime Request</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $request->formatted_date }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold {{ $request->status_badge_class }}">
                    {{ $request->status_label }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Employee Info --}}
            <div class="space-y-6">
                {{-- Employee Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Karyawan</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-xl font-bold text-indigo-600">
                                {{ strtoupper(substr($request->employee->full_name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-900">{{ $request->employee->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $request->employee->nik }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Departemen:</span>
                            <span class="font-medium text-gray-900">{{ $request->employee->currentCareer?->department?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Posisi:</span>
                            <span class="font-medium text-gray-900">{{ $request->employee->currentCareer?->position?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Timeline Card (if approved/rejected) --}}
                @if($request->approver)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Timeline</h3>
                        <div class="space-y-4">
                            <div class="flex gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs text-gray-500">Request Created</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $request->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>

                            @if($request->is_approved)
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500">Approved by {{ $request->approver->name }}</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $request->approved_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @elseif($request->is_rejected)
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500">Rejected by {{ $request->approver->name }}</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $request->rejected_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($request->is_cancelled)
                                <div class="flex gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs text-gray-500">Cancelled by {{ $request->cancelledBy->name ?? '-' }}</p>
                                        <p class="text-sm font-semibold text-gray-900">{{ $request->cancelled_at?->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column - Request Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Request Info Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">Informasi Request</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Date & Time --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 mb-2">Tanggal Overtime</p>
                                <p class="text-xl font-bold text-gray-900">{{ $request->formatted_date }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ $request->day_name }}</p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 mb-2">Waktu</p>
                                <p class="text-xl font-bold text-gray-900">{{ $request->time_range }}</p>
                            </div>
                        </div>

                        {{-- Duration Summary --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-orange-50 rounded-xl">
                                <p class="text-xs text-gray-500 mb-2">Requested</p>
                                <p class="text-2xl font-bold text-orange-600">{{ $request->formatted_duration }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $request->duration_hours }} jam</p>
                            </div>

                            @if($request->is_approved)
                                <div class="text-center p-4 bg-green-50 rounded-xl">
                                    <p class="text-xs text-gray-500 mb-2">Approved</p>
                                    <p class="text-2xl font-bold text-green-600">{{ $request->formatted_approved_duration }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ round($request->approved_duration_minutes / 60, 2) }} jam</p>
                                </div>
                            @endif

                            @if($request->actual_duration_minutes > 0)
                                <div class="text-center p-4 bg-blue-50 rounded-xl">
                                    <p class="text-xs text-gray-500 mb-2">Actual</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ $request->formatted_actual_duration }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ round($request->actual_duration_minutes / 60, 2) }} jam</p>
                                </div>
                            @endif
                        </div>

                        {{-- Reason --}}
                        <div>
                            <p class="text-xs text-gray-500 mb-2">Alasan Overtime</p>
                            <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-xl">{{ $request->reason }}</p>
                        </div>

                        {{-- Work Description --}}
                        @if($request->work_description)
                            <div>
                                <p class="text-xs text-gray-500 mb-2">Deskripsi Pekerjaan</p>
                                <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-xl">{{ $request->work_description }}</p>
                            </div>
                        @endif

                        {{-- Approval Notes --}}
                        @if($request->approval_notes)
                            <div>
                                <p class="text-xs text-gray-500 mb-2">Catatan Approval</p>
                                <div class="bg-green-50 border border-green-200 p-4 rounded-xl">
                                    <p class="text-sm text-green-900">{{ $request->approval_notes }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Rejection Note --}}
                        @if($request->rejection_note)
                            <div>
                                <p class="text-xs text-gray-500 mb-2">Alasan Penolakan</p>
                                <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
                                    <p class="text-sm text-red-900">{{ $request->rejection_note }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Cancellation Reason --}}
                        @if($request->cancellation_reason)
                            <div>
                                <p class="text-xs text-gray-500 mb-2">Alasan Pembatalan</p>
                                <div class="bg-gray-50 border border-gray-200 p-4 rounded-xl">
                                    <p class="text-sm text-gray-900">{{ $request->cancellation_reason }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if($request->is_pending)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Actions</h3>
                        <div class="flex items-center gap-3">
                            <button @click="approveRequest()" type="button"
                                class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Approve Request
                            </button>
                            <button @click="rejectRequest()" type="button"
                                class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Reject Request
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function overtimeDetailActions() {
                return {
                    approveRequest() {
                        if (!confirm('Approve overtime request ini?')) return;
                        
                        window.location.href = '{{ route('hris.attendance.overtime.approvals') }}?approve={{ $request->id }}';
                    },

                    rejectRequest() {
                        const reason = prompt('Alasan penolakan:');
                        if (!reason) return;

                        fetch('{{ route('hris.attendance.overtime.reject', $request->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ reason })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                window.location.reload();
                            } else {
                                alert(data.message || 'Terjadi kesalahan');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Terjadi kesalahan');
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection