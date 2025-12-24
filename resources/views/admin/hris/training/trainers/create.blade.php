@extends('layouts.admin')

@section('title', 'Tambah Trainer')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Tambah Trainer</h1>
                <p class="text-sm text-gray-500 mt-1">Tambah trainer internal atau eksternal</p>
            </div>
            <a href="{{ route('hris.training.trainers.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <form action="{{ route('hris.training.trainers.store') }}" method="POST"
            class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Trainer *</label>
                <div class="flex gap-4">
                    <label class="flex items-center">
                        <input type="radio" name="type" value="internal" {{ old('type', 'internal') == 'internal' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600" x-model="type">
                        <span class="ml-2 text-sm text-gray-700">Internal (Karyawan)</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="type" value="external" {{ old('type') == 'external' ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600" x-model="type">
                        <span class="ml-2 text-sm text-gray-700">Eksternal</span>
                    </label>
                </div>
            </div>

            <div x-data="{ type: '{{ old('type', 'internal') }}' }">
                <div x-show="type === 'internal'">
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih Karyawan *</label>
                    <select name="employee_id" id="employee_id" class="w-full px-4 py-2.5 border rounded-xl">
                        <option value="">Pilih Karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->nik }})</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="type === 'external'" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Trainer *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="w-full px-4 py-2.5 border rounded-xl">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="w-full px-4 py-2.5 border rounded-xl">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                class="w-full px-4 py-2.5 border rounded-xl">
                        </div>
                    </div>
                    <div>
                        <label for="organization"
                            class="block text-sm font-medium text-gray-700 mb-2">Organisasi/Perusahaan</label>
                        <input type="text" name="organization" id="organization" value="{{ old('organization') }}"
                            class="w-full px-4 py-2.5 border rounded-xl">
                    </div>
                </div>
            </div>

            <div>
                <label for="expertise" class="block text-sm font-medium text-gray-700 mb-2">Keahlian/Bidang</label>
                <textarea name="expertise" id="expertise" rows="2" class="w-full px-4 py-2.5 border rounded-xl"
                    placeholder="Contoh: Leadership, Communication, Project Management">{{ old('expertise') }}</textarea>
            </div>
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                <textarea name="bio" id="bio" rows="3"
                    class="w-full px-4 py-2.5 border rounded-xl">{{ old('bio') }}</textarea>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded">
                <label for="is_active" class="ml-2 text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex justify-end gap-4">
                <a href="{{ route('hris.training.trainers.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
@endsection