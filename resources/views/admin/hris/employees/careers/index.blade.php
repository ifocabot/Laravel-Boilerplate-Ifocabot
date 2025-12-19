@extends('layouts.admin')

@section('title', 'Riwayat Karir - ' . $employee->full_name)

@section('content')
    <div class="space-y-6" x-data="careerManager()">
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
                    <h1 class="text-2xl font-bold text-gray-900">Riwayat Karir & Kompensasi</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">Kelola riwayat jabatan dan perubahan gaji untuk: <strong>{{ $employee->full_name }}</strong></p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openCreateModal()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Riwayat Karir
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Riwayat</p>
                        <h3 class="text-3xl font-bold mt-2">{{ $totalCareers }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Posisi Saat Ini</p>
                        <h3 class="text-lg font-bold text-gray-900 mt-2">
                            @if($employee->current_career)
                                {{ $employee->current_career->position->name }}
                            @else
                                -
                            @endif
                        </h3>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Promosi</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $totalPromotions }}</h3>
                            <p class="text-xs text-gray-500 mt-1">Riwayat tidak aktif</p>
                        </div>
                        <div class="bg-green-50 rounded-xl p-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                </div>
        </div>

        {{-- Career Timeline --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Timeline Karir</h2>
            </div>

            <div class="p-6">
                @if($employee->careers->count() > 0)
                    <div class="space-y-6">
                        @foreach($employee->careers as $index => $career)
                            <div class="relative">
                                {{-- Timeline Line --}}
                                @if(!$loop->last)
                                    <div class="absolute left-6 top-16 bottom-0 w-0.5 bg-gray-200"></div>
                                @endif

                                {{-- Career Card --}}
                                <div class="flex gap-4 group">
                                    {{-- Timeline Dot --}}
                                    <div class="relative flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $career->is_active ? 'bg-gradient-to-br from-green-500 to-green-600 ring-4 ring-green-100' : 'bg-gradient-to-br from-gray-400 to-gray-500' }} shadow-lg">
                                            @if($career->is_active)
                                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Career Details Card --}}
                                    <div class="flex-1 bg-gray-50 rounded-xl border border-gray-200 p-5 hover:border-indigo-300 transition-colors">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1">
                                                {{-- Header --}}
                                                <div class="flex items-start justify-between mb-3">
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <h3 class="text-lg font-bold text-gray-900">{{ $career->position->name }}</h3>
                                                            @if($career->is_active)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                                    Aktif
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                            </svg>
                                                            <span>{{ $career->department->name }}</span>
                                                        </div>
                                                    </div>

                                                    {{-- Period --}}
                                                    <div class="text-right">
                                                        <div class="text-sm font-semibold text-gray-900">
                                                            {{ $career->start_date->format('d M Y') }}
                                                            @if($career->end_date)
                                                                - {{ $career->end_date->format('d M Y') }}
                                                            @else
                                                                - Sekarang
                                                            @endif
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            {{ $career->duration }}
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Details Grid --}}
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                                    {{-- Level --}}
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-gray-500">Level</p>
                                                            <p class="text-sm font-semibold text-gray-900">{{ $career->level->grade_code }}</p>
                                                        </div>
                                                    </div>

                                                    {{-- Branch --}}
                                                    @if($career->branch)
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-gray-500">Lokasi</p>
                                                                <p class="text-sm font-semibold text-gray-900">{{ $career->branch->name }}</p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Manager --}}
                                                    @if($career->manager)
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-gray-500">Manager</p>
                                                                <p class="text-sm font-semibold text-gray-900">{{ $career->manager->full_name }}</p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Subordinates --}}
                                                    @if($career->subordinates_count > 0)
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-gray-500">Bawahan</p>
                                                                <p class="text-sm font-semibold text-gray-900">{{ $career->subordinates_count }} orang</p>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Notes --}}
                                                @if($career->notes)
                                                    <div class="p-3 bg-white rounded-lg border border-gray-200">
                                                        <p class="text-xs text-gray-500 mb-1">Catatan:</p>
                                                        <p class="text-sm text-gray-700">{{ $career->notes }}</p>
                                                    </div>
                                                @endif

                                                {{-- Promotion Info --}}
                                                @php
                                                    $promotionInfo = $career->getPromotionInfo();
                                                @endphp

                                                @if($promotionInfo['is_promotion'])
                                                    <div class="mt-3 flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                                        </svg>
                                                        <div class="flex-1">
                                                            <p class="text-sm font-semibold text-green-900">Promosi/Mutasi</p>
                                                            <div class="text-xs text-green-700 mt-0.5 space-y-1">
                                                                @foreach($promotionInfo['changes'] as $change)
                                                                    <p>• {{ ucfirst($change['type']) }}: {{ $change['from'] }} → {{ $change['to'] }}</p>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Actions --}}
                                            <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="openEditModal(
                                                    {{ $career->id }},
                                                    {{ $career->department_id }},
                                                    {{ $career->position_id }},
                                                    {{ $career->level_id }},
                                                    {{ $career->branch_id ?? 'null' }},
                                                    {{ $career->manager_id ?? 'null' }},
                                                    '{{ $career->start_date->format('Y-m-d') }}',
                                                    '{{ $career->end_date ? $career->end_date->format('Y-m-d') : '' }}',
                                                    {{ $career->is_active ? 'true' : 'false' }},
                                                    '{{ addslashes($career->notes ?? '') }}'
                                                )"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                    title="Edit">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>

                                                @if(!$career->is_active)
                                                    <form action="{{ route('hris.employees.careers.destroy', [$employee->id, $career->id]) }}" method="POST"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat karir ini?');">
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
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada riwayat karir</p>
                        <p class="text-gray-400 text-sm mb-4">Mulai dengan menambahkan riwayat karir pertama</p>
                        <button @click="openCreateModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Riwayat Karir
                        </button>
                    </div>
                @endif
            </div>
        </div>

{{-- Create/Edit Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="closeModal()">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">

                    <form :action="formAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="formMethod">

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white" id="modal-title" 
                                            x-text="modalMode === 'create' ? 'Tambah Riwayat Karir' : 'Edit Riwayat Karir'"></h3>
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
                        <div class="px-6 py-6 space-y-6 max-h-[70vh] overflow-y-auto">
                            {{-- Display Validation Errors --}}
                            @if ($errors->any())
                                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                {{-- Position Information --}}
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 mb-4">Informasi Posisi</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Department --}}
                                        <div>
                                            <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Departemen <span class="text-red-500">*</span>
                                            </label>
                                            <select name="department_id" id="department_id" required x-model="departmentId"
                                                @change="loadPositions($event.target.value)"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                                <option value="">Pilih Departemen</option>
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Position --}}
                                        <div>
                                            <label for="position_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Posisi <span class="text-red-500">*</span>
                                            </label>
                                            <select name="position_id" id="position_id" required x-model="positionId"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                                <option value="">Pilih Posisi</option>
                                                @foreach($positions as $pos)
                                                    <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Level --}}
                                        <div>
                                            <label for="level_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Level/Grade <span class="text-red-500">*</span>
                                            </label>
                                            <select name="level_id" id="level_id" required x-model="levelId"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                                <option value="">Pilih Level</option>
                                                @foreach($levels as $level)
                                                    <option value="{{ $level->id }}">{{ $level->grade_code }} - {{ $level->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Branch --}}
                                        <div>
                                            <label for="branch_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Lokasi/Cabang Kerja
                                            </label>
                                            <select name="branch_id" id="branch_id" x-model="branchId"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                                <option value="">Pilih Lokasi</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Reporting Line --}}
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 mb-4">Reporting Line</h4>
                                    
                                    <div>
                                        <label for="manager_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Manager/Atasan Langsung
                                        </label>
                                        <select name="manager_id" id="manager_id" x-model="managerId"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                            <option value="">Pilih Manager</option>
                                            @foreach($managers as $mgr)
                                                <option value="{{ $mgr->id }}">{{ $mgr->full_name }} ({{ $mgr->nik }})</option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1.5 text-xs text-gray-500">Manager yang dipilih akan menjadi approver di workflow approval</p>
                                    </div>
                                </div>

                                {{-- Period Information --}}
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 mb-4">Periode Efektif</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Start Date --}}
                                        <div>
                                            <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Tanggal Mulai (Efektif SK) <span class="text-red-500">*</span>
                                            </label>
                                            <input type="date" name="start_date" id="start_date" required x-model="startDate"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                        </div>

                                        {{-- End Date --}}
                                        <div>
                                            <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Tanggal Akhir <span class="text-gray-400 text-xs">(Kosongkan jika masih aktif)</span>
                                            </label>
                                            <input type="date" name="end_date" id="end_date" x-model="endDate"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                        </div>
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Catatan/Alasan
                                    </label>
                                    <textarea name="notes" id="notes" rows="3" x-model="notes"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Contoh: Promosi Tahunan, Mutasi Divisi, Rotasi Jabatan, dll"></textarea>
                                </div>

                                {{-- Is Active Checkbox (Only for edit mode) --}}
                                <div x-show="modalMode === 'edit'" class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-xl">
                                    <input type="checkbox" name="is_active" id="is_active" 
                                        x-model="isActive"
                                        class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <label for="is_active" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                            Status Aktif
                                        </label>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Centang jika ini adalah posisi/jabatan yang sedang aktif. Sistem akan otomatis menonaktifkan riwayat karir lain yang masih aktif.
                                        </p>
                                    </div>
                                </div>

                                {{-- Info Box --}}
                                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-blue-900 mb-1">Informasi Penting</h4>
                                            <ul class="text-xs text-blue-700 space-y-1">
                                                <li>• Tanggal Mulai adalah tanggal efektif berlakunya SK/Surat Keputusan</li>
                                                <li>• Hanya boleh ada satu riwayat karir yang aktif pada satu waktu</li>
                                                <li>• Manager akan menjadi approver untuk workflow approval (cuti, lembur, dll)</li>
                                                <li>• Riwayat karir digunakan untuk tracking promosi dan perubahan posisi</li>
                                            </ul>
                                        </div>
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
            let careerManagerInstance = null;

            function careerManager() {
                return {
                    showModal: false,
                    modalMode: 'create',
                    editCareerId: null,
                    departmentId: '',
                    positionId: '',
                    levelId: '',
                    branchId: '',
                    managerId: '',
                    startDate: '',
                    endDate: '',
                    isActive: true,
                    notes: '',
                    formAction: '',
                    formMethod: 'POST',

                    init() {
                        careerManagerInstance = this;
                        this.updateFormAction();
                    },

                    updateFormAction() {
                        const employeeId = {{ $employee->id }};
                        
                        if (this.modalMode === 'create') {
                            this.formAction = '{{ route("hris.employees.careers.store", $employee->id) }}';
                            this.formMethod = 'POST';
                        } else if (this.modalMode === 'edit') {
                            this.formAction = '{{ route("hris.employees.careers.update", [$employee->id, "__ID__"]) }}'.replace('__ID__', this.editCareerId);
                            this.formMethod = 'PUT';
                        }
                        
                        console.log('Career Form Action:', this.formAction);
                        console.log('Career Form Method:', this.formMethod);
                    },

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.resetForm();
                        this.updateFormAction();
                        this.showModal = true;
                    },

                    openEditModal(id, deptId, posId, levelId, branchId, managerId, startDate, endDate, isActive, notes) {
                        this.modalMode = 'edit';
                        this.editCareerId = id;
                        this.departmentId = deptId;
                        this.positionId = posId;
                        this.levelId = levelId;
                        this.branchId = branchId || '';
                        this.managerId = managerId || '';
                        this.startDate = startDate;
                        this.endDate = endDate || '';
                        this.isActive = isActive;
                        this.notes = notes || '';
                        this.updateFormAction();
                        this.showModal = true;
                    },

                    closeModal() {
                        this.showModal = false;
                        setTimeout(() => {
                            this.resetForm();
                        }, 300);
                    },

                    resetForm() {
                        this.modalMode = 'create';
                        this.editCareerId = null;
                        this.departmentId = '';
                        this.positionId = '';
                        this.levelId = '';
                        this.branchId = '';
                        this.managerId = '';
                        this.startDate = '';
                        this.endDate = '';
                        this.isActive = true;
                        this.notes = '';
                        this.formAction = '';
                        this.formMethod = 'POST';
                    },

                    async loadPositions(departmentId) {
                        if (!departmentId) {
                            document.getElementById('position_id').innerHTML = '<option value="">Pilih Posisi</option>';
                            return;
                        }

                        try {
                            const response = await fetch(`/hris/employees/api/positions-by-department/${departmentId}`);
                            const data = await response.json();
                            
                            if (data.success) {
                                let options = '<option value="">Pilih Posisi</option>';
                                data.data.forEach(position => {
                                    options += `<option value="${position.id}">${position.name}</option>`;
                                });
                                
                                document.getElementById('position_id').innerHTML = options;
                                
                                if (this.modalMode === 'create') {
                                    this.positionId = '';
                                }
                            }
                        } catch (error) {
                            console.error('Error loading positions:', error);
                        }
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                @if($errors->any())
                    if (careerManagerInstance) {
                        careerManagerInstance.openCreateModal();
                        
                        setTimeout(() => {
                            careerManagerInstance.departmentId = '{{ old('department_id') }}';
                            careerManagerInstance.positionId = '{{ old('position_id') }}';
                            careerManagerInstance.levelId = '{{ old('level_id') }}';
                            careerManagerInstance.branchId = '{{ old('branch_id') }}';
                            careerManagerInstance.managerId = '{{ old('manager_id') }}';
                            careerManagerInstance.startDate = '{{ old('start_date') }}';
                            careerManagerInstance.endDate = '{{ old('end_date') }}';
                            careerManagerInstance.isActive = {{ old('is_active') ? 'true' : 'false' }};
                            careerManagerInstance.notes = `{{ old('notes') }}`;
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