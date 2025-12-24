@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Profil Saya</h1>
                <p class="text-sm text-gray-500 mt-1">Informasi data diri Anda</p>
            </div>
            <a href="{{ route('ess.profile.edit') }}"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Edit
                Profil</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Profile Card --}}
            <div class="bg-white rounded-xl shadow-sm border p-6 text-center">
                <div
                    class="w-24 h-24 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 mx-auto flex items-center justify-center text-white text-3xl font-bold">
                    @if($employee->photo)
                        <img src="{{ Storage::url($employee->photo) }}" class="w-24 h-24 rounded-full object-cover">
                    @else
                        {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                    @endif
                </div>
                <h2 class="text-xl font-bold text-gray-900 mt-4">{{ $employee->full_name }}</h2>
                <p class="text-gray-500">{{ $employee->nik }}</p>
                <div class="mt-4 space-y-1">
                    <p class="text-sm"><span class="text-gray-500">Posisi:</span> <span
                            class="font-medium">{{ $employee->current_position?->name ?? '-' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Departemen:</span> <span
                            class="font-medium">{{ $employee->current_department?->name ?? '-' }}</span></p>
                    <p class="text-sm"><span class="text-gray-500">Level:</span> <span
                            class="font-medium">{{ $employee->current_level?->name ?? '-' }}</span></p>
                </div>
            </div>

            {{-- Personal Info --}}
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Pribadi</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Nama Lengkap</p>
                        <p class="font-medium text-gray-900">{{ $employee->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Jenis Kelamin</p>
                        <p class="font-medium text-gray-900">{{ $employee->gender === 'male' ? 'Laki-laki' : 'Perempuan' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Tempat, Tanggal Lahir</p>
                        <p class="font-medium text-gray-900">{{ $employee->birth_place }},
                            {{ $employee->birth_date?->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Status Pernikahan</p>
                        <p class="font-medium text-gray-900">{{ $employee->marital_status_label ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">No. Telepon</p>
                        <p class="font-medium text-gray-900">{{ $employee->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Email Pribadi</p>
                        <p class="font-medium text-gray-900">{{ $employee->personal_email ?? '-' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 uppercase">Alamat</p>
                        <p class="font-medium text-gray-900">{{ $employee->current_address ?? '-' }}</p>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 mt-6 mb-4">Kontak Darurat</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Nama</p>
                        <p class="font-medium text-gray-900">{{ $employee->emergency_contact_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">No. Telepon</p>
                        <p class="font-medium text-gray-900">{{ $employee->emergency_contact_phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Hubungan</p>
                        <p class="font-medium text-gray-900">{{ $employee->emergency_contact_relation ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Employment Info --}}
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kepegawaian</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">NIK</p>
                    <p class="font-medium text-gray-900">{{ $employee->nik }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Tanggal Bergabung</p>
                    <p class="font-medium text-gray-900">{{ $employee->join_date?->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Status Kontrak</p>
                    <p class="font-medium text-gray-900">{{ $employee->contract_type_label ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Cabang</p>
                    <p class="font-medium text-gray-900">{{ $employee->current_branch?->name ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection