@extends('layouts.admin')

@section('title', 'Edit Karyawan - ' . $employee->full_name)

@section('content')
    <div class="space-y-6" x-data="employeeEditForm()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.employees.show', $employee->id) }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Data Karyawan</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Perbarui informasi karyawan: <strong>{{ $employee->full_name }}</strong></p>
            </div>
        </div>

        <form action="{{ route('hris.employees.update', $employee->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Progress Steps --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 flex-1" :class="currentStep >= 1 ? 'text-indigo-600' : 'text-gray-400'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'">
                            1
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">Data Dasar</span>
                    </div>
                    <div class="w-16 h-0.5 mx-2" :class="currentStep >= 2 ? 'bg-indigo-600' : 'bg-gray-200'"></div>

                    <div class="flex items-center gap-2 flex-1" :class="currentStep >= 2 ? 'text-indigo-600' : 'text-gray-400'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'">
                            2
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">Data Personal</span>
                    </div>
                    <div class="w-16 h-0.5 mx-2" :class="currentStep >= 3 ? 'bg-indigo-600' : 'bg-gray-200'"></div>

                    <div class="flex items-center gap-2 flex-1" :class="currentStep >= 3 ? 'text-indigo-600' : 'text-gray-400'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'">
                            3
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">Data Sensitif</span>
                    </div>
                    <div class="w-16 h-0.5 mx-2" :class="currentStep >= 4 ? 'bg-indigo-600' : 'bg-gray-200'"></div>

                    <div class="flex items-center gap-2 flex-1" :class="currentStep >= 4 ? 'text-indigo-600' : 'text-gray-400'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="currentStep >= 4 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'">
                            4
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">Kepegawaian</span>
                    </div>
                </div>
            </div>

            {{-- Form Content --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Step 1: Basic Data --}}
                <div x-show="currentStep === 1" x-cloak class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Informasi Dasar</h3>
                        <p class="text-sm text-gray-500">Data utama karyawan</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- NIK --}}
                        <div>
                            <label for="nik" class="block text-sm font-semibold text-gray-700 mb-2">
                                NIK Karyawan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nik" id="nik" required
                                value="{{ old('nik', $employee->nik) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('nik') border-red-500 @enderror"
                                placeholder="Contoh: 2024120001">
                            @error('nik')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Full Name --}}
                        <div>
                            <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" id="full_name" required
                                value="{{ old('full_name', $employee->full_name) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('full_name') border-red-500 @enderror"
                                placeholder="Nama lengkap sesuai KTP">
                            @error('full_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email Corporate --}}
                        <div>
                            <label for="email_corporate" class="block text-sm font-semibold text-gray-700 mb-2">
                                Email Corporate
                            </label>
                            <input type="email" name="email_corporate" id="email_corporate"
                                value="{{ old('email_corporate', $employee->email_corporate) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('email_corporate') border-red-500 @enderror"
                                placeholder="nama@perusahaan.com">
                            @error('email_corporate')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone Number --}}
                        <div>
                            <label for="phone_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                No. Telepon
                            </label>
                            <input type="text" name="phone_number" id="phone_number"
                                value="{{ old('phone_number', $employee->phone_number) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('phone_number') border-red-500 @enderror"
                                placeholder="08123456789">
                            @error('phone_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Step 2: Personal Data --}}
                <div x-show="currentStep === 2" x-cloak class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Data Personal</h3>
                        <p class="text-sm text-gray-500">Informasi pribadi karyawan</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Place of Birth --}}
                        <div>
                            <label for="place_of_birth" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tempat Lahir
                            </label>
                            <input type="text" name="place_of_birth" id="place_of_birth"
                                value="{{ old('place_of_birth', $employee->place_of_birth) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('place_of_birth') border-red-500 @enderror"
                                placeholder="Contoh: Jakarta">
                            @error('place_of_birth')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Date of Birth --}}
                        <div>
                            <label for="date_of_birth" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Lahir
                            </label>
                            <input type="date" name="date_of_birth" id="date_of_birth"
                                value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}"
                                max="{{ now()->subYears(17)->format('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('date_of_birth') border-red-500 @enderror">
                            @error('date_of_birth')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Gender --}}
                        <div>
                            <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">
                                Jenis Kelamin
                            </label>
                            <select name="gender" id="gender"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('gender') border-red-500 @enderror">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('gender')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Marital Status --}}
                        <div>
                            <label for="marital_status" class="block text-sm font-semibold text-gray-700 mb-2">
                                Status Pernikahan
                            </label>
                            <select name="marital_status" id="marital_status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('marital_status') border-red-500 @enderror">
                                <option value="">Pilih Status</option>
                                <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>Belum Menikah</option>
                                <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>Menikah</option>
                                <option value="widow" {{ old('marital_status', $employee->marital_status) == 'widow' ? 'selected' : '' }}>Janda</option>
                                <option value="widower" {{ old('marital_status', $employee->marital_status) == 'widower' ? 'selected' : '' }}>Duda</option>
                            </select>
                            @error('marital_status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Religion --}}
                        <div class="md:col-span-2">
                            <label for="religion" class="block text-sm font-semibold text-gray-700 mb-2">
                                Agama
                            </label>
                            <select name="religion" id="religion"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('religion') border-red-500 @enderror">
                                <option value="">Pilih Agama</option>
                                <option value="Islam" {{ old('religion', $employee->religion) == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion', $employee->religion) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('religion', $employee->religion) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion', $employee->religion) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion', $employee->religion) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('religion', $employee->religion) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                            @error('religion')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Step 3: Sensitive Data --}}
                <div x-show="currentStep === 3" x-cloak class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Data Sensitif</h3>
                        <p class="text-sm text-gray-500">Informasi yang akan dienkripsi</p>
                    </div>

                    {{-- Warning Box --}}
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900 mb-1">Data ini akan dienkripsi</p>
                                <p class="text-xs text-yellow-700">Semua data sensitif akan disimpan dengan enkripsi untuk keamanan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- ID Card Number --}}
                        <div>
                            <label for="id_card_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                No. KTP
                            </label>
                            <input type="text" name="id_card_number" id="id_card_number"
                                value="{{ old('id_card_number', $employee->sensitiveData?->id_card_number) }}"
                                maxlength="16"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('id_card_number') border-red-500 @enderror"
                                placeholder="16 digit">
                            @error('id_card_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NPWP Number --}}
                        <div>
                            <label for="npwp_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                No. NPWP
                            </label>
                            <input type="text" name="npwp_number" id="npwp_number"
                                value="{{ old('npwp_number', $employee->sensitiveData?->npwp_number) }}"
                                maxlength="20"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('npwp_number') border-red-500 @enderror"
                                placeholder="XX.XXX.XXX.X-XXX.XXX">
                            @error('npwp_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- BPJS TK Number --}}
                        <div>
                            <label for="bpjs_tk_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                No. BPJS Ketenagakerjaan
                            </label>
                            <input type="text" name="bpjs_tk_number" id="bpjs_tk_number"
                                value="{{ old('bpjs_tk_number', $employee->sensitiveData?->bpjs_tk_number) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('bpjs_tk_number') border-red-500 @enderror"
                                placeholder="No. BPJS TK">
                            @error('bpjs_tk_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- BPJS Kesehatan Number --}}
                        <div>
                            <label for="bpjs_kes_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                No. BPJS Kesehatan
                            </label>
                            <input type="text" name="bpjs_kes_number" id="bpjs_kes_number"
                                value="{{ old('bpjs_kes_number', $employee->sensitiveData?->bpjs_kes_number) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('bpjs_kes_number') border-red-500 @enderror"
                                placeholder="No. BPJS Kesehatan">
                            @error('bpjs_kes_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tax Status --}}
                        <div>
                            <label for="tax_status" class="block text-sm font-semibold text-gray-700 mb-2">
                                Status Pajak (PTKP)
                            </label>
                            <select name="tax_status" id="tax_status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('tax_status') border-red-500 @enderror">
                                <option value="">Pilih Status Pajak</option>
                                <option value="TK/0" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'TK/0' ? 'selected' : '' }}>TK/0 - Tidak Kawin, Tanpa Tanggungan</option>
                                <option value="TK/1" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'TK/1' ? 'selected' : '' }}>TK/1 - Tidak Kawin, 1 Tanggungan</option>
                                <option value="TK/2" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'TK/2' ? 'selected' : '' }}>TK/2 - Tidak Kawin, 2 Tanggungan</option>
                                <option value="TK/3" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'TK/3' ? 'selected' : '' }}>TK/3 - Tidak Kawin, 3 Tanggungan</option>
                                <option value="K/0" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'K/0' ? 'selected' : '' }}>K/0 - Kawin, Tanpa Tanggungan</option>
                                <option value="K/1" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'K/1' ? 'selected' : '' }}>K/1 - Kawin, 1 Tanggungan</option>
                                <option value="K/2" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'K/2' ? 'selected' : '' }}>K/2 - Kawin, 2 Tanggungan</option>
                                <option value="K/3" {{ old('tax_status', $employee->sensitiveData?->tax_status) == 'K/3' ? 'selected' : '' }}>K/3 - Kawin, 3 Tanggungan</option>
                            </select>
                            @error('tax_status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Bank Name --}}
                        <div>
                            <label for="bank_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Bank
                            </label>
                            <input type="text" name="bank_name" id="bank_name"
                                value="{{ old('bank_name', $employee->sensitiveData?->bank_name) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('bank_name') border-red-500 @enderror"
                                placeholder="Contoh: Bank BCA, Bank Mandiri">
                            @error('bank_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Bank Account Number --}}
                        <div>
                            <label for="bank_account_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                No. Rekening
                            </label>
                            <input type="text" name="bank_account_number" id="bank_account_number"
                                value="{{ old('bank_account_number', $employee->sensitiveData?->bank_account_number) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('bank_account_number') border-red-500 @enderror"
                                placeholder="No. rekening bank">
                            @error('bank_account_number')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Bank Account Holder --}}
                        <div>
                            <label for="bank_account_holder" class="block text-sm font-semibold text-gray-700 mb-2">
                                Atas Nama Rekening
                            </label>
                            <input type="text" name="bank_account_holder" id="bank_account_holder"
                                value="{{ old('bank_account_holder', $employee->sensitiveData?->bank_account_holder) }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('bank_account_holder') border-red-500 @enderror"
                                placeholder="Nama pemilik rekening">
                            @error('bank_account_holder')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Emergency Contact Section --}}
                    <div class="pt-6 border-t border-gray-200">
                        <h4 class="text-base font-bold text-gray-900 mb-4">Kontak Darurat</h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Emergency Contact Name --}}
                            <div>
                                <label for="emergency_contact_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Kontak Darurat
                                </label>
                                <input type="text" name="emergency_contact_name" id="emergency_contact_name"
                                    value="{{ old('emergency_contact_name', $employee->sensitiveData?->emergency_contact_name) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('emergency_contact_name') border-red-500 @enderror"
                                    placeholder="Nama lengkap">
                                @error('emergency_contact_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Emergency Contact Relationship --}}
                            <div>
                                <label for="emergency_contact_relationship" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Hubungan
                                </label>
                                <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship"
                                    value="{{ old('emergency_contact_relationship', $employee->sensitiveData?->emergency_contact_relationship) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('emergency_contact_relationship') border-red-500 @enderror"
                                    placeholder="Contoh: Istri, Suami, Orang Tua, Saudara">
                                @error('emergency_contact_relationship')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Emergency Contact Phone --}}
                            <div>
                                <label for="emergency_contact_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    No. Telepon Darurat
                                </label>
                                <input type="text" name="emergency_contact_phone" id="emergency_contact_phone"
                                    value="{{ old('emergency_contact_phone', $employee->sensitiveData?->emergency_contact_phone) }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('emergency_contact_phone') border-red-500 @enderror"
                                    placeholder="08123456789">
                                @error('emergency_contact_phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Emergency Contact Address --}}
                            <div class="md:col-span-2">
                                <label for="emergency_contact_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alamat Kontak Darurat
                                </label>
                                <textarea name="emergency_contact_address" id="emergency_contact_address" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('emergency_contact_address') border-red-500 @enderror"
                                    placeholder="Alamat lengkap">{{ old('emergency_contact_address', $employee->sensitiveData?->emergency_contact_address) }}</textarea>
                                @error('emergency_contact_address')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Employment Data --}}
                <div x-show="currentStep === 4" x-cloak class="p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Data Kepegawaian</h3>
                        <p class="text-sm text-gray-500">Informasi posisi dan jabatan</p>
                    </div>

                    {{-- Info Box --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-900 mb-1">Perubahan Data Kepegawaian</p>
                                <p class="text-xs text-blue-700">
                                    Data kepegawaian (department, position, dll) dikelola melalui modul <strong>Employee Career</strong>. 
                                    Klik tab "Riwayat Karir" untuk mengelola perubahan jabatan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Join Date --}}
                        <div>
                            <label for="join_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Bergabung <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="join_date" id="join_date" required
                                value="{{ old('join_date', $employee->join_date->format('Y-m-d')) }}"
                                max="{{ now()->format('Y-m-d') }}"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('join_date') border-red-500 @enderror">
                            @error('join_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                                Status Kepegawaian <span class="text-red-500">*</span>
                            </label>
                            <select name="status" id="status" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="resigned" {{ old('status', $employee->status) == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                <option value="terminated" {{ old('status', $employee->status) == 'terminated' ? 'selected' : '' }}>Terminated</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Current Department (Read Only) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Departemen Saat Ini
                            </label>
                            <input type="text" readonly
                                value="{{ $employee->current_department?->name ?? '-' }}"
                                class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-xl text-gray-600 cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500">Kelola di modul Employee Career</p>
                        </div>

                        {{-- Current Position (Read Only) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Posisi Saat Ini
                            </label>
                            <input type="text" readonly
                                value="{{ $employee->current_position?->name ?? '-' }}"
                                class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-xl text-gray-600 cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500">Kelola di modul Employee Career</p>
                        </div>

                        {{-- Current Level (Read Only) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Level Saat Ini
                            </label>
                            <input type="text" readonly
                                value="{{ $employee->current_level ? $employee->current_level->grade_code . ' - ' . $employee->current_level->name : '-' }}"
                                class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-xl text-gray-600 cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500">Kelola di modul Employee Career</p>
                        </div>

                        {{-- Current Branch (Read Only) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Lokasi Saat Ini
                            </label>
                            <input type="text" readonly
                                value="{{ $employee->current_branch?->name ?? '-' }}"
                                class="w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-xl text-gray-600 cursor-not-allowed">
                            <p class="mt-1 text-xs text-gray-500">Kelola di modul Employee Career</p>
                        </div>
                    </div>

                    {{-- Quick Link to Career Module --}}
                    <div class="pt-4">
                        <a href="{{ route('hris.employees.careers.index', $employee->id) }}" target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-100 hover:bg-purple-200 text-purple-700 text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Kelola Riwayat Karir
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Navigation Buttons --}}
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t border-gray-200">
                    <button type="button" @click="previousStep()" x-show="currentStep > 1"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Sebelumnya
                    </button>

                    <div class="flex-1"></div>

                    <button type="button" @click="nextStep()" x-show="currentStep < 4"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <button type="submit" x-show="currentStep === 4"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Data Karyawan
                    </button>
                </div>

            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function employeeEditForm() {
                return {
                    currentStep: 1,

                    nextStep() {
                        if (this.currentStep < 4) {
                            this.currentStep++;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    },

                    previousStep() {
                        if (this.currentStep > 1) {
                            this.currentStep--;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
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