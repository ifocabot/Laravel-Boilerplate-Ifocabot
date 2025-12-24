@extends('layouts.admin')

@section('title', 'Edit Profil')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Profil</h1>
                <p class="text-sm text-gray-500 mt-1">Perbarui informasi pribadi Anda</p>
            </div>
            <a href="{{ route('ess.profile.index') }}"
                class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kembali</a>
        </div>

        <form action="{{ route('ess.profile.update') }}" method="POST"
            class="bg-white rounded-xl shadow-sm border p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kontak</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $employee->phone) }}"
                            class="w-full px-4 py-2.5 border rounded-xl">
                    </div>
                    <div>
                        <label for="personal_email" class="block text-sm font-medium text-gray-700 mb-2">Email
                            Pribadi</label>
                        <input type="email" name="personal_email" id="personal_email"
                            value="{{ old('personal_email', $employee->personal_email) }}"
                            class="w-full px-4 py-2.5 border rounded-xl">
                    </div>
                </div>
            </div>

            <div>
                <label for="current_address" class="block text-sm font-medium text-gray-700 mb-2">Alamat Saat Ini</label>
                <textarea name="current_address" id="current_address" rows="3"
                    class="w-full px-4 py-2.5 border rounded-xl">{{ old('current_address', $employee->current_address) }}</textarea>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontak Darurat</h3>
                <div class="grid grid-cols-3 gap-6">
                    <div>
                        <label for="emergency_contact_name"
                            class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                        <input type="text" name="emergency_contact_name" id="emergency_contact_name"
                            value="{{ old('emergency_contact_name', $employee->emergency_contact_name) }}"
                            class="w-full px-4 py-2.5 border rounded-xl">
                    </div>
                    <div>
                        <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700 mb-2">No.
                            Telepon</label>
                        <input type="text" name="emergency_contact_phone" id="emergency_contact_phone"
                            value="{{ old('emergency_contact_phone', $employee->emergency_contact_phone) }}"
                            class="w-full px-4 py-2.5 border rounded-xl">
                    </div>
                    <div>
                        <label for="emergency_contact_relation"
                            class="block text-sm font-medium text-gray-700 mb-2">Hubungan</label>
                        <input type="text" name="emergency_contact_relation" id="emergency_contact_relation"
                            value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation) }}"
                            class="w-full px-4 py-2.5 border rounded-xl" placeholder="cth: Suami, Istri, Orang Tua">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t">
                <a href="{{ route('ess.profile.index') }}"
                    class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
@endsection