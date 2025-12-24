@extends('layouts.admin')

@section('title', 'Buat Pengumuman')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Buat Pengumuman</h1>
                <p class="text-sm text-gray-500 mt-1">Buat pengumuman baru untuk karyawan</p>
            </div>
            <a href="{{ route('access-control.announcements.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <form action="{{ route('access-control.announcements.store') }}" method="POST"
            class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-2.5 border rounded-xl @error('title') border-red-500 @enderror">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipe *</label>
                <select name="type" id="type" required class="w-full px-4 py-2.5 border rounded-xl">
                    <option value="info" {{ old('type', 'info') == 'info' ? 'selected' : '' }}>ℹ️ Informasi</option>
                    <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>✅ Sukses</option>
                    <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>⚠️ Peringatan</option>
                    <option value="danger" {{ old('type') == 'danger' ? 'selected' : '' }}>🚨 Penting</option>
                </select>
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Isi Pengumuman *</label>
                <textarea name="content" id="content" rows="6" required
                    class="w-full px-4 py-2.5 border rounded-xl @error('content') border-red-500 @enderror"
                    placeholder="Tulis isi pengumuman...">{{ old('content') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Anda dapat menggunakan HTML untuk formatting (contoh: &lt;b&gt;bold&lt;/b&gt;, &lt;i&gt;italic&lt;/i&gt;)</p>
                @error('content')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="published_at" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Publish</label>
                    <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}"
                        class="w-full px-4 py-2.5 border rounded-xl">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk publish sekarang</p>
                </div>
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Kadaluarsa</label>
                    <input type="datetime-local" name="expires_at" id="expires_at" value="{{ old('expires_at') }}"
                        class="w-full px-4 py-2.5 border rounded-xl">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ada batas waktu</p>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_pinned" id="is_pinned" value="1" {{ old('is_pinned') ? 'checked' : '' }}
                    class="w-4 h-4 text-indigo-600 rounded">
                <label for="is_pinned" class="ml-2 text-sm text-gray-700">📌 Pin pengumuman ini (tampil di atas)</label>
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