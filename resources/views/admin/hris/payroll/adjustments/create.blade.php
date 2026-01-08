@extends('layouts.admin')

@section('title', 'Buat Adjustment')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <a href="{{ route('hris.payroll.adjustments.index') }}" class="hover:text-indigo-600">Adjustments</a>
                    <span>/</span>
                    <span class="text-gray-900">Buat Adjustment</span>
                </nav>
                <h1 class="text-2xl font-bold text-gray-900">Buat Adjustment Baru</h1>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('hris.payroll.adjustments.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-6">Informasi Adjustment</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Employee --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Karyawan <span class="text-red-500">*</span>
                        </label>
                        <select name="employee_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('employee_id') border-red-500 @enderror">
                            <option value="">Pilih Karyawan</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->nik }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Period --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Periode Tujuan <span class="text-red-500">*</span>
                        </label>
                        <select name="payroll_period_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('payroll_period_id') border-red-500 @enderror">
                            <option value="">Pilih Periode</option>
                            @foreach($periods as $period)
                                <option value="{{ $period->id }}" {{ old('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                    {{ $period->period_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('payroll_period_id')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Adjustment <span class="text-red-500">*</span>
                        </label>
                        <select name="type" required id="adjustmentType"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('type') border-red-500 @enderror">
                            <option value="">Pilih Tipe</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Source Date --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Sumber <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <input type="date" name="source_date" value="{{ old('source_date') }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Tanggal asal jika ini koreksi untuk tanggal tertentu</p>
                    </div>
                </div>

                <hr class="my-6">

                <h3 class="text-md font-semibold text-gray-900 mb-4">Amount</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Minutes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Menit</label>
                        <input type="number" name="amount_minutes" value="{{ old('amount_minutes') }}"
                            placeholder="e.g. 120 untuk 2 jam"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Untuk overtime/late correction</p>
                    </div>

                    {{-- Days --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hari</label>
                        <input type="number" name="amount_days" value="{{ old('amount_days') }}" step="0.5"
                            placeholder="e.g. 0.5 untuk setengah hari"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Untuk leave correction</p>
                    </div>

                    {{-- Money --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rupiah</label>
                        <input type="number" name="amount_money" value="{{ old('amount_money') }}" placeholder="e.g. 500000"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Untuk manual adjustment</p>
                    </div>
                </div>

                <hr class="my-6">

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Alasan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="3" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 @error('reason') border-red-500 @enderror"
                        placeholder="Jelaskan alasan adjustment...">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan Tambahan <span class="text-gray-400">(Opsional)</span>
                    </label>
                    <textarea name="notes" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500"
                        placeholder="Catatan internal...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('hris.payroll.adjustments.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors">
                    Simpan Adjustment
                </button>
            </div>
        </form>
    </div>
@endsection