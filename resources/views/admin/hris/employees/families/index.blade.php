@extends('layouts.admin')

@section('title', 'Data Keluarga - ' . $employee->full_name)

@section('content')
    <div class="space-y-6" x-data="familyManager()">
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
                    <h1 class="text-2xl font-bold text-gray-900">Data Keluarga</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Kelola data keluarga untuk:
                    <strong>{{ $employee->full_name }}</strong>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openCreateModal()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Anggota Keluarga
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Anggota</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $employee->families->count() }}</h3>
                    </div>
                    <div class="bg-indigo-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Kontak Darurat</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $employee->emergencyContacts->count() }}</h3>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tanggungan BPJS</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $employee->bpjsDependents->count() }}</h3>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Anak</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $employee->children_count }}</h3>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Family Members List --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Anggota Keluarga</h2>
            </div>

            <div class="p-6">
                @if($employee->families->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($employee->families as $family)
                            @php
                                $relationColors = [
                                    'spouse' => 'bg-pink-50 text-pink-700 border-pink-100',
                                    'child' => 'bg-blue-50 text-blue-700 border-blue-100',
                                    'parent' => 'bg-green-50 text-green-700 border-green-100',
                                    'sibling' => 'bg-purple-50 text-purple-700 border-purple-100',
                                ];
                            @endphp
                            <div
                                class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200 hover:border-indigo-300 transition-colors group">
                                <div
                                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-sm flex-shrink-0">
                                    {{ strtoupper(substr($family->name, 0, 2)) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="flex-1">
                                            <h3 class="text-sm font-semibold text-gray-900">{{ $family->name }}</h3>
                                            <span
                                                class="inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-medium border {{ $relationColors[$family->relation] ?? 'bg-gray-50 text-gray-700 border-gray-100' }}">
                                                {{ $family->relation_label }}
                                            </span>
                                        </div>
                                    </div>

                                    @if($family->phone)
                                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                            </svg>
                                            <span>{{ $family->phone }}</span>
                                        </div>
                                    @endif

                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        @if($family->is_emergency_contact)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-100 text-red-700 text-xs font-medium rounded">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                Kontak Darurat
                                            </span>
                                        @endif

                                        @if($family->is_bpjs_dependent)
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                </svg>
                                                BPJS
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="openEditModal({{ $family->id }}, '{{ addslashes($family->name) }}', '{{ $family->relation }}', '{{ addslashes($family->phone ?? '') }}', {{ $family->is_emergency_contact ? 'true' : 'false' }}, {{ $family->is_bpjs_dependent ? 'true' : 'false' }})"
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form action="{{ route('hris.employees.families.destroy', [$employee->id, $family->id]) }}"
                                        method="POST" class="inline-block"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus data keluarga ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada data keluarga</p>
                        <p class="text-gray-400 text-sm mb-4">Mulai dengan menambahkan anggota keluarga pertama</p>
                        <button @click="openCreateModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Anggota Keluarga
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Create/Edit Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="closeModal()">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form
                        :action="modalMode === 'create' ? '{{ route('hris.employees.families.store', $employee->id) }}' : '{{ url('hris/employees/' . $employee->id . '/families') }}/' + editFamilyId"
                        method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="modalMode === 'edit' ? 'PUT' : 'POST'">

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white" id="modal-title"
                                            x-text="modalMode === 'create' ? 'Tambah Anggota Keluarga' : 'Edit Anggota Keluarga'">
                                        </h3>
                                        <p class="text-sm text-indigo-100">{{ $employee->full_name }}</p>
                                    </div>
                                </div>
                                <button @click="closeModal()" type="button"
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
                            {{-- Display Validation Errors --}}
                            @if ($errors->any())
                                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-red-900 mb-1">Terjadi Kesalahan</h4>
                                            <ul class="text-xs text-red-700 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>• {{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-6">
                                {{-- Name --}}
                                <div>
                                    <label for="family_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="family_name" required x-model="familyName"
                                        value="{{ old('name') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                                        placeholder="Nama lengkap anggota keluarga">
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Relation --}}
                                <div>
                                    <label for="relation" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Hubungan Keluarga <span class="text-red-500">*</span>
                                    </label>
                                    <select name="relation" id="relation" required x-model="relation"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('relation') border-red-500 @enderror">
                                        <option value="">Pilih Hubungan</option>
                                        <option value="spouse" {{ old('relation') == 'spouse' ? 'selected' : '' }}>Suami/Istri
                                        </option>
                                        <option value="child" {{ old('relation') == 'child' ? 'selected' : '' }}>Anak</option>
                                        <option value="parent" {{ old('relation') == 'parent' ? 'selected' : '' }}>Orang Tua
                                        </option>
                                        <option value="sibling" {{ old('relation') == 'sibling' ? 'selected' : '' }}>Saudara
                                            Kandung</option>
                                    </select>
                                    @error('relation')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div>
                                    <label for="family_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nomor Telepon
                                    </label>
                                    <input type="text" name="phone" id="family_phone" x-model="phone"
                                        value="{{ old('phone') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('phone') border-red-500 @enderror"
                                        placeholder="08123456789">
                                    @error('phone')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Checkboxes --}}
                                <div class="space-y-3">
                                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                                        <input type="hidden" name="is_emergency_contact" value="0">
                                        <input type="checkbox" name="is_emergency_contact" id="is_emergency_contact"
                                            value="1" x-model="isEmergencyContact"
                                            class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="flex-1">
                                            <label for="is_emergency_contact"
                                                class="text-sm font-semibold text-gray-900 cursor-pointer">
                                                Kontak Darurat
                                            </label>
                                            <p class="text-xs text-gray-500 mt-0.5">Jadikan sebagai kontak yang dapat
                                                dihubungi dalam keadaan darurat</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3 p-4 bg-gray-50 rounded-xl">
                                        <input type="hidden" name="is_bpjs_dependent" value="0">
                                        <input type="checkbox" name="is_bpjs_dependent" id="is_bpjs_dependent" value="1"
                                            x-model="isBpjsDependent"
                                            class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <div class="flex-1">
                                            <label for="is_bpjs_dependent"
                                                class="text-sm font-semibold text-gray-900 cursor-pointer">
                                                Tanggungan BPJS
                                            </label>
                                            <p class="text-xs text-gray-500 mt-0.5">Termasuk dalam tanggungan BPJS Kesehatan
                                                karyawan</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Box --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-blue-900 mb-1">Informasi</h4>
                                        <p class="text-xs text-blue-700">Data keluarga ini akan digunakan untuk keperluan
                                            administrasi, BPJS, dan kontak darurat karyawan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span x-text="modalMode === 'create' ? 'Simpan Data' : 'Update Data'"></span>
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
            let familyManagerInstance = null;

            function familyManager() {
                return {
                    showModal: false,
                    modalMode: 'create',
                    editFamilyId: null,
                    familyName: '',
                    relation: '',
                    phone: '',
                    isEmergencyContact: false,
                    isBpjsDependent: false,

                    init() {
                        familyManagerInstance = this;
                    },

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.familyName = '';
                        this.relation = '';
                        this.phone = '';
                        this.isEmergencyContact = false;
                        this.isBpjsDependent = false;
                        this.showModal = true;
                    },

                    openEditModal(id, name, relation, phone, isEmergency, isBpjs) {
                        this.modalMode = 'edit';
                        this.editFamilyId = id;
                        this.familyName = name;
                        this.relation = relation;
                        this.phone = phone || '';
                        this.isEmergencyContact = isEmergency;
                        this.isBpjsDependent = isBpjs;
                        this.showModal = true;
                    },

                    closeModal() {
                        this.showModal = false;
                        setTimeout(() => {
                            this.modalMode = 'create';
                            this.editFamilyId = null;
                            this.familyName = '';
                            this.relation = '';
                            this.phone = '';
                            this.isEmergencyContact = false;
                            this.isBpjsDependent = false;
                        }, 300);
                    }
                }
            }

            // Auto open modal if there are validation errors
            document.addEventListener('DOMContentLoaded', function () {
                @if($errors->any() && old('_method'))
                    // Edit mode
                    if (familyManagerInstance) {
                        // We don't have the ID from old input, so just open create mode
                        // In production, you might want to pass the ID differently
                        familyManagerInstance.openCreateModal();

                        setTimeout(() => {
                            familyManagerInstance.familyName = '{{ old('name') }}';
                            familyManagerInstance.relation = '{{ old('relation') }}';
                            familyManagerInstance.phone = '{{ old('phone') }}';
                            familyManagerInstance.isEmergencyContact = {{ old('is_emergency_contact') ? 'true' : 'false' }};
                            familyManagerInstance.isBpjsDependent = {{ old('is_bpjs_dependent') ? 'true' : 'false' }};
                        }, 100);
                    }
                @elseif($errors->any())
                    // Create mode
                    if (familyManagerInstance) {
                        familyManagerInstance.openCreateModal();

                        setTimeout(() => {
                            familyManagerInstance.familyName = '{{ old('name') }}';
                            familyManagerInstance.relation = '{{ old('relation') }}';
                            familyManagerInstance.phone = '{{ old('phone') }}';
                            familyManagerInstance.isEmergencyContact = {{ old('is_emergency_contact') ? 'true' : 'false' }};
                            familyManagerInstance.isBpjsDependent = {{ old('is_bpjs_dependent') ? 'true' : 'false' }};
                        }, 100);
                    }
                @endif
                                            });
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
@endsection