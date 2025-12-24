@extends('layouts.admin')

@section('title', 'Ajukan Cuti')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('hris.leave.requests.index') }}"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Ajukan Cuti</h1>
                <p class="text-sm text-gray-500 mt-1">Buat pengajuan cuti baru</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Form --}}
            <div class="lg:col-span-2">
                <form action="{{ route('hris.leave.requests.store') }}" method="POST" enctype="multipart/form-data"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="leave_type_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipe Cuti <span class="text-red-500">*</span>
                            </label>
                            <select name="leave_type_id" id="leave_type_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Pilih Tipe Cuti</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}" 
                                        data-remaining="{{ $balances[$type->id]->remaining ?? 0 }}"
                                        data-max-days="{{ $type->max_consecutive_days }}"
                                        data-requires-attachment="{{ $type->requires_attachment ? '1' : '0' }}"
                                        {{ old('leave_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} (Sisa: {{ $balances[$type->id]->remaining ?? 0 }} hari)
                                    </option>
                                @endforeach
                            </select>
                            @error('leave_type_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date" required
                                    value="{{ old('start_date') }}"
                                    min="{{ now()->format('Y-m-d') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                @error('start_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="end_date" id="end_date" required
                                    value="{{ old('end_date') }}"
                                    min="{{ now()->format('Y-m-d') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                @error('end_date')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">
                                Alasan
                            </label>
                            <textarea name="reason" id="reason" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Jelaskan alasan cuti (opsional)">{{ old('reason') }}</textarea>
                        </div>

                        <div>
                            <label for="attachment" class="block text-sm font-semibold text-gray-700 mb-2">
                                Lampiran <span id="attachment-required" class="text-red-500 hidden">*</span>
                            </label>
                            <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            <p class="text-xs text-gray-500 mt-1">File: PDF, JPG, PNG (max 2MB)</p>
                            @error('attachment')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-gray-100">
                        <a href="{{ route('hris.leave.requests.index') }}"
                            class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            Ajukan Cuti
                        </button>
                    </div>
                </form>
            </div>

            {{-- Sidebar: Saldo Cuti --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-4">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Saldo Cuti Tahun {{ now()->year }}</h3>
                    
                    <div class="space-y-4">
                        @forelse($balances as $typeId => $balance)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ $balance->leaveType->name }}</span>
                                <span class="text-sm font-bold text-gray-900">{{ $balance->remaining }}/{{ $balance->total_quota }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada saldo cuti.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const leaveTypeSelect = document.getElementById('leave_type_id');
            const attachmentRequired = document.getElementById('attachment-required');
            const attachmentInput = document.getElementById('attachment');

            leaveTypeSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const requiresAttachment = selected.dataset.requiresAttachment === '1';

                if (requiresAttachment) {
                    attachmentRequired.classList.remove('hidden');
                    attachmentInput.setAttribute('required', 'required');
                } else {
                    attachmentRequired.classList.add('hidden');
                    attachmentInput.removeAttribute('required');
                }
            });

            // Trigger on load
            leaveTypeSelect.dispatchEvent(new Event('change'));
        });
    </script>
    @endpush
@endsection
