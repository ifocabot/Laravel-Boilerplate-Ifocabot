@extends('layouts.admin')

@section('title', 'Detail Karyawan - ' . $employee->full_name)

@section('content')
    <div class="space-y-6" x-data="employeeDetail()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.employees.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Karyawan</h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($employee->status === 'active')
                    <button @click="openResignModal()" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Resign
                    </button>
                    <button @click="openTerminateModal()" type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Terminate
                    </button>
                @else
                    <form action="{{ route('hris.employees.reactivate', $employee->id) }}" method="POST"
                        onsubmit="return confirm('Apakah Anda yakin ingin mengaktifkan kembali karyawan ini?');">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Aktifkan Kembali
                        </button>
                    </form>
                @endif
                
                <a href="{{ route('hris.employees.edit', $employee->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Data
                </a>
            </div>
        </div>

        {{-- Employee Header Card --}}
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex items-start gap-6">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 rounded-2xl bg-white/20 flex items-center justify-center text-white text-3xl font-bold flex-shrink-0">
                        {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 text-white">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h2 class="text-2xl font-bold mb-1">{{ $employee->full_name }}</h2>
                                <p class="text-indigo-100 text-sm">NIK: {{ $employee->nik }}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $employee->status === 'active' ? 'bg-green-500 text-white' : '' }}
                                {{ $employee->status === 'resigned' ? 'bg-yellow-500 text-white' : '' }}
                                {{ $employee->status === 'terminated' ? 'bg-red-500 text-white' : '' }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <p class="text-indigo-100 text-xs mb-1">Posisi</p>
                                <p class="font-semibold">{{ $employee->current_position?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-indigo-100 text-xs mb-1">Departemen</p>
                                <p class="font-semibold">{{ $employee->current_department?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-indigo-100 text-xs mb-1">Level</p>
                                <p class="font-semibold">{{ $employee->current_level?->grade_code ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Bergabung</p>
                        <p class="text-sm font-bold text-gray-900">{{ $employee->join_date->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Masa Kerja</p>
                        <p class="text-sm font-bold text-gray-900">{{ $employee->formatted_tenure }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tipe Kontrak</p>
                        <p class="text-sm font-bold text-gray-900">{{ $employee->contract_type_label ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Usia</p>
                        <p class="text-sm font-bold text-gray-900">{{ $employee->age ?? '-' }} tahun</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation Tabs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button @click="activeTab = 'personal'" 
                        :class="activeTab === 'personal' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Informasi Personal
                    </button>
                    <button @click="activeTab = 'employment'" 
                        :class="activeTab === 'employment' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Data Kepegawaian
                    </button>
                    <button @click="activeTab = 'sensitive'" 
                        :class="activeTab === 'sensitive' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Data Sensitif
                    </button>
                    <button @click="activeTab = 'emergency'" 
                        :class="activeTab === 'emergency' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        Kontak Darurat
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                {{-- Personal Info Tab --}}
                <div x-show="activeTab === 'personal'" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Lengkap</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->full_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">NIK Karyawan</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->nik }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Email Corporate</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->email_corporate ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">No. Telepon</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->phone_number ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tempat, Tanggal Lahir</label>
                            <p class="text-base font-semibold text-gray-900">
                                {{ $employee->place_of_birth ?? '-' }}
                                @if($employee->date_of_birth)
                                    , {{ $employee->date_of_birth->format('d F Y') }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Usia</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->age ?? '-' }} tahun</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Jenis Kelamin</label>
                            <p class="text-base font-semibold text-gray-900">
                                {{ $employee->gender === 'male' ? 'Laki-laki' : ($employee->gender === 'female' ? 'Perempuan' : '-') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status Pernikahan</label>
                            <p class="text-base font-semibold text-gray-900">{{ ucfirst($employee->marital_status ?? '-') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Agama</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->religion ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Employment Tab --}}
                <div x-show="activeTab === 'employment'" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Departemen</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->current_department?->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Posisi</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->current_position?->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Level/Grade</label>
                            <p class="text-base font-semibold text-gray-900">
                                {{ $employee->current_level?->grade_code ?? '-' }} - {{ $employee->current_level?->name ?? '' }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi/Cabang</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->current_branch?->name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Manager/Atasan</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->manager?->full_name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Bergabung</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->join_date->format('d F Y') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Masa Kerja</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->formatted_tenure }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status Kepegawaian</label>
                            <p class="text-base font-semibold text-gray-900">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $employee->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $employee->status === 'resigned' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $employee->status === 'terminated' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </p>
                        </div>

                        @if($employee->resign_date)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Keluar</label>
                                <p class="text-base font-semibold text-gray-900">{{ $employee->resign_date->format('d F Y') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sensitive Data Tab --}}
                <div x-show="activeTab === 'sensitive'" x-cloak>
                    <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-sm text-yellow-800 font-medium">Data sensitif dienkripsi. Beberapa informasi ditampilkan dengan masking untuk keamanan.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">No. KTP</label>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-semibold text-gray-900" x-show="!showSensitive">
                                    {{ $employee->masked_id_card_number ?? '-' }}
                                </p>
                                <p class="text-base font-semibold text-gray-900" x-show="showSensitive" x-cloak>
                                    {{ $employee->id_card_number ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">No. NPWP</label>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-semibold text-gray-900" x-show="!showSensitive">
                                    {{ $employee->masked_npwp_number ?? '-' }}
                                </p>
                                <p class="text-base font-semibold text-gray-900" x-show="showSensitive" x-cloak>
                                    {{ $employee->npwp_number ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">No. BPJS Ketenagakerjaan</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->bpjs_tk_number ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">No. BPJS Kesehatan</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->bpjs_kes_number ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status Pajak</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->tax_status ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Bank</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->sensitiveData?->bank_name ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">No. Rekening</label>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-semibold text-gray-900" x-show="!showSensitive">
                                    {{ $employee->masked_bank_account_number ?? '-' }}
                                </p>
                                <p class="text-base font-semibold text-gray-900" x-show="showSensitive" x-cloak>
                                    {{ $employee->bank_account_number ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Atas Nama Rekening</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->sensitiveData?->bank_account_holder ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button @click="showSensitive = !showSensitive" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!showSensitive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="showSensitive" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            <span x-text="showSensitive ? 'Sembunyikan Data' : 'Tampilkan Data'"></span>
                        </button>
                    </div>
                </div>

                {{-- Emergency Contact Tab --}}
                <div x-show="activeTab === 'emergency'" x-cloak>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Kontak Darurat</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->emergency_contact['name'] ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Hubungan</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->emergency_contact['relationship'] ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">No. Telepon Darurat</label>
                            <div class="flex items-center gap-2">
                                <p class="text-base font-semibold text-gray-900" x-show="!showSensitive">
                                    {{ $employee->sensitiveData?->masked_emergency_contact_phone ?? '-' }}
                                </p>
                                <p class="text-base font-semibold text-gray-900" x-show="showSensitive" x-cloak>
                                    {{ $employee->emergency_contact['phone'] ?? '-' }}
                                </p>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Alamat Kontak Darurat</label>
                            <p class="text-base font-semibold text-gray-900">{{ $employee->emergency_contact['address'] ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button @click="showSensitive = !showSensitive" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!showSensitive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="showSensitive" x-cloak>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            <span x-text="showSensitive ? 'Sembunyikan Data' : 'Tampilkan Data'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('hris.employees.contracts.index', $employee->id) }}"
                class="block p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-indigo-300 transition-colors group">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 group-hover:text-indigo-600 transition-colors">Riwayat Kontrak</h3>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $employee->contracts->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Total kontrak</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 group-hover:bg-indigo-200 flex items-center justify-center transition-colors">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('hris.employees.careers.index', $employee->id) }}"
                class="block p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-purple-300 transition-colors group">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 group-hover:text-purple-600 transition-colors">Riwayat Karir</h3>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $employee->careers->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Perubahan posisi</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-100 group-hover:bg-purple-200 flex items-center justify-center transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </a>

            <a href="{{ route('hris.employees.families.index', $employee->id) }}"
                class="block p-6 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-green-300 transition-colors group">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 group-hover:text-green-600 transition-colors">Data Keluarga</h3>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ $employee->families->count() }}</p>
                        <p class="text-xs text-gray-500 mt-1">Anggota keluarga</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 group-hover:bg-green-200 flex items-center justify-center transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </a>
        </div>
    {{-- Resign Modal --}}
        <div x-show="showResignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="closeResignModal()">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showResignModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeResignModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showResignModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form action="{{ route('hris.employees.resign', $employee->id) }}" method="POST">
                        @csrf

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Resign Karyawan</h3>
                                        <p class="text-sm text-yellow-100">{{ $employee->full_name }}</p>
                                    </div>
                                </div>
                                <button @click="closeResignModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-6">
                            {{-- Warning --}}
                            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-yellow-900 mb-1">Perhatian</h4>
                                        <ul class="text-xs text-yellow-700 space-y-1">
                                            <li>• Proses resign akan menonaktifkan semua kontrak dan jabatan aktif</li>
                                            <li>• Status karyawan akan berubah menjadi "Resigned"</li>
                                            <li>• Data karyawan tetap tersimpan untuk keperluan arsip</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Resign Date --}}
                            <div>
                                <label for="resign_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Resign <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="resign_date" id="resign_date" required
                                    min="{{ $employee->join_date->format('Y-m-d') }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    value="{{ now()->format('Y-m-d') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all">
                                <p class="mt-1.5 text-xs text-gray-500">
                                    Tanggal efektif resign (antara {{ $employee->join_date->format('d M Y') }} - {{ now()->format('d M Y') }})
                                </p>
                            </div>

                            {{-- Reason --}}
                            <div>
                                <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alasan Resign <span class="text-red-500">*</span>
                                </label>
                                <textarea name="reason" id="reason" rows="4" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all"
                                    placeholder="Contoh: Mengundurkan diri untuk melanjutkan pendidikan, Pindah ke perusahaan lain, Alasan pribadi, dll"></textarea>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeResignModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Proses Resign
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Terminate Modal --}}
        <div x-show="showTerminateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="closeTerminateModal()">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showTerminateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeTerminateModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showTerminateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form action="{{ route('hris.employees.terminate', $employee->id) }}" method="POST">
                        @csrf

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Terminate Karyawan</h3>
                                        <p class="text-sm text-red-100">{{ $employee->full_name }}</p>
                                    </div>
                                </div>
                                <button @click="closeTerminateModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-6">
                            {{-- Warning --}}
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-red-900 mb-1">Peringatan Keras</h4>
                                        <ul class="text-xs text-red-700 space-y-1">
                                            <li>• Proses terminate akan mengakhiri hubungan kerja secara sepihak</li>
                                            <li>• Status karyawan akan berubah menjadi "Terminated"</li>
                                            <li>• Semua kontrak dan jabatan aktif akan dinonaktifkan</li>
                                            <li>• Proses ini tidak dapat dibatalkan tanpa reactivation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Terminate Date --}}
                            <div>
                                <label for="terminate_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Tanggal Terminate <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="terminate_date" id="terminate_date" required
                                    min="{{ $employee->join_date->format('Y-m-d') }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    value="{{ now()->format('Y-m-d') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all">
                                <p class="mt-1.5 text-xs text-gray-500">
                                    Tanggal efektif terminate (antara {{ $employee->join_date->format('d M Y') }} - {{ now()->format('d M Y') }})
                                </p>
                            </div>

                            {{-- Reason --}}
                            <div>
                                <label for="terminate_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alasan Terminate <span class="text-red-500">*</span>
                                </label>
                                <textarea name="reason" id="terminate_reason" rows="4" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all"
                                    placeholder="Contoh: Pelanggaran kode etik, Kinerja tidak memenuhi standar, Restrukturisasi perusahaan, dll"></textarea>
                            </div>

                            {{-- Confirmation Checkbox --}}
                            <div class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-xl">
                                <input type="checkbox" id="confirm_terminate" required
                                    class="w-5 h-5 mt-0.5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <div class="flex-1">
                                    <label for="confirm_terminate" class="text-sm font-semibold text-red-900 cursor-pointer">
                                        Saya memahami konsekuensi dari proses terminate ini
                                    </label>
                                    <p class="text-xs text-red-700 mt-0.5">
                                        Centang untuk melanjutkan proses terminate karyawan
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeTerminateModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Proses Terminate
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Toast Notification --}}
        <div x-data="{
                show: false,
                message: '',
                type: 'success',
                init() {
                    @if(session('success'))
                        this.notify('{{ session('success') }}', 'success');
                    @endif
                    @if(session('error'))
                        this.notify('{{ session('error') }}', 'error');
                    @endif
                },
                notify(message, type = 'success') {
                    this.show = true;
                    this.message = message;
                    this.type = type;
                    setTimeout(() => {
                        this.show = false;
                    }, 3000);
                }
            }" x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-4 right-4 z-50 flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-xl shadow-lg border border-gray-100"
            role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg"
                :class="type === 'success' ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100'">
                <template x-if="type === 'success'">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </template>
            </div>
            <div class="ml-3 text-sm font-medium text-gray-700" x-text="message"></div>
            <button type="button"
                class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-50 inline-flex items-center justify-center h-8 w-8"
                @click="show = false">
                <span class="sr-only">Close</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>
    </div>

    @push('scripts')
        <script>
            function employeeDetail() {
                return {
                    activeTab: 'personal',
                    showSensitive: false,
                    showResignModal: false,
                    showTerminateModal: false,

                    openResignModal() {
                        this.showResignModal = true;
                    },

                    closeResignModal() {
                        this.showResignModal = false;
                    },

                    openTerminateModal() {
                        this.showTerminateModal = true;
                    },

                    closeTerminateModal() {
                        this.showTerminateModal = false;
                    }
                }
            }
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
@endsection