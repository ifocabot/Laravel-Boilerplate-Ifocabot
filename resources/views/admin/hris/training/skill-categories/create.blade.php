@extends('layouts.admin')

@section('title', 'Tambah Kategori Skill')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Kategori Skill</h1>
                <p class="text-sm text-gray-500 mt-1">Buat kategori baru untuk mengelompokkan skill</p>
            </div>
            <a href="{{ route('hris.training.skill-categories.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <form action="{{ route('hris.training.skill-categories.store') }}" method="POST"
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
                <a href="{{ route('hris.training.skill-categories.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
@endsection