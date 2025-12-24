@extends('layouts.admin')

@section('title', 'Buat Program Training')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Buat Program Training</h1>
                <p class="text-sm text-gray-500 mt-1">Tambah program pelatihan baru</p>
            </div>
            <a href="{{ route('hris.training.programs.index') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        {{-- Form --}}
        <form action="{{ route('hris.training.programs.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h2 class="text-lg font-bold text-gray-900">Informasi Dasar</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Program *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-red-500 @enderror"
                            placeholder="Contoh: Leadership Development Program">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode Program *</label>
                        <input type="text" name="code" id="code" value="{{ old('code') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('code') border-red-500 @enderror"
                            placeholder="Contoh: LDP-2024">
                        @error('code')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Deskripsi singkat tentang program training">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipe *</label>
                        <select name="type" id="type" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="internal" {{ old('type') == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="external" {{ old('type') == 'external' ? 'selected' : '' }}>Eksternal</option>
                            <option value="online" {{ old('type') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="hybrid" {{ old('type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                    </div>

                    <div>
                        <label for="trainer_id" class="block text-sm font-medium text-gray-700 mb-2">Trainer</label>
                        <select name="trainer_id" id="trainer_id"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Pilih Trainer</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                    {{ $trainer->display_name }} ({{ $trainer->type_label }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="provider" class="block text-sm font-medium text-gray-700 mb-2">Provider
                            Eksternal</label>
                        <input type="text" name="provider" id="provider" value="{{ old('provider') }}"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Nama provider eksternal">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h2 class="text-lg font-bold text-gray-900">Jadwal & Lokasi</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="duration_hours" class="block text-sm font-medium text-gray-700 mb-2">Durasi
                            (Jam)</label>
                        <input type="number" name="duration_hours" id="duration_hours" value="{{ old('duration_hours') }}"
                            min="1"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Contoh: 24">
                    </div>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Contoh: Ruang Training Lt. 3 / Online via Zoom">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h2 class="text-lg font-bold text-gray-900">Kapasitas & Biaya</h2>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-2">Max
                            Peserta</label>
                        <input type="number" name="max_participants" id="max_participants"
                            value="{{ old('max_participants') }}" min="1"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Kosongkan jika tidak dibatasi">
                    </div>

                    <div>
                        <label for="cost_per_person" class="block text-sm font-medium text-gray-700 mb-2">Biaya per
                            Orang</label>
                        <input type="number" name="cost_per_person" id="cost_per_person"
                            value="{{ old('cost_per_person') }}" min="0"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Rp">
                    </div>

                    <div>
                        <label for="total_budget" class="block text-sm font-medium text-gray-700 mb-2">Total Budget</label>
                        <input type="number" name="total_budget" id="total_budget" value="{{ old('total_budget') }}" min="0"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Rp">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">
                <h2 class="text-lg font-bold text-gray-900">Detail Pembelajaran</h2>

                <div>
                    <label for="objectives" class="block text-sm font-medium text-gray-700 mb-2">Tujuan Pembelajaran</label>
                    <textarea name="objectives" id="objectives" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Apa yang akan dipelajari peserta dari program ini?">{{ old('objectives') }}</textarea>
                </div>

                <div>
                    <label for="prerequisites" class="block text-sm font-medium text-gray-700 mb-2">Prasyarat</label>
                    <textarea name="prerequisites" id="prerequisites" rows="2"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Persyaratan yang harus dipenuhi peserta">{{ old('prerequisites') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('hris.training.programs.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-colors shadow-sm">
                    Simpan Program
                </button>
            </div>
        </form>
    </div>
@endsection