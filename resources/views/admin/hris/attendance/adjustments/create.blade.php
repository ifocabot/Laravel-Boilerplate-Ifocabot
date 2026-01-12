@extends('layouts.admin')

@section('title', 'Koreksi Manual Kehadiran')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Koreksi Manual Kehadiran</h1>
                <p class="text-sm text-gray-500 mt-1">Buat adjustment manual untuk kehadiran karyawan</p>
            </div>
            <a href="{{ route('hris.attendance.adjustments.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('hris.attendance.adjustments.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Employee --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Karyawan <span class="text-red-500">*</span>
                    </label>
                    <select name="employee_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('employee_id') border-red-500 @enderror">
                        <option value="">Pilih Karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->nik }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Override --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Override Status Kehadiran
                    </label>
                    <select name="status_override"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">-- Tidak override status --</option>
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" {{ old('status_override') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah status kehadiran</p>
                </div>

                {{-- Adjustment Minutes --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Adjustment Menit (Overtime)
                    </label>
                    <input type="number" name="adjustment_minutes" value="{{ old('adjustment_minutes', 0) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('adjustment_minutes') border-red-500 @enderror"
                        placeholder="Contoh: 60 untuk +1 jam, -30 untuk -30 menit">
                    <p class="text-xs text-gray-500 mt-1">Gunakan nilai positif untuk menambah overtime, negatif untuk
                        mengurangi</p>
                    @error('adjustment_minutes')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Alasan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="3" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('reason') border-red-500 @enderror"
                        placeholder="Jelaskan alasan koreksi...">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Warning --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-yellow-800">Perhatian</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                Adjustment ini akan langsung diterapkan ke attendance summary karyawan.
                                Pastikan informasi yang dimasukkan sudah benar.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('hris.attendance.adjustments.index') }}"
                        class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                        Simpan Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection