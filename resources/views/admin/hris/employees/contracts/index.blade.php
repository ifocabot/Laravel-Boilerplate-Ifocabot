@extends('layouts.admin')

@section('title', 'Kontrak Kerja - ' . $employee->full_name)

@section('content')
<div class="space-y-6" x-data="contractManager()">
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
                <h1 class="text-2xl font-bold text-gray-900">Kontrak Kerja</h1>
            </div>
            <p class="text-sm text-gray-500 ml-11">Kelola kontrak kerja untuk:
                <strong>{{ $employee->full_name }}</strong></p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCreateModal()" type="button"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kontrak
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-indigo-100">Total Kontrak</p>
                    <h3 class="text-3xl font-bold mt-2">{{ $totalContracts }}</h3>
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
                    <p class="text-sm font-medium text-gray-500">Kontrak Aktif</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $activeContracts }}</h3>
                </div>
                <div class="bg-green-50 rounded-xl p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Akan Berakhir (30 Hari)</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $expiringContracts }}</h3>
                </div>
                <div class="bg-yellow-50 rounded-xl p-3">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

{{-- Contract Timeline --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Riwayat Kontrak</h2>
            </div>

            <div class="p-6">
                @if($employee->contracts->count() > 0)
                    <div class="space-y-6">
                        @foreach($employee->contracts as $index => $contract)
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-700 border-green-200',
                                    'expiring_soon' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'expired' => 'bg-red-100 text-red-700 border-red-200',
                                    'permanent' => 'bg-blue-100 text-blue-700 border-blue-200',
                                ];
                                
                                $typeColors = [
                                    'pkwt' => 'bg-yellow-50 text-yellow-700',
                                    'pkwtt' => 'bg-green-50 text-green-700',
                                    'internship' => 'bg-blue-50 text-blue-700',
                                    'probation' => 'bg-purple-50 text-purple-700',
                                ];
                            @endphp

                            <div class="relative">
                                {{-- Timeline Line --}}
                                @if(!$loop->last)
                                    <div class="absolute left-6 top-16 bottom-0 w-0.5 bg-gray-200"></div>
                                @endif

                                {{-- Contract Card --}}
                                <div class="flex gap-4 group">
                                    {{-- Timeline Dot --}}
                                    <div class="relative flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $contract->is_active ? 'bg-gradient-to-br from-green-500 to-green-600 ring-4 ring-green-100' : 'bg-gradient-to-br from-gray-400 to-gray-500' }} shadow-lg">
                                            @if($contract->is_active)
                                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @else
                                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Contract Details Card --}}
                                    <div class="flex-1 bg-gray-50 rounded-xl border border-gray-200 p-5 hover:border-indigo-300 transition-colors">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1">
                                                {{-- Header --}}
                                                <div class="flex items-start justify-between mb-3">
                                                    <div>
                                                        <div class="flex items-center gap-2 mb-2">
                                                            <h3 class="text-lg font-bold text-gray-900">{{ $contract->type_label }}</h3>
                                                            @if($contract->is_active)
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full border {{ $statusColors[$contract->status] ?? 'border-gray-200' }}">
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                                                    Aktif
                                                                </span>
                                                            @endif
                                                            
                                                            @if($contract->status === 'expiring_soon')
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full border border-yellow-200">
                                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                    </svg>
                                                                    Akan Berakhir
                                                                </span>
                                                            @endif
                                                        </div>
                                                        @if($contract->contract_number)
                                                            <p class="text-sm text-gray-600">No. Kontrak: <span class="font-medium">{{ $contract->contract_number }}</span></p>
                                                        @endif
                                                    </div>

                                                    {{-- Period --}}
                                                    <div class="text-right">
                                                        <div class="text-sm font-semibold text-gray-900">
                                                            {{ $contract->start_date->format('d M Y') }}
                                                            @if($contract->end_date)
                                                                - {{ $contract->end_date->format('d M Y') }}
                                                            @else
                                                                - Permanen
                                                            @endif
                                                        </div>
                                                        @if($contract->days_remaining !== null && $contract->days_remaining >= 0)
                                                            <div class="text-xs text-gray-500 mt-1">
                                                                {{ $contract->days_remaining }} hari lagi
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Details --}}
                                                <div class="flex flex-wrap gap-4 mb-3">
                                                    {{-- Contract Type Badge --}}
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-lg {{ $typeColors[$contract->type] ?? 'bg-gray-50' }} flex items-center justify-center">
                                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-xs text-gray-500">Tipe</p>
                                                            <p class="text-sm font-semibold text-gray-900">{{ $contract->type_label }}</p>
                                                        </div>
                                                    </div>

                                                    {{-- Duration --}}
                                                    @if($contract->end_date)
                                                        @php
                                                            $duration = $contract->start_date->diffForHumans($contract->end_date, true);
                                                        @endphp
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-gray-500">Durasi</p>
                                                                <p class="text-sm font-semibold text-gray-900">{{ $duration }}</p>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Document --}}
                                                    @if($contract->document_path)
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                                                                <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-xs text-gray-500">Dokumen</p>
                                                                <a href="{{ route('hris.employees.contracts.download', [$employee->id, $contract->id]) }}" 
                                                                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                                                    Download PDF
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Notes --}}
                                                @if($contract->notes)
                                                    <div class="p-3 bg-white rounded-lg border border-gray-200">
                                                        <p class="text-xs text-gray-500 mb-1">Catatan:</p>
                                                        <p class="text-sm text-gray-700">{{ $contract->notes }}</p>
                                                    </div>
                                                @endif

                                                {{-- Expiring Warning --}}
                                                @if($contract->status === 'expiring_soon' && $contract->is_active)
                                                    <div class="mt-3 flex items-center gap-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                        </svg>
                                                        <div class="flex-1">
                                                            <p class="text-sm font-semibold text-yellow-900">Kontrak akan berakhir dalam {{ $contract->days_remaining }} hari</p>
                                                            <p class="text-xs text-yellow-700 mt-0.5">Segera lakukan perpanjangan kontrak jika diperlukan</p>
                                                        </div>
                                                        <button @click="openRenewModal({{ $contract->id }}, '{{ $contract->type }}', '{{ $contract->contract_number }}')"
                                                            class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                            Perpanjang
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Actions --}}
                                            <div class="flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                @if($contract->is_active)
                                                    <button @click="openRenewModal({{ $contract->id }}, '{{ $contract->type }}', '{{ $contract->contract_number }}')"
                                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                                        title="Perpanjang">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                    </button>
                                                @endif

                                                <button @click="openEditModal(
                                                    {{ $contract->id }},
                                                    '{{ addslashes($contract->contract_number ?? '') }}',
                                                    '{{ $contract->type }}',
                                                    '{{ $contract->start_date->format('Y-m-d') }}',
                                                    '{{ $contract->end_date ? $contract->end_date->format('Y-m-d') : '' }}',
                                                    {{ $contract->is_active ? 'true' : 'false' }},
                                                    '{{ addslashes($contract->notes ?? '') }}'
                                                )"
                                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                    title="Edit">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>

                                            @if(!$contract->is_active)
                                                <form action="{{ route('hris.employees.contracts.destroy', [$employee->id, $contract->id]) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kontrak ini?');">
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
                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada kontrak</p>
                        <p class="text-gray-400 text-sm mb-4">Mulai dengan menambahkan kontrak pertama</p>
                        <button @click="openCreateModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Kontrak
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
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form :action="getFormAction()" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="_method" :value="getFormMethod()">

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
                                            x-text="modalMode === 'create' ? 'Tambah Kontrak' : (modalMode === 'renew' ? 'Perpanjang Kontrak' : 'Edit Kontrak')"></h3>
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
                                {{-- Contract Number --}}
                                <div>
                                    <label for="contract_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nomor Kontrak
                                    </label>
                                    <input type="text" name="contract_number" id="contract_number" x-model="contractNumber"
                                        value="{{ old('contract_number') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('contract_number') border-red-500 @enderror"
                                        placeholder="Nomor kontrak (opsional)">
                                    @error('contract_number')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Contract Type --}}
                                <div>
                                    <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Tipe Kontrak <span class="text-red-500">*</span>
                                    </label>
                                    <select name="type" id="type" required x-model="contractType"
                                        @change="handleContractTypeChange()"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('type') border-red-500 @enderror">
                                        <option value="">Pilih Tipe Kontrak</option>
                                        <option value="pkwt" {{ old('type') == 'pkwt' ? 'selected' : '' }}>PKWT (Kontrak)</option>
                                        <option value="pkwtt" {{ old('type') == 'pkwtt' ? 'selected' : '' }}>PKWTT (Tetap)</option>
                                        <option value="internship" {{ old('type') == 'internship' ? 'selected' : '' }}>Magang</option>
                                        <option value="probation" {{ old('type') == 'probation' ? 'selected' : '' }}>Probation</option>
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Start & End Date --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Tanggal Mulai <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="start_date" id="start_date" required x-model="startDate"
                                            value="{{ old('start_date') }}"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('start_date') border-red-500 @enderror">
                                        @error('start_date')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Tanggal Akhir
                                            <span x-show="contractType === 'pkwt'" class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="end_date" id="end_date" x-model="endDate"
                                            value="{{ old('end_date') }}"
                                            :required="contractType === 'pkwt'"
                                            :disabled="contractType === 'pkwtt'"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all disabled:bg-gray-100 disabled:text-gray-400 @error('end_date') border-red-500 @enderror">
                                        <p class="mt-1.5 text-xs text-gray-500" x-show="contractType === 'pkwtt'">
                                            Kontrak PKWTT tidak memiliki tanggal akhir
                                        </p>
                                        @error('end_date')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Document Upload --}}
                                <div>
                                    <label for="document" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Upload Dokumen Kontrak (PDF)
                                    </label>
                                    <input type="file" name="document" id="document" accept=".pdf"
                                        @change="handleFileChange($event)"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('document') border-red-500 @enderror">
                                    <p class="mt-1.5 text-xs text-gray-500">Maksimal 5MB, format PDF</p>
                                    
                                    <div x-show="fileName" class="mt-2 flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-sm text-blue-700" x-text="fileName"></span>
                                    </div>
                                    
                                    @error('document')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Catatan
                                    </label>
                                    <textarea name="notes" id="notes" rows="3" x-model="notes"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('notes') border-red-500 @enderror"
                                        placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Is Active Checkbox (Only for edit mode) --}}
                                <div x-show="modalMode === 'edit'" class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-xl">
                                    <input type="checkbox" name="is_active" id="is_active" 
                                        x-model="isActive"
                                        {{ old('is_active') ? 'checked' : '' }}
                                        class="w-5 h-5 mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <label for="is_active" class="text-sm font-semibold text-gray-900 cursor-pointer">
                                            Status Aktif
                                        </label>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Centang jika ini adalah kontrak yang sedang aktif. Sistem akan otomatis menonaktifkan kontrak lain.
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
                                            <h4 class="text-sm font-semibold text-blue-900 mb-1">Informasi Kontrak</h4>
                                            <ul class="text-xs text-blue-700 space-y-1">
                                                <li>• <strong>PKWT:</strong> Kontrak waktu tertentu, harus memiliki tanggal akhir</li>
                                                <li>• <strong>PKWTT:</strong> Kontrak tetap, tidak ada tanggal akhir</li>
                                                <li>• <strong>Internship:</strong> Kontrak magang/praktik kerja</li>
                                                <li>• <strong>Probation:</strong> Masa percobaan karyawan baru</li>
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
                                    <span x-text="modalMode === 'create' ? 'Simpan Kontrak' : (modalMode === 'renew' ? 'Perpanjang Kontrak' : 'Update Kontrak')"></span>
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
        let contractManagerInstance = null;

        function contractManager() {
            return {
                showModal: false,
                modalMode: 'create', // 'create', 'edit', 'renew'
                editContractId: null,
                contractNumber: '',
                contractType: '',
                startDate: '',
                endDate: '',
                isActive: true,
                notes: '',
                fileName: '',

                init() {
                    contractManagerInstance = this;
                },

                // GET FORM ACTION - Sesuaikan dengan route structure
                getFormAction() {
                    const employeeId = {{ $employee->id }};
                    
                    if (this.modalMode === 'create') {
                        return '{{ route("hris.employees.contracts.store", $employee->id) }}';
                    } else if (this.modalMode === 'renew') {
                        // Route: POST /employees/{employee_id}/contracts/{id}/renew
                        return `/hris/employees/${employeeId}/contracts/${this.editContractId}/renew`;
                    } else {
                        // Route: PUT /employees/{employee_id}/contracts/{id}/update
                        return `/hris/employees/${employeeId}/contracts/${this.editContractId}/update`;
                    }
                },

                // GET FORM METHOD
                getFormMethod() {
                    return this.modalMode === 'edit' ? 'PUT' : 'POST';
                },

                openCreateModal() {
                    this.modalMode = 'create';
                    this.resetForm();
                    this.showModal = true;
                },

                openEditModal(id, number, type, startDate, endDate, isActive, notes) {
                    this.modalMode = 'edit';
                    this.editContractId = id;
                    this.contractNumber = number;
                    this.contractType = type;
                    this.startDate = startDate;
                    this.endDate = endDate;
                    this.isActive = isActive;
                    this.notes = notes;
                    this.fileName = '';
                    this.showModal = true;
                },

                openRenewModal(id, type, number) {
                    this.modalMode = 'renew';
                    this.editContractId = id;
                    this.contractType = type;
                    this.contractNumber = number || '';
                    this.startDate = '';
                    this.endDate = '';
                    this.isActive = true;
                    this.notes = 'Perpanjangan kontrak';
                    this.fileName = '';
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
                    this.editContractId = null;
                    this.contractNumber = '';
                    this.contractType = '';
                    this.startDate = '';
                    this.endDate = '';
                    this.isActive = true;
                    this.notes = '';
                    this.fileName = '';
                },

                handleContractTypeChange() {
                    if (this.contractType === 'pkwtt') {
                        this.endDate = '';
                    }
                },

                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.fileName = file.name;
                    } else {
                        this.fileName = '';
                    }
                }
            }
        }

        // Auto open modal if there are validation errors
        document.addEventListener('DOMContentLoaded', function() {
            @if($errors->any())
                if (contractManagerInstance) {
                    contractManagerInstance.openCreateModal();
                    
                    setTimeout(() => {
                        contractManagerInstance.contractNumber = '{{ old('contract_number') }}';
                        contractManagerInstance.contractType = '{{ old('type') }}';
                        contractManagerInstance.startDate = '{{ old('start_date') }}';
                        contractManagerInstance.endDate = '{{ old('end_date') }}';
                        contractManagerInstance.isActive = {{ old('is_active') ? 'true' : 'false' }};
                        contractManagerInstance.notes = '{{ old('notes') }}';
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