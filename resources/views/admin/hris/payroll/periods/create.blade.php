@extends('layouts.admin')

@section('title', 'Buat Periode Payroll Baru')

@section('content')
    <div class="space-y-6">
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
                    <h1 class="text-2xl font-bold text-gray-900">Buat Periode Payroll Baru</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Lengkapi informasi periode payroll bulanan</p>
            </div>
        </div>

        <form action="{{ route('hris.payroll.periods.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 space-y-6">
                    {{-- Display Errors --}}
                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-red-900 mb-1">Terjadi Kesalahan</h4>
                                    <ul class="text-xs text-red-700 space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>• {{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Info Box --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-900 mb-1">Informasi</p>
                                <p class="text-xs text-blue-700">
                                    Periode payroll dibuat per bulan. Setelah periode dibuat, Anda dapat generate slip gaji
                                    untuk semua karyawan aktif.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Year --}}
                        <div>
                            <label for="year" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tahun <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="year" id="year" required value="{{ old('year', $suggestedYear) }}"
                                min="2020" max="2100"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('year') border-red-500 @enderror">
                            @error('year')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Month --}}
                        <div>
                            <label for="month" class="block text-sm font-semibold text-gray-700 mb-2">
                                Bulan <span class="text-red-500">*</span>
                            </label>
                            <select name="month" id="month" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('month') border-red-500 @enderror">
                                <option value="">Pilih Bulan</option>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('month', $suggestedMonth) == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(null, $i)->locale('id')->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                            @error('month')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Start Date --}}
                        <div>
                            <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Mulai Periode <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="start_date" required
                                value="{{ old('start_date', $suggestedStartDate) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('start_date') border-red-500 @enderror">
                            @error('start_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- End Date --}}
                        <div>
                            <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Akhir Periode <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="end_date" id="end_date" required
                                value="{{ old('end_date', $suggestedEndDate) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('end_date') border-red-500 @enderror">
                            @error('end_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Payment Date --}}
                        <div class="md:col-span-2">
                            <label for="payment_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="payment_date" id="payment_date" required
                                value="{{ old('payment_date', $suggestedPaymentDate) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('payment_date') border-red-500 @enderror">
                            <p class="mt-1.5 text-xs text-gray-500">Tanggal actual transfer gaji ke karyawan</p>
                            @error('payment_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Policy Configuration Section --}}
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Konfigurasi Kebijakan Periode
                        </h3>
                        <p class="text-xs text-gray-500 mb-4">Pengaturan rate dan multiplier untuk perhitungan payroll
                            periode ini</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Late Penalty Rate --}}
                            <div>
                                <label for="late_penalty_per_minute" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Penalty Terlambat (per menit)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" name="late_penalty_per_minute" id="late_penalty_per_minute"
                                        value="{{ old('late_penalty_per_minute', 1000) }}" min="0" step="100"
                                        class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('late_penalty_per_minute') border-red-500 @enderror">
                                </div>
                                <p class="mt-1.5 text-xs text-gray-500">Potongan per menit keterlambatan</p>
                                @error('late_penalty_per_minute')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Standard Monthly Hours --}}
                            <div>
                                <label for="standard_monthly_hours" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Jam Kerja Standar/Bulan
                                </label>
                                <div class="relative">
                                    <input type="number" name="standard_monthly_hours" id="standard_monthly_hours"
                                        value="{{ old('standard_monthly_hours', 173) }}" min="100" max="250"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('standard_monthly_hours') border-red-500 @enderror">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">jam</span>
                                </div>
                                <p class="mt-1.5 text-xs text-gray-500">Untuk menghitung hourly rate (default: 173)</p>
                                @error('standard_monthly_hours')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Overtime Multiplier --}}
                            <div>
                                <label for="overtime_multiplier" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Multiplier Lembur
                                </label>
                                <div class="relative">
                                    <input type="number" name="overtime_multiplier" id="overtime_multiplier"
                                        value="{{ old('overtime_multiplier', 1.5) }}" min="1" max="5" step="0.1"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('overtime_multiplier') border-red-500 @enderror">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">x</span>
                                </div>
                                <p class="mt-1.5 text-xs text-gray-500">Pengali upah lembur (1.5x = 150%)</p>
                                @error('overtime_multiplier')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Overtime Hourly Rate --}}
                            <div>
                                <label for="overtime_hourly_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Rate Lembur/Jam (Fixed)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" name="overtime_hourly_rate" id="overtime_hourly_rate"
                                        value="{{ old('overtime_hourly_rate', 10000) }}" min="0" step="1000"
                                        class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('overtime_hourly_rate') border-red-500 @enderror">
                                </div>
                                <p class="mt-1.5 text-xs text-gray-500">Override rate per jam (kosongkan = hitung dari gaji
                                    pokok)</p>
                                @error('overtime_hourly_rate')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Notes Section --}}
                    <div class="grid grid-cols-1 gap-6">
                        {{-- Notes --}}
                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                Catatan
                            </label>
                            <textarea name="notes" id="notes" rows="3"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror"
                                placeholder="Catatan tambahan tentang periode payroll ini (opsional)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                    <a href="{{ route('hris.payroll.periods.index') }}"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Buat Periode
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection