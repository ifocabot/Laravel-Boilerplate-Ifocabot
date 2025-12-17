@extends('layouts.admin')

@section('title', 'Department Management')

@section('content')
    <div class="space-y-6" x-data="departmentManager()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Departemen</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola struktur organisasi dan hierarki departemen perusahaan Anda.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="toggleView()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="viewMode === 'tree'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="viewMode === 'list'"
                        x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span x-text="viewMode === 'tree' ? 'Tampilan List' : 'Tampilan Tree'"></span>
                </button>
                <a href="#"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ekspor
                </a>
                <button @click="openCreateModal()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Departemen
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Departments --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Departemen</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold">{{ $departments->count() }}</h3>
                        </div>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Parent Departments --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Induk Departemen</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $parentDepartments ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Sub Departments --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sub Departemen</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $subDepartments ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Departments with Manager --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Ada Manager</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $withManager ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tree View --}}
        <div x-show="viewMode === 'tree'" x-cloak
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Struktur Organisasi</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Hierarki departemen dalam bentuk tree</p>
                    </div>
                    <button @click="expandAll()" type="button"
                        class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        <span x-text="allExpanded ? 'Collapse All' : 'Expand All'"></span>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-2">
                    @foreach($departments->whereNull('parent_id') as $department)
                        @include('master-data.general.departments.partials.tree-item', ['department' => $department, 'level' => 0])
                    @endforeach
                </div>

                @if($departments->whereNull('parent_id')->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada departemen</p>
                        <p class="text-gray-400 text-sm mb-4">Mulai dengan membuat departemen pertama</p>
                        <button @click="openCreateModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Departemen
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- List View --}}
        <div x-show="viewMode === 'list'" x-cloak
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="tableFilters()">
            {{-- Search and Filter Bar --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-4">
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
                            placeholder="Cari nama atau kode departemen...">
                    </div>

                    {{-- Type Filter --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                            <span
                                x-text="selectedFilter === 'all' ? 'Semua Tipe' : selectedFilter === 'parent' ? 'Induk' : 'Sub Departemen'"></span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
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
                                <button @click="selectedFilter = 'all'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="selectedFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Semua Tipe
                                </button>
                                <button @click="selectedFilter = 'parent'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="selectedFilter === 'parent' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Induk Departemen
                                </button>
                                <button @click="selectedFilter = 'sub'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="selectedFilter === 'sub' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Sub Departemen
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500 font-medium">SORT BY:</span>
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
                                    <button @click="sortBy = 'code-asc'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'code-asc' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Kode (A-Z)
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
                                Departemen</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Kode</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Induk Departemen</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Manager</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Sub Dept</th>
                            <th
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="departmentsTable">
                        @forelse($departments as $department)
                            <tr class="hover:bg-gray-50/50 transition-colors group department-row"
                                data-department-name="{{ strtolower($department->name) }}"
                                data-department-code="{{ strtolower($department->code) }}"
                                data-department-type="{{ $department->parent_id ? 'sub' : 'parent' }}"
                                data-department-date="{{ $department->created_at ? $department->created_at->timestamp : 0 }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 row-checkbox">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg {{ $department->parent_id ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 'bg-gradient-to-br from-indigo-500 to-purple-600' }} flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($department->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $department->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $department->created_at ? $department->created_at->diffForHumans() : 'Baru dibuat' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="text-xs font-mono bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                                                                                                        {{ $department->code }}
                                                                                                                    </code>
                                </td>
                                <td class="px-6 py-4">
                                    @if($department->parent)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded bg-indigo-50 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-indigo-600" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                                </svg>
                                            </div>
                                            <span class="text-sm text-gray-900">{{ $department->parent->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Induk Departemen</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($department->manager)
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-semibold text-xs">
                                                {{ strtoupper(substr($department->manager->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $department->manager->name }}
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $department->manager->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum ada manager</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($department->children->count() > 0)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                                            </svg>
                                            {{ $department->children->count() }} Sub
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="openEditModal({{ $department->id }}, '{{ addslashes($department->name) }}', '{{ $department->code }}', {{ $department->parent_id ?? 'null' }}, {{ $department->manager_id ?? 'null' }})"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('master-data.general.departments.destroy', $department->id) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?');">
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
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada departemen</p>
                                        <p class="text-gray-400 text-sm mb-4">Mulai dengan membuat departemen pertama</p>
                                        <button @click="openCreateModal()"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            Tambah Departemen
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
            @if ($departments->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $departments->links() }}
                </div>
            @endif
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
                        :action="modalMode === 'create' ? '{{ route('master-data.general.departments.store') }}' : '{{ url('master-data/general/departments') }}/' + editDepartmentId"
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
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white" id="modal-title"
                                            x-text="modalMode === 'create' ? 'Tambah Departemen Baru' : 'Edit Departemen'">
                                        </h3>
                                        <p class="text-sm text-indigo-100"
                                            x-text="modalMode === 'create' ? 'Buat departemen baru dalam organisasi' : 'Perbarui informasi departemen'">
                                        </p>
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Department Name --}}
                                <div class="md:col-span-2">
                                    <label for="department_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nama Departemen <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="department_name" required x-model="departmentName"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Contoh: Human Resources">
                                    <p class="mt-1.5 text-xs text-gray-500">
                                        Nama lengkap departemen
                                    </p>
                                </div>

                                {{-- Department Code --}}
                                <div>
                                    <label for="department_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Kode Departemen <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="code" id="department_code" required x-model="departmentCode"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Contoh: HR">
                                    <p class="mt-1.5 text-xs text-gray-500">
                                        Kode unik departemen
                                    </p>
                                </div>

                                {{-- Parent Department --}}
                                <div>
                                    <label for="parent_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Induk Departemen <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                                    </label>
                                    <select name="parent_id" id="parent_id" x-model="parentId"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                        <option value="">Tidak ada (Induk Departemen)</option>
                                        @foreach($departments->whereNull('parent_id') as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1.5 text-xs text-gray-500">
                                        Pilih jika ini sub departemen
                                    </p>
                                </div>

                                {{-- Manager --}}
                                <div class="md:col-span-2">
                                    <label for="manager_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Manager <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                                    </label>
                                    <select name="manager_id" id="manager_id" x-model="managerId"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                        <option value="">Belum ada manager</option>
                                        @foreach($users ?? [] as $user)
                                            @if(is_object($user))
                                                <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <p class="mt-1.5 text-xs text-gray-500">
                                        Tetapkan kepala departemen
                                    </p>
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
                                        <ul class="text-xs text-blue-700 space-y-1">
                                            <li>• Kode departemen harus unik</li>
                                            <li>• Jika tidak memilih induk departemen, ini akan menjadi departemen utama
                                            </li>
                                            <li>• Manager dapat diubah sewaktu-waktu</li>
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
                                    <span
                                        x-text="modalMode === 'create' ? 'Simpan Departemen' : 'Update Departemen'"></span>
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
        // Global reference untuk Alpine component
        let departmentManagerInstance = null;

        function departmentManager() {
            return {
                showModal: false,
                viewMode: 'tree',
                modalMode: 'create',
                editDepartmentId: null,
                departmentName: '',
                departmentCode: '',
                parentId: '',
                managerId: '',
                allExpanded: false,

                init() {
                    // Store instance globally
                    departmentManagerInstance = this;
                },

                toggleView() {
                    this.viewMode = this.viewMode === 'tree' ? 'list' : 'tree';
                },

                openCreateModal() {
                    this.modalMode = 'create';
                    this.departmentName = '';
                    this.departmentCode = '';
                    this.parentId = '';
                    this.managerId = '';
                    this.showModal = true;
                },

                openEditModal(id, name, code, parentId, managerId) {
                    this.modalMode = 'edit';
                    this.editDepartmentId = id;
                    this.departmentName = name;
                    this.departmentCode = code;
                    this.parentId = parentId || '';
                    this.managerId = managerId || '';
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    setTimeout(() => {
                        this.modalMode = 'create';
                        this.editDepartmentId = null;
                        this.departmentName = '';
                        this.departmentCode = '';
                        this.parentId = '';
                        this.managerId = '';
                    }, 300);
                },

                expandAll() {
                    this.allExpanded = !this.allExpanded;
                    const items = document.querySelectorAll('[x-data*="expanded"]');
                    items.forEach(item => {
                        try {
                            const component = Alpine.$data(item);
                            if (component && 'expanded' in component) {
                                component.expanded = this.allExpanded;
                            }
                        } catch (e) {
                            console.log('Skip non-Alpine element');
                        }
                    });
                }
            }
        }

        // Global functions for tree view buttons
        window.openEditModalFromTree = function(id, name, code, parentId, managerId) {
            if (departmentManagerInstance) {
                departmentManagerInstance.openEditModal(id, name, code, parentId, managerId);
            } else {
                console.error('Department manager not initialized');
            }
        };

        window.openAddSubDepartmentModal = function(parentId, parentName) {
            if (departmentManagerInstance) {
                departmentManagerInstance.openCreateModal();
                
                // Set parent after modal opens
                setTimeout(() => {
                    departmentManagerInstance.parentId = parentId;
                    const parentSelect = document.getElementById('parent_id');
                    if (parentSelect) {
                        parentSelect.value = parentId;
                    }
                }, 100);
            } else {
                console.error('Department manager not initialized');
            }
        };

        function tableFilters() {
            return {
                searchQuery: '',
                selectedFilter: 'all',
                sortBy: 'name-asc',
                noResults: false,
                sortOptions: {
                    'name-asc': 'Nama (A-Z)',
                    'name-desc': 'Nama (Z-A)',
                    'code-asc': 'Kode (A-Z)',
                    'newest': 'Terbaru'
                },

                filterTable() {
                    const rows = document.querySelectorAll('.department-row');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const name = row.dataset.departmentName || '';
                        const code = row.dataset.departmentCode || '';
                        const type = row.dataset.departmentType || '';
                        const searchTerm = this.searchQuery.toLowerCase();

                        const matchesSearch = !searchTerm || 
                            name.includes(searchTerm) || 
                            code.includes(searchTerm);

                        const matchesFilter = this.selectedFilter === 'all' || 
                            type === this.selectedFilter;

                        if (matchesSearch && matchesFilter) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    this.noResults = visibleCount === 0;
                    
                    const emptyState = document.getElementById('emptyState');
                    if (emptyState && (this.searchQuery || this.selectedFilter !== 'all')) {
                        emptyState.style.display = 'none';
                    }

                    this.sortTable();
                },

                sortTable() {
                    const tbody = document.getElementById('departmentsTable');
                    if (!tbody) return;
                    
                    const rows = Array.from(tbody.querySelectorAll('.department-row'));
                    
                    rows.sort((a, b) => {
                        switch(this.sortBy) {
                            case 'newest':
                                return parseInt(b.dataset.departmentDate || 0) - parseInt(a.dataset.departmentDate || 0);
                            case 'name-asc':
                                return (a.dataset.departmentName || '').localeCompare(b.dataset.departmentName || '');
                            case 'name-desc':
                                return (b.dataset.departmentName || '').localeCompare(a.dataset.departmentName || '');
                            case 'code-asc':
                                return (a.dataset.departmentCode || '').localeCompare(b.dataset.departmentCode || '');
                            default:
                                return 0;
                        }
                    });

                    rows.forEach(row => tbody.appendChild(row));
                },

                resetFilters() {
                    this.searchQuery = '';
                    this.selectedFilter = 'all';
                    this.sortBy = 'name-asc';
                    this.filterTable();
                },

                toggleAllCheckboxes(event) {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(checkbox => {
                        const row = checkbox.closest('.department-row');
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