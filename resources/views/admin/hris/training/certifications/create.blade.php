@extends('layouts.admin')

@section('title', 'Tambah Sertifikasi')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Sertifikasi</h1>
                <p class="text-sm text-gray-500 mt-1">Tambah data sertifikasi profesional baru</p>
            </div>
            <a href="{{ route('hris.training.certifications.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <form action="{{ route('hris.training.certifications.store') }}" method="POST"
            class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            @csrf
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Kode *</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required
                        class="w-full px-4 py-2.5 border rounded-xl @error('code') border-red-500 @enderror">
                    @error('code')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2.5 border rounded-xl @error('name') border-red-500 @enderror">
                    @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="issuing_organization" class="block text-sm font-medium text-gray-700 mb-2">Lembaga Penerbit
                        *</label>
                    <input type="text" name="issuing_organization" id="issuing_organization"
                        value="{{ old('issuing_organization') }}" required class="w-full px-4 py-2.5 border rounded-xl">
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                    <select name="level" id="level" class="w-full px-4 py-2.5 border rounded-xl">
                        <option value="">Pilih Level</option>
                        <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }}>Pemula</option>
                        <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>Menengah</option>
                        <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>Lanjutan</option>
                        <option value="expert" {{ old('level') == 'expert' ? 'selected' : '' }}>Ahli</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="validity_months" class="block text-sm font-medium text-gray-700 mb-2">Masa Berlaku
                        (Bulan)</label>
                    <input type="number" name="validity_months" id="validity_months" value="{{ old('validity_months') }}"
                        min="0" class="w-full px-4 py-2.5 border rounded-xl" placeholder="Kosongkan jika berlaku selamanya">
                </div>
                <div>
                    <label for="skill_id" class="block text-sm font-medium text-gray-700 mb-2">Skill Terkait</label>
                    <select name="skill_id" id="skill_id" class="w-full px-4 py-2.5 border rounded-xl">
                        <option value="">Pilih Skill</option>
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}" {{ old('skill_id') == $skill->id ? 'selected' : '' }}>
                                {{ $skill->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-4 py-2.5 border rounded-xl">{{ old('description') }}</textarea>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex justify-end gap-4">
                <a href="{{ route('hris.training.certifications.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
@endsection