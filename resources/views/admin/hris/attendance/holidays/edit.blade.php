@extends('layouts.admin')

@section('title', 'Edit Hari Libur - ' . $holiday->name)

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.attendance.holidays.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Hari Libur</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $holiday->name }} ({{ $holiday->formatted_date }})</p>
            </div>
        </div>

        <form action="{{ route('hris.attendance.holidays.update', $holiday->id) }}" method="POST">
            @csrf
            @method('PUT')

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

                    {{-- Holiday Information --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Libur</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Hari Libur <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" required
                                    value="{{ old('name', $holiday->name) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date --}}
                            <div>
                                <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="date" id="date" required
                                    value="{{ old('date', $holiday->date->format('Y-m-d')) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('date') border-red-500 @enderror">
                                @error('date')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Is Recurring --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Berulang Tiap Tahun?
                                </label>
                                <div class="flex items-center gap-3 mt-3">
                                    <input type="checkbox" name="is_recurring" id="is_recurring" value="1"
                                        {{ old('is_recurring', $holiday->is_recurring) ? 'checked' : '' }}
                                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <label for="is_recurring" class="text-sm text-gray-700 cursor-pointer">
                                        Ya, libur ini berulang setiap tahun
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('description') border-red-500 @enderror"
                            placeholder="Deskripsi tambahan">{{ old('description', $holiday->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Status</h3>
                        <div class="flex items-center gap-3">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $holiday->is_active) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="is_active" class="text-sm text-gray-700 cursor-pointer">
                                Aktif (akan diterapkan pada schedule)
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                    <a href="{{ route('hris.attendance.holidays.index') }}"
                        class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Libur
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
