@extends('layouts.admin')

@section('title', 'Tambah Karyawan Baru')

@section('content')
    <div class="space-y-6" x-data="employeeForm()">
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
                    <h1 class="text-2xl font-bold text-gray-900">Tambah Karyawan Baru</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Lengkapi formulir di bawah untuk menambahkan karyawan baru</p>
            </div>
        </div>

        <form action="{{ route('hris.employees.store') }}" method="POST">
            @csrf

            {{-- Progress Steps --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 flex-1"
                        :class="currentStep >= 1 ? 'text-indigo-600' : 'text-gray-400'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'">
                            1
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">Data Dasar</span>
                    </div>
                    <div class="w-16 h-0.5 mx-2" :class="currentStep >= 2 ? 'bg-indigo-600' : 'bg-gray-200'"></div>

                    <div class="flex items-center gap-2 flex-1"
                        :class="currentStep >= 2 ? 'text-indigo-600' : 'text-gray-400'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'">
                            2
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">Data Personal</span>
                    </div>
                    <div class="w-16 h-0.5 mx-2" :class="currentStep >= 3 ? 'bg-indigo-600' : 'bg-gray-200'"></div>

                    <div class="flex items-center gap-2 flex-1"
                        :class="currentStep >= 3 ? 'text-indigo-600' : 'text-gray-400'">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-colors"
                            :class="currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500'">
                            3
                        </div>
                        <span class="text-sm font-medium hidden sm:inline">Data Sensitif</span>
                    </div>
                    <div class="w-16 h-0.5 mx-2" :class="currentStep >= 4 ? 'bg-indigo-600' : 'bg-gray-200'"></div>

                    <div class="flex items-center gap-2 flex-1"
                        :class="currentStep >= 4 ? 'text-indigo-600' : 'text-gray-400'">
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
                        {{-- NIK with Auto Generate --}}
                        <div>
                            <label for="nik" class="block text-sm font-semibold text-gray-700 mb-2">
                                NIK Karyawan
                            </label>
                            <div class="relative">
                                <input type="text" name="nik" id="nik" x-model="nikValue" value="{{ old('nik', $autoNik) }}"
                                    class="w-full px-4 py-3 pr-24 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('nik') border-red-500 @enderror"
                                    placeholder="Auto-generated">
                                <button type="button" @click="generateNik()"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-3 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 text-xs font-medium rounded-lg transition-colors">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Generate
                                </button>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">
                                Format: YYMM0001 (Auto-generated berdasarkan tanggal bergabung). Kosongkan untuk
                                auto-generate.
                            </p>
                            @error('nik')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Full Name --}}
                        <div>
                            <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" id="full_name" required value="{{ old('full_name') }}"
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
                                value="{{ old('email_corporate') }}"
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
                            <input type="text" name="phone_number" id="phone_number" value="{{ old('phone_number') }}"
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
                            <input type="text" name="place_of_birth" id="place_of_birth" value="{{ old('place_of_birth') }}"
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
                            <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
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
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
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
                                <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Belum Menikah
                                </option>
                                <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Menikah
                                </option>
                                <option value="widow" {{ old('marital_status') == 'widow' ? 'selected' : '' }}>Janda</option>
                                <option value="widower" {{ old('marital_status') == 'widower' ? 'selected' : '' }}>Duda
                                </option>
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
                                <option value="Islam" {{ old('religion') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('religion') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('religion') == 'Konghucu' ? 'selected' : '' }}>Konghucu
                                </option>
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
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-900 mb-1">Data ini akan dienkripsi</p>
                                <p class="text-xs text-yellow-700">Semua data sensitif akan disimpan dengan enkripsi untuk
                                    keamanan.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- ID Card Number --}}
                        <div>
                            <label for="id_card_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                No. KTP
                            </label>
                            <input type="text" name="id_card_number" id="id_card_number" value="{{ old('id_card_number') }}"
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
                            <input type="text" name="npwp_number" id="npwp_number" value="{{ old('npwp_number') }}"
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
                            <input type="text" name="bpjs_tk_number" id="bpjs_tk_number" value="{{ old('bpjs_tk_number') }}"
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
                                value="{{ old('bpjs_kes_number') }}"
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
                                <option value="TK/0" {{ old('tax_status') == 'TK/0' ? 'selected' : '' }}>TK/0 - Tidak Kawin,
                                    Tanpa Tanggungan</option>
                                <option value="TK/1" {{ old('tax_status') == 'TK/1' ? 'selected' : '' }}>TK/1 - Tidak Kawin, 1
                                    Tanggungan</option>
                                <option value="TK/2" {{ old('tax_status') == 'TK/2' ? 'selected' : '' }}>TK/2 - Tidak Kawin, 2
                                    Tanggungan</option>
                                <option value="TK/3" {{ old('tax_status') == 'TK/3' ? 'selected' : '' }}>TK/3 - Tidak Kawin, 3
                                    Tanggungan</option>
                                <option value="K/0" {{ old('tax_status') == 'K/0' ? 'selected' : '' }}>K/0 - Kawin, Tanpa
                                    Tanggungan</option>
                                <option value="K/1" {{ old('tax_status') == 'K/1' ? 'selected' : '' }}>K/1 - Kawin, 1
                                    Tanggungan</option>
                                <option value="K/2" {{ old('tax_status') == 'K/2' ? 'selected' : '' }}>K/2 - Kawin, 2
                                    Tanggungan</option>
                                <option value="K/3" {{ old('tax_status') == 'K/3' ? 'selected' : '' }}>K/3 - Kawin, 3
                                    Tanggungan</option>
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
                            <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name') }}"
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
                                value="{{ old('bank_account_number') }}"
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
                                value="{{ old('bank_account_holder') }}"
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
                                    value="{{ old('emergency_contact_name') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('emergency_contact_name') border-red-500 @enderror"
                                    placeholder="Nama lengkap">
                                @error('emergency_contact_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Emergency Contact Relationship --}}
                            <div>
                                <label for="emergency_contact_relationship"
                                    class="block text-sm font-semibold text-gray-700 mb-2">
                                    Hubungan
                                </label>
                                <input type="text" name="emergency_contact_relationship" id="emergency_contact_relationship"
                                    value="{{ old('emergency_contact_relationship') }}"
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
                                    value="{{ old('emergency_contact_phone') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('emergency_contact_phone') border-red-500 @enderror"
                                    placeholder="08123456789">
                                @error('emergency_contact_phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Emergency Contact Address --}}
                            <div class="md:col-span-2">
                                <label for="emergency_contact_address"
                                    class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alamat Kontak Darurat
                                </label>
                                <textarea name="emergency_contact_address" id="emergency_contact_address" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('emergency_contact_address') border-red-500 @enderror"
                                    placeholder="Alamat lengkap">{{ old('emergency_contact_address') }}</textarea>
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

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Join Date --}}
                        <div>
                            <label for="join_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tanggal Bergabung <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="join_date" id="join_date" required
                                value="{{ old('join_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}"
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
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="resigned" {{ old('status') == 'resigned' ? 'selected' : '' }}>Resigned</option>
                                <option value="terminated" {{ old('status') == 'terminated' ? 'selected' : '' }}>Terminated
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Department --}}
                        <div>
                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Departemen <span class="text-red-500">*</span>
                            </label>
                            <select name="department_id" id="department_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('department_id') border-red-500 @enderror">
                                <option value="">Pilih Departemen</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Position --}}
                        <div>
                            <label for="position_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Posisi/Jabatan <span class="text-red-500">*</span>
                            </label>
                            <select name="position_id" id="position_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('position_id') border-red-500 @enderror">
                                <option value="">Pilih Posisi</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Level --}}
                        <div>
                            <label for="level_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Level/Grade <span class="text-red-500">*</span>
                            </label>
                            <select name="level_id" id="level_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('level_id') border-red-500 @enderror">
                                <option value="">Pilih Level</option>
                                @foreach($levels as $level)
                                    <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>
                                        {{ $level->grade_code }} - {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('level_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Branch/Location --}}
                        <div>
                            <label for="branch_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Lokasi/Cabang Kerja
                            </label>
                            <select name="branch_id" id="branch_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('branch_id') border-red-500 @enderror">
                                <option value="">Pilih Lokasi</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ old('branch_id') == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Manager --}}
                        <div>
                            <label for="manager_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Manager/Atasan Langsung
                            </label>
                            <select name="manager_id" id="manager_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('manager_id') border-red-500 @enderror">
                                <option value="">Pilih Manager</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('manager_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }} ({{ $emp->nik }})
                                    </option>
                                @endforeach
                            </select>
                            @error('manager_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- User Account Section --}}
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex items-start gap-3 mb-4">
                            <input type="checkbox" name="create_user_account" id="create_user_account"
                                x-model="createUserAccount" value="1" {{ old('create_user_account') ? 'checked' : '' }}
                                class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <div class="flex-1">
                                <label for="create_user_account"
                                    class="text-base font-semibold text-gray-900 cursor-pointer">
                                    Buat Akun User untuk Login
                                </label>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Centang jika karyawan ini akan memiliki akses ke sistem
                                </p>
                            </div>
                        </div>

                        <div x-show="createUserAccount" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            {{-- User Email --}}
                            <div>
                                <label for="user_email" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Login <span class="text-red-500" x-show="createUserAccount">*</span>
                                </label>
                                <input type="email" name="user_email" id="user_email" :required="createUserAccount"
                                    value="{{ old('user_email') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('user_email') border-red-500 @enderror"
                                    placeholder="email@example.com">
                                @error('user_email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- User Password --}}
                            <div>
                                <label for="user_password" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Password <span class="text-red-500" x-show="createUserAccount">*</span>
                                </label>
                                <input type="password" name="user_password" id="user_password" :required="createUserAccount"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('user_password') border-red-500 @enderror"
                                    placeholder="Minimal 8 karakter">
                                @error('user_password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
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
                        Simpan Data Karyawan
                    </button>
                </div>

            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function employeeForm() {
                return {
                    currentStep: 1,
                    createUserAccount: {{ old('create_user_account') ? 'true' : 'false' }},
                    nikValue: '{{ old('nik', $autoNik) }}',

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
                    },

                    async generateNik() {
                        try {
                            const response = await fetch('{{ route('hris.employees.generate-nik') }}');
                            const data = await response.json();

                            if (data.success) {
                                this.nikValue = data.nik;
                                document.getElementById('nik').value = data.nik;
                            } else {
                                alert('Gagal generate NIK: ' + data.message);
                            }
                        } catch (error) {
                            console.error('Error generating NIK:', error);
                            alert('Terjadi kesalahan saat generate NIK');
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