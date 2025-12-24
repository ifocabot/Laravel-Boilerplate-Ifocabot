@extends('layouts.admin')

@section('title', 'Employee Management')

@section('content')
    <div class="space-y-6" x-data="employeeManager()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Karyawan</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola data karyawan, posisi, dan informasi kepegawaian.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hris.employees.export') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ekspor Data
                </a>
                <a href="{{ route('hris.employees.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Karyawan
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Employees --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Karyawan</p>
                        <h3 class="text-3xl font-bold mt-2">{{ $totalEmployees }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Employees --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Karyawan Aktif</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $activeEmployees }}</h3>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Resigned Employees --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Resign/Terminate</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $resignedEmployees }}</h3>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- New This Month --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Baru Bulan Ini</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $newThisMonth }}</h3>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
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
                            placeholder="Cari nama, NIK, atau email...">
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Department Filter --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
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
                                class="absolute left-0 mt-2 w-64 max-h-80 overflow-y-auto rounded-xl bg-white shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button @click="departmentFilter = 'all'; applyFilter(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="departmentFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Semua Departemen
                                    </button>
                                    @foreach($departments as $dept)
                                        <button @click="departmentFilter = '{{ $dept->id }}'; applyFilter(); open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                            :class="departmentFilter === '{{ $dept->id }}' ? 'bg-indigo-50 text-indigo-700' : ''">
                                            {{ $dept->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Status Filter --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                <span x-text="statusFilterLabel"></span>
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
                                class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button @click="statusFilter = 'all'; applyFilter(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="statusFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Semua Status
                                    </button>
                                    <button @click="statusFilter = 'active'; applyFilter(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="statusFilter === 'active' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Aktif
                                    </button>
                                    <button @click="statusFilter = 'resigned'; applyFilter(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="statusFilter === 'resigned' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Resign
                                    </button>
                                    <button @click="statusFilter = 'terminated'; applyFilter(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="statusFilter === 'terminated' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Terminate
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Sort --}}
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-500 font-medium hidden lg:inline">SORT:</span>
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
                                        <button @click="sortBy = 'nik-asc'; sortTable(); open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                            :class="sortBy === 'nik-asc' ? 'bg-indigo-50 text-indigo-700' : ''">
                                            NIK (A-Z)
                                        </button>
                                        <button @click="sortBy = 'newest'; sortTable(); open = false"
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                            :class="sortBy === 'newest' ? 'bg-indigo-50 text-indigo-700' : ''">
                                            Terbaru Join
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
                                Karyawan</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Departemen & Posisi</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Level</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Lokasi</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Status</th>
                            <th
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="employeesTable">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-50/50 transition-colors group employee-row"
                                data-employee-name="{{ strtolower($employee->full_name) }}"
                                data-employee-nik="{{ strtolower($employee->nik) }}"
                                data-employee-email="{{ strtolower($employee->email_corporate ?? '') }}"
                                data-department-id="{{ $employee->current_career && $employee->current_career->department ? $employee->current_career->department->id : '' }}"
                                data-status="{{ $employee->status }}"
                                data-join-date="{{ $employee->join_date ? $employee->join_date->timestamp : 0 }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 row-checkbox">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                            {{ $employee->initials }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $employee->full_name }}</div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <code
                                                    class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $employee->nik }}</code>
                                                @if($employee->email_corporate)
                                                    <span class="text-xs text-gray-500">{{ $employee->email_corporate }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($employee->current_career)
                                        <div class="space-y-1">
                                            @if($employee->current_career->department)
                                                <div class="flex items-center gap-2 text-sm text-gray-700">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                    </svg>
                                                    <span class="font-medium">{{ $employee->current_career->department->name }}</span>
                                                </div>
                                            @endif
                                            @if($employee->current_career->position)
                                                <div class="text-xs text-gray-500 pl-6">
                                                    {{ $employee->current_career->position->name }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($employee->current_career && $employee->current_career->level)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 text-purple-700 text-xs font-semibold rounded-full">
                                            {{ $employee->current_career->level->grade_code }} -
                                            {{ $employee->current_career->level->name }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($employee->current_career && $employee->current_career->branch)
                                        <div class="flex items-center gap-2 text-sm text-gray-700">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            <span>{{ $employee->current_career->branch->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-green-50 text-green-700 border-green-100',
                                            'resigned' => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                            'terminated' => 'bg-red-50 text-red-700 border-red-100',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusColors[$employee->status] ?? 'bg-gray-50 text-gray-700 border-gray-100' }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $employee->status === 'active' ? 'bg-green-500' : ($employee->status === 'resigned' ? 'bg-yellow-500' : 'bg-red-500') }}"></span>
                                        {{ $employee->status_label }}
                                    </span>
                                    @if($employee->join_date)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Join: {{ $employee->join_date->format('d M Y') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('hris.employees.show', $employee->id) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('hris.employees.edit', $employee->id) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('hris.employees.destroy', $employee->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');">
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
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada karyawan</p>
                                        <p class="text-gray-400 text-sm mb-4">Mulai dengan menambahkan karyawan pertama</p>
                                        <a href="{{ route('hris.employees.create') }}"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            Tambah Karyawan
                                        </a>
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
            @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $employees->links() }}
                </div>
            @endif
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
                                                                                @if(session('info'))
                                                                                    this.notify('{{ session('info') }}', 'info');
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
                                                                        }" x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            class="fixed bottom-4 right-4 z-50 flex items-center w-full max-w-xs p-4 mb-4 text-gray-500 bg-white rounded-xl shadow-lg border border-gray-100"
            role="alert">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg" :class="{
                                                                                'text-green-500 bg-green-100': type === 'success',
                                                                                'text-red-500 bg-red-100': type === 'error',
                                                                                'text-blue-500 bg-blue-100': type === 'info'
                                                                            }">
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
                <template x-if="type === 'info'">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
            function employeeManager() {
                return {
                    init() {
                        // Any initialization logic
                    }
                }
            }

            function tableFilters() {
                return {
                    // ⭐ Initialize from server-provided values (from URL query params)
                    searchQuery: '{{ $currentSearch ?? '' }}',
                    departmentFilter: '{{ $currentDepartment ?? '' }}' || 'all',
                    statusFilter: '{{ $currentStatus ?? '' }}' || 'all',
                    sortBy: 'name-asc',
                    noResults: false,
                    sortOptions: {
                        'name-asc': 'Nama (A-Z)',
                        'name-desc': 'Nama (Z-A)',
                        'nik-asc': 'NIK (A-Z)',
                        'newest': 'Terbaru Join'
                    },

                    // ⭐ Apply filter via URL (server-side)
                    applyFilter() {
                        const params = new URLSearchParams();

                        if (this.statusFilter && this.statusFilter !== 'all') {
                            params.set('status', this.statusFilter);
                        }
                        if (this.searchQuery) {
                            params.set('search', this.searchQuery);
                        }
                        if (this.departmentFilter && this.departmentFilter !== 'all') {
                            params.set('department_id', this.departmentFilter);
                        }

                        const queryString = params.toString();
                        window.location.href = '{{ route("hris.employees.index") }}' + (queryString ? '?' + queryString : '');
                    },

                    get departmentFilterLabel() {
                        if (this.departmentFilter === 'all' || !this.departmentFilter) {
                            return 'Semua Departemen';
                        }
                        // Get label from button text
                        const btn = document.querySelector(`button[onclick*="departmentFilter = '${this.departmentFilter}'"]`);
                        return btn ? btn.textContent.trim() : 'Filter Departemen';
                    },

                    get statusFilterLabel() {
                        const labels = {
                            'all': 'Semua Status',
                            '': 'Semua Status',
                            'active': 'Aktif',
                            'resigned': 'Resign',
                            'terminated': 'Terminate'
                        };
                        return labels[this.statusFilter] || 'Semua Status';
                    },

                    filterTable() {
                        const rows = document.querySelectorAll('.employee-row');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const name = row.dataset.employeeName || '';
                            const nik = row.dataset.employeeNik || '';
                            const email = row.dataset.employeeEmail || '';
                            const deptId = row.dataset.departmentId || '';
                            const status = row.dataset.status || '';
                            const searchTerm = this.searchQuery.toLowerCase();

                            const matchesSearch = !searchTerm ||
                                name.includes(searchTerm) ||
                                nik.includes(searchTerm) ||
                                email.includes(searchTerm);

                            const matchesDepartment = this.departmentFilter === 'all' ||
                                deptId === this.departmentFilter;

                            const matchesStatus = this.statusFilter === 'all' ||
                                status === this.statusFilter;

                            if (matchesSearch && matchesDepartment && matchesStatus) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        this.noResults = visibleCount === 0;

                        const emptyState = document.getElementById('emptyState');
                        if (emptyState && (this.searchQuery || this.departmentFilter !== 'all' || this.statusFilter !== 'all')) {
                            emptyState.style.display = 'none';
                        }

                        this.sortTable();
                    },

                    sortTable() {
                        const tbody = document.getElementById('employeesTable');
                        if (!tbody) return;

                        const rows = Array.from(tbody.querySelectorAll('.employee-row'));

                        rows.sort((a, b) => {
                            switch (this.sortBy) {
                                case 'newest':
                                    return parseInt(b.dataset.joinDate || 0) - parseInt(a.dataset.joinDate || 0);
                                case 'name-asc':
                                    return (a.dataset.employeeName || '').localeCompare(b.dataset.employeeName || '');
                                case 'name-desc':
                                    return (b.dataset.employeeName || '').localeCompare(a.dataset.employeeName || '');
                                case 'nik-asc':
                                    return (a.dataset.employeeNik || '').localeCompare(b.dataset.employeeNik || '');
                                default:
                                    return 0;
                            }
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    },

                    resetFilters() {
                        this.searchQuery = '';
                        this.departmentFilter = 'all';
                        this.statusFilter = 'all';
                        this.sortBy = 'name-asc';
                        this.filterTable();
                    },

                    toggleAllCheckboxes(event) {
                        const checkboxes = document.querySelectorAll('.row-checkbox');
                        checkboxes.forEach(checkbox => {
                            const row = checkbox.closest('.employee-row');
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