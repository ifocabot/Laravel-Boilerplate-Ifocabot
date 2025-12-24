@extends('layouts.admin')

@section('title', 'Tambah Tipe Cuti')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('hris.leave.types.index') }}"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Tipe Cuti</h1>
                <p class="text-sm text-gray-500 mt-1">Buat tipe cuti baru</p>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('hris.leave.types.store') }}" method="POST"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Tipe Cuti <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Contoh: Cuti Tahunan">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
                        Kode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" id="code" required maxlength="10" value="{{ old('code') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent uppercase"
                        placeholder="ANNUAL">
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="default_quota" class="block text-sm font-semibold text-gray-700 mb-2">
                        Kuota Default (hari/tahun) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="default_quota" id="default_quota" required min="0" max="365"
                        value="{{ old('default_quota', 12) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    @error('default_quota')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_consecutive_days" class="block text-sm font-semibold text-gray-700 mb-2">
                        Maks Hari Berturut-turut
                    </label>
                    <input type="number" name="max_consecutive_days" id="max_consecutive_days" min="1" max="365"
                        value="{{ old('max_consecutive_days') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Kosongkan jika tidak dibatasi">
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Deskripsi tipe cuti (opsional)">{{ old('description') }}</textarea>
                </div>

                <div class="md:col-span-2 flex items-center gap-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_paid_submitted" value="1">
                        <input type="checkbox" name="is_paid" id="is_paid" value="1" checked
                            class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Cuti Dibayar</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="requires_attachment" id="requires_attachment" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Wajib Lampiran</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-gray-100">
                <a href="{{ route('hris.leave.types.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection