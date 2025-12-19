@extends('layouts.admin')

@section('title', 'Position Management')

@section('content')
<div class="space-y-6" x-data="positionManager()">
    {{-- Header Section --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manajemen Posisi Jabatan</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola posisi jabatan karyawan berdasarkan departemen dan deskripsi
                pekerjaan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="#"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Ekspor Data
            </a>
            <button @click="openCreateModal()" type="button"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Posisi
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Total Positions --}}
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-indigo-100">Total Posisi</p>
                    <h3 class="text-3xl font-bold mt-2">{{ $positions->count() }}</h3>
                </div>
                <div class="bg-white/20 rounded-xl p-3">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- By Department --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Departemen Aktif</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $departmentCount ?? 0 }}</h3>
                </div>
                <div class="bg-purple-50 rounded-xl p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- With Job Description --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ada Job Desc</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $withJobDesc ?? 0 }}</h3>
                </div>
                <div class="bg-green-50 rounded-xl p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Recently Added --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Baru Bulan Ini</p>
                    <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $recentlyAdded ?? 0 }}</h3>
                </div>
                <div class="bg-blue-50 rounded-xl p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="tableFilters()">
        {{-- Search and Filter Bar --}}
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                {{-- Search Input --}}
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-model="searchQuery" @input="filterTable()"
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Cari nama posisi...">
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    {{-- Department Filter --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                            <span x-text="departmentFilterLabel"></span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-64 max-h-80 overflow-y-auto rounded-xl bg-white shadow-lg border border-gray-200 z-10">
                            <div class="py-1">
                                <button @click="departmentFilter = 'all'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="departmentFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Semua Departemen
                                </button>
                                @foreach($departments as $dept)
                                    <button @click="departmentFilter = '{{ $dept->id }}'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="departmentFilter === '{{ $dept->id }}' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        {{ $dept->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500 font-medium hidden lg:inline">SORT BY:</span>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-1 px-3 py-2 hover:bg-gray-50 text-gray-700 font-medium rounded-lg transition-colors">
                                <span x-text="sortOptions[sortBy]"></span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button @click="sortBy = 'name-asc'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'name-asc' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Nama (A-Z)
                                    </button>
                                    <button @click="sortBy = 'name-desc'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'name-desc' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Nama (Z-A)
                                    </button>
                                    <button @click="sortBy = 'newest'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'newest' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Terbaru
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                            <input type="checkbox" @change="toggleAllCheckboxes($event)"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                            Posisi</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                            Departemen</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                            Job Description</th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="positionsTable">
                    @forelse($positions as $position)
                        <tr class="hover:bg-gray-50/50 transition-colors group position-row"
                            data-position-name="{{ strtolower($position->name) }}"
                            data-department-id="{{ $position->department_id }}"
                            data-position-date="{{ $position->created_at ? $position->created_at->timestamp : 0 }}">
                            <td class="px-6 py-4">
                                <input type="checkbox"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 row-checkbox">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr($position->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $position->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $position->created_at ? $position->created_at->diffForHumans() : 'Baru dibuat' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($position->department)
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $position->department->name }}
                                            </div>
                                            <code class="text-xs text-gray-500">{{ $position->department->code }}</code>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak ada departemen</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($position->job_description)
                                    <div class="max-w-md">
                                        <p class="text-sm text-gray-700 line-clamp-2">
                                            {{ Str::limit($position->job_description, 100) }}
                                        </p>
                                        @if(strlen($position->job_description) > 100)
                                            <button
                                                @click="showJobDescription('{{ addslashes($position->name) }}', '{{ addslashes($position->job_description) }}')"
                                                class="text-xs text-indigo-600 hover:text-indigo-700 font-medium mt-1">
                                                Lihat selengkapnya →
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum ada deskripsi</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="openEditModal({{ $position->id }}, '{{ addslashes($position->name) }}', {{ $position->department_id ?? 'null' }}, '{{ addslashes($position->job_description ?? '') }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <form action="{{ route('master-data.hris.positions.destroy', $position->id) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus posisi ini?');">
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
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 text-sm font-medium mb-1">Belum ada posisi</p>
                                    <p class="text-gray-400 text-sm mb-4">Mulai dengan membuat posisi pertama</p>
                                    <button @click="openCreateModal()"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Tambah Posisi
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- No Results State --}}
            <div x-show="noResults" x-cloak class="px-6 py-16 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Tidak ada hasil ditemukan</p>
                    <p class="text-gray-400 text-sm mb-4">Coba ubah kata kunci pencarian atau filter Anda</p>
                    <button @click="resetFilters()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        {{-- Pagination --}}
        @if ($positions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $positions->links() }}
            </div>
        @endif
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
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                    <form :action="modalMode === 'create' ? '{{ route('master-data.hris.positions.store') }}' : '{{ url('master-data.hris.positions') }}/' + editPositionId" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="modalMode === 'edit' ? 'PUT' : 'POST'">

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white" id="modal-title" 
                                            x-text="modalMode === 'create' ? 'Tambah Posisi Baru' : 'Edit Posisi'"></h3>
                                        <p class="text-sm text-indigo-100" 
                                            x-text="modalMode === 'create' ? 'Buat posisi jabatan baru' : 'Perbarui informasi posisi'"></p>
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
                                {{-- Position Name --}}
                                <div>
                                    <label for="position_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nama Posisi <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="position_name" required x-model="positionName"
                                        value="{{ old('name') }}"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('name') border-red-500 @enderror"
                                        placeholder="Contoh: Software Engineer, Sales Manager">
                                    @error('name')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Department --}}
                                <div>
                                    <label for="department_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Departemen <span class="text-red-500">*</span>
                                    </label>
                                    <select name="department_id" id="department_id" required x-model="departmentId"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('department_id') border-red-500 @enderror">
                                        <option value="">Pilih Departemen</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                                {{ $dept->name }} ({{ $dept->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Job Description --}}
                                <div>
                                    <label for="job_description" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Deskripsi Pekerjaan <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                                    </label>
                                    <textarea name="job_description" id="job_description" rows="6" x-model="jobDescription"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('job_description') border-red-500 @enderror"
                                        placeholder="Tulis deskripsi pekerjaan, tanggung jawab, dan kualifikasi yang dibutuhkan...">{{ old('job_description') }}</textarea>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-xs text-gray-500">Jelaskan tugas dan tanggung jawab posisi ini</p>
                                        <span class="text-xs text-gray-400" x-text="jobDescription.length + ' karakter'"></span>
                                    </div>
                                    @error('job_description')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
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
                                        <h4 class="text-sm font-semibold text-blue-900 mb-1">Tips</h4>
                                        <ul class="text-xs text-blue-700 space-y-1">
                                            <li>• Gunakan nama posisi yang jelas dan mudah dipahami</li>
                                            <li>• Pilih departemen yang sesuai dengan fungsi posisi</li>
                                            <li>• Job description membantu kandidat memahami peran mereka</li>
                                        </ul>
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
                                    <span x-text="modalMode === 'create' ? 'Simpan Posisi' : 'Update Posisi'"></span>
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Job Description Modal --}}
        <div x-show="showJobDescModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="job-desc-modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="showJobDescModal = false">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showJobDescModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showJobDescModal = false">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showJobDescModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    {{-- Job Desc Modal Header --}}
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
                                    <h3 class="text-lg font-bold text-white" id="job-desc-modal-title" x-text="jobDescTitle"></h3>
                                    <p class="text-sm text-indigo-100">Deskripsi Pekerjaan</p>
                                </div>
                            </div>
                            <button @click="showJobDescModal = false" type="button"
                                class="text-white/80 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Job Desc Modal Body --}}
                    <div class="px-6 py-6">
                        <div class="prose prose-sm max-w-none">
                            <div class="whitespace-pre-wrap text-gray-700 text-sm leading-relaxed" x-text="jobDescContent"></div>
                        </div>
                    </div>

                    {{-- Job Desc Modal Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end border-t border-gray-200">
                        <button @click="showJobDescModal = false" type="button"
                            class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                            Tutup
                        </button>
                    </div>

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
            let positionManagerInstance = null;

            function positionManager() {
                return {
                    showModal: false,
                    showJobDescModal: false,
                    modalMode: 'create',
                    editPositionId: null,
                    positionName: '',
                    departmentId: '',
                    jobDescription: '',
                    jobDescTitle: '',
                    jobDescContent: '',

                    init() {
                        positionManagerInstance = this;
                    },

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.positionName = '';
                        this.departmentId = '';
                        this.jobDescription = '';
                        this.showModal = true;
                    },

                    openEditModal(id, name, departmentId, jobDesc) {
                        this.modalMode = 'edit';
                        this.editPositionId = id;
                        this.positionName = name;
                        this.departmentId = departmentId || '';
                        this.jobDescription = jobDesc || '';
                        this.showModal = true;
                    },

                    closeModal() {
                        this.showModal = false;
                        setTimeout(() => {
                            this.modalMode = 'create';
                            this.editPositionId = null;
                            this.positionName = '';
                            this.departmentId = '';
                            this.jobDescription = '';
                        }, 300);
                    },

                    showJobDescription(title, content) {
                        this.jobDescTitle = title;
                        this.jobDescContent = content;
                        this.showJobDescModal = true;
                    }
                }
            }

            // Auto open modal if there are validation errors
            document.addEventListener('DOMContentLoaded', function() {
                @if($errors->any() && old('_method'))
                    // Edit mode
                    if (positionManagerInstance) {
                        positionManagerInstance.openEditModal(
                            {{ old('id') ?? 0 }},
                            '{{ old('name') }}',
                            {{ old('department_id') ?? 'null' }},
                            '{{ old('job_description') }}'
                        );
                    }
                @elseif($errors->any())
                    // Create mode
                    if (positionManagerInstance) {
                        positionManagerInstance.openCreateModal();
                        
                        setTimeout(() => {
                            positionManagerInstance.positionName = '{{ old('name') }}';
                            positionManagerInstance.departmentId = '{{ old('department_id') }}';
                            positionManagerInstance.jobDescription = '{{ old('job_description') }}';
                        }, 100);
                    }
                @endif
            });

            function tableFilters() {
                return {
                    searchQuery: '',
                    departmentFilter: 'all',
                    sortBy: 'name-asc',
                    noResults: false,
                    sortOptions: {
                        'name-asc': 'Nama (A-Z)',
                        'name-desc': 'Nama (Z-A)',
                        'newest': 'Terbaru'
                    },

                    get departmentFilterLabel() {
                        if (this.departmentFilter === 'all') {
                            return 'Semua Departemen';
                        }
                        const select = document.querySelector(`button[onclick*="departmentFilter = '${this.departmentFilter}'"]`);
                        return select ? select.textContent.trim() : 'Filter Departemen';
                    },

                    filterTable() {
                        const rows = document.querySelectorAll('.position-row');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const name = row.dataset.positionName || '';
                            const deptId = row.dataset.departmentId || '';
                            const searchTerm = this.searchQuery.toLowerCase();

                            const matchesSearch = !searchTerm || name.includes(searchTerm);
                            const matchesDepartment = this.departmentFilter === 'all' || deptId === this.departmentFilter;

                            if (matchesSearch && matchesDepartment) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        this.noResults = visibleCount === 0;
                        
                        const emptyState = document.getElementById('emptyState');
                        if (emptyState && (this.searchQuery || this.departmentFilter !== 'all')) {
                            emptyState.style.display = 'none';
                        }

                        this.sortTable();
                    },

                    sortTable() {
                        const tbody = document.getElementById('positionsTable');
                        if (!tbody) return;
                        
                        const rows = Array.from(tbody.querySelectorAll('.position-row'));
                        
                        rows.sort((a, b) => {
                            switch(this.sortBy) {
                                case 'newest':
                                    return parseInt(b.dataset.positionDate || 0) - parseInt(a.dataset.positionDate || 0);
                                case 'name-asc':
                                    return (a.dataset.positionName || '').localeCompare(b.dataset.positionName || '');
                                case 'name-desc':
                                    return (b.dataset.positionName || '').localeCompare(a.dataset.positionName || '');
                                default:
                                    return 0;
                            }
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    },

                    resetFilters() {
                        this.searchQuery = '';
                        this.departmentFilter = 'all';
                        this.sortBy = 'name-asc';
                        this.filterTable();
                    },

                    toggleAllCheckboxes(event) {
                        const checkboxes = document.querySelectorAll('.row-checkbox');
                        checkboxes.forEach(checkbox => {
                            const row = checkbox.closest('.position-row');
                            if (row && row.style.display !== 'none') {
                                checkbox.checked = event.target.checked;
                            }
                        });
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