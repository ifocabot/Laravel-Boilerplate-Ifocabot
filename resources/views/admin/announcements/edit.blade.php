@extends('layouts.admin')

@section('title', 'Edit Pengumuman')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Pengumuman</h1>
                <p class="text-sm text-gray-500 mt-1">Edit pengumuman yang sudah ada</p>
            </div>
            <a href="{{ route('access-control.announcements.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <form action="{{ route('access-control.announcements.update', $announcement) }}" method="POST"
            class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
                <input type="text" name="title" id="title" value="{{ old('title', $announcement->title) }}" required
                    class="w-full px-4 py-2.5 border rounded-xl">
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipe *</label>
                <select name="type" id="type" required class="w-full px-4 py-2.5 border rounded-xl">
                    <option value="info" {{ old('type', $announcement->type) == 'info' ? 'selected' : '' }}>ℹ️ Informasi</option>
                    <option value="success" {{ old('type', $announcement->type) == 'success' ? 'selected' : '' }}>✅ Sukses</option>
                    <option value="warning" {{ old('type', $announcement->type) == 'warning' ? 'selected' : '' }}>⚠️ Peringatan</option>
                    <option value="danger" {{ old('type', $announcement->type) == 'danger' ? 'selected' : '' }}>🚨 Penting</option>
                </select>
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Isi Pengumuman *</label>
                <textarea name="content" id="content" rows="6" required
                    class="w-full px-4 py-2.5 border rounded-xl"
                    placeholder="Tulis isi pengumuman...">{{ old('content', $announcement->content) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Anda dapat menggunakan HTML untuk formatting</p>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publish</label>
                    <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at', $announcement->published_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2.5 border rounded-xl">
                </div>
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kadaluarsa</label>
                    <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at', $announcement->expires_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2.5 border rounded-xl">
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $announcement->is_pinned) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">📌 Pin pengumuman</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 rounded">
                    <span class="ml-2 text-sm text-gray-700">Aktif</span>
                </label>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="{{ route('access-control.announcements.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
@endsection
