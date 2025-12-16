@extends('layouts.admin')

@section('title', 'Role Management')

@section('content')
    <div class="space-y-6" x-data="roleManager()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Izin</h1>
                <p class="mt-1 text-sm text-gray-500">Pusat kontrol untuk membuat, mengubah, dan mengelompokkan izin akses
                    sistem. Tetapkan izin granular ke peran untuk keamanan yang lebih baik.</p>
            </div>
            <div class="flex items-center gap-3">
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
                    Buat Role Baru
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Total Roles --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Roles</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold">{{ $roles->count() }}</h3>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-indigo-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                +5%
                            </span>
                        </div>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Module Groups --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Users</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $user ?? 12 }}</h3>
                            <span class="text-sm font-medium text-gray-500">Aktif</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Critical Permissions --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Permissions</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $criticalPermissions ?? 8 }}</h3>
                            <span class="text-sm font-medium text-gray-500">Membutuhkan audit</span>
                        </div>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Card --}}
        {{-- Main Content Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="tableFilters()">
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
                            placeholder="Cari nama peran, kode slug, atau izin...">
                    </div>

                    {{-- Module Filter --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                            <span
                                x-text="selectedFilter === 'all' ? 'Semua Status' : selectedFilter === 'active' ? 'Aktif' : 'Tidak Aktif'"></span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
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
                                    Semua Status
                                </button>
                                <button @click="selectedFilter = 'active'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="selectedFilter === 'active' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Aktif
                                </button>
                                <button @click="selectedFilter = 'inactive'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="selectedFilter === 'inactive' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Tidak Aktif
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

                            {{-- Dropdown --}}
                            <div x-show="open" @click.away="open = false" x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button @click="sortBy = 'newest'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'newest' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Terbaru
                                    </button>
                                    <button @click="sortBy = 'oldest'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'oldest' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Terlama
                                    </button>
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
                                    <button @click="sortBy = 'permissions'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'permissions' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Jumlah Izin
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
                                Nama Peran</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Kode Slug</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Izin</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Status</th>
                            <th
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="rolesTable">
                        @forelse($roles as $role)
                            <tr class="hover:bg-gray-50/50 transition-colors group role-row"
                                data-role-name="{{ strtolower($role->name) }}" data-role-slug="{{ Str::slug($role->name) }}"
                                data-role-permissions="{{ $role->permissions->pluck('name')->implode(',') }}"
                                data-role-date="{{ $role->created_at->timestamp }}"
                                data-permission-count="{{ $role->permissions->count() }}" data-status="active">
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 row-checkbox">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($role->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $role->name }}</div>
                                            <div class="text-xs text-gray-500">Dibuat {{ $role->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code
                                        class="text-xs font-mono bg-gray-100 text-gray-700 px-2 py-1 rounded">{{ Str::slug($role->name) }}</code>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $displayPermissions = $role->permissions->take(3);
                                            $remainingCount = $role->permissions->count() - 3;
                                        @endphp
                                        @forelse($displayPermissions as $permission)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ $permission->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Belum ada izin</span>
                                        @endforelse
                                        @if ($remainingCount > 0)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">
                                                +{{ $remainingCount }} lainnya
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                        Aktif
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="openEditModal({{ $role->id }}, '{{ $role->name }}', {{ json_encode($role->permissions->pluck('id')) }})"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('access-control.roles.destroy', $role->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus peran ini?');">
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
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada peran</p>
                                        <p class="text-gray-400 text-sm mb-4">Mulai dengan membuat peran pertama Anda</p>
                                        <button @click="openCreateModal()"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                            Buat Peran Baru
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
            @if ($roles->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>

        {{-- Create/Edit Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="closeModal()">

            {{-- Backdrop --}}
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal()">
                </div>

                {{-- Center modal --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal panel --}}
                <div x-show="showModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form
                        :action="modalMode === 'create' ? '{{ route('access-control.roles.store') }}' : '{{ url('access-control/roles') }}/' + editRoleId"
                        method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="modalMode === 'edit' ? 'PUT' : 'POST'">

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            {{-- ... --}}
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-6 max-h-[60vh] overflow-y-auto">
                            {{-- Role Name --}}
                            <div>
                                <label for="role_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Nama Peran <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="role_name" required x-model="roleName"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                    placeholder="Contoh: Manager Keuangan">
                            </div>

                            {{-- Permissions Selection --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">
                                    Izin Akses <span class="text-red-500">*</span>
                                </label>

                                {{-- Search Permissions --}}
                                <div class="relative mb-3">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input type="text" x-model="searchPermission" @input="filterPermissions()"
                                        class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                        placeholder="Cari izin...">
                                </div>

                                {{-- Permissions List --}}
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div
                                        class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Pilih Izin</span>
                                        <div class="flex gap-2">
                                            <button type="button" @click="selectAllPermissions()"
                                                class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                                Pilih Semua
                                            </button>
                                            <span class="text-gray-300">|</span>
                                            <button type="button" @click="deselectAllPermissions()"
                                                class="text-xs font-medium text-gray-600 hover:text-gray-700">
                                                Hapus Semua
                                            </button>
                                        </div>
                                    </div>

                                    <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                                        @forelse($permissions ?? [] as $permission)
                                            <label
                                                class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors permission-item"
                                                :style="filteredPermissions.includes({{ $permission->id }}) ? '' : 'display: none;'">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 permission-checkbox"
                                                    x-model="selectedPermissions">
                                                <div class="flex-1">
                                                    <div class="text-sm font-medium text-gray-900 permission-name">
                                                        {{ $permission->name }}
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded">{{ $permission->name }}</span>
                                            </label>
                                        @empty
                                            <div class="px-4 py-8 text-center text-gray-500 text-sm">
                                                Tidak ada izin tersedia. Silakan buat izin terlebih dahulu.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <p class="mt-2 text-xs text-gray-500">
                                    <span x-text="selectedPermissions.length"></span> izin dipilih
                                </p>
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
                                    <span x-text="modalMode === 'create' ? 'Simpan Peran' : 'Update Peran'"></span>
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
            function roleManager() {
                return {
                    showModal: false,
                    modalMode: 'create', // 'create' or 'edit'
                    editRoleId: null,
                    roleName: '',
                    selectedPermissions: [],
                    allPermissions: @json($permissions->pluck('id')),
                    filteredPermissions: @json($permissions->pluck('id')),
                    searchPermission: '',

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.roleName = '';
                        this.selectedPermissions = [];
                        this.searchPermission = '';
                        this.filteredPermissions = [...this.allPermissions];
                        this.showModal = true;
                    },

                    openEditModal(roleId, roleName, permissions) {
                        this.modalMode = 'edit';
                        this.editRoleId = roleId;
                        this.roleName = roleName;
                        this.selectedPermissions = [...permissions]; // Clone array
                        this.searchPermission = '';
                        this.filteredPermissions = [...this.allPermissions];
                        this.showModal = true;

                        // Wait for modal to render then sync checkboxes
                        this.$nextTick(() => {
                            this.syncCheckboxes();
                        });
                    },

                    closeModal() {
                        this.showModal = false;
                        setTimeout(() => {
                            this.modalMode = 'create';
                            this.editRoleId = null;
                            this.roleName = '';
                            this.selectedPermissions = [];
                            this.searchPermission = '';
                        }, 300);
                    },

                    syncCheckboxes() {
                        // Ensure checkboxes reflect selectedPermissions
                        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
                            const permissionId = parseInt(checkbox.value);
                            checkbox.checked = this.selectedPermissions.includes(permissionId);
                        });
                    },

                    selectAllPermissions() {
                        this.selectedPermissions = [...this.filteredPermissions];
                    },

                    deselectAllPermissions() {
                        this.selectedPermissions = [];
                    },

                    filterPermissions() {
                        const searchTerm = this.searchPermission.toLowerCase();
                        if (!searchTerm) {
                            this.filteredPermissions = [...this.allPermissions];
                            return;
                        }

                        const permissionElements = document.querySelectorAll('.permission-item');
                        this.filteredPermissions = [];

                        permissionElements.forEach(element => {
                            const name = element.querySelector('.permission-name').textContent.toLowerCase();
                            const checkbox = element.querySelector('.permission-checkbox');
                            const permissionId = parseInt(checkbox.value);

                            if (name.includes(searchTerm)) {
                                this.filteredPermissions.push(permissionId);
                            }
                        });
                    }
                }
            }
            // Simple search functionality for roles table
            document.getElementById('searchInput').addEventListener('input', function (e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#rolesTable tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
    @push('scripts')
        <script>
            function tableFilters() {
                return {
                    searchQuery: '',
                    selectedFilter: 'all',
                    sortBy: 'newest',
                    noResults: false,
                    sortOptions: {
                        'newest': 'Terbaru',
                        'oldest': 'Terlama',
                        'name-asc': 'Nama (A-Z)',
                        'name-desc': 'Nama (Z-A)',
                        'permissions': 'Jumlah Izin'
                    },

                    filterTable() {
                        const rows = document.querySelectorAll('.role-row');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const name = row.dataset.roleName;
                            const slug = row.dataset.roleSlug;
                            const permissions = row.dataset.rolePermissions.toLowerCase();
                            const status = row.dataset.status;
                            const searchTerm = this.searchQuery.toLowerCase();

                            // Check search match
                            const matchesSearch = !searchTerm ||
                                name.includes(searchTerm) ||
                                slug.includes(searchTerm) ||
                                permissions.includes(searchTerm);

                            // Check filter match
                            const matchesFilter = this.selectedFilter === 'all' ||
                                status === this.selectedFilter;

                            // Show/hide row
                            if (matchesSearch && matchesFilter) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        // Show/hide no results message
                        this.noResults = visibleCount === 0;

                        // Hide empty state if we have filters
                        const emptyState = document.getElementById('emptyState');
                        if (emptyState && (this.searchQuery || this.selectedFilter !== 'all')) {
                            emptyState.style.display = 'none';
                        }

                        // Sort after filtering
                        this.sortTable();
                    },

                    sortTable() {
                        const tbody = document.getElementById('rolesTable');
                        const rows = Array.from(tbody.querySelectorAll('.role-row'));

                        rows.sort((a, b) => {
                            switch (this.sortBy) {
                                case 'newest':
                                    return parseInt(b.dataset.roleDate) - parseInt(a.dataset.roleDate);
                                case 'oldest':
                                    return parseInt(a.dataset.roleDate) - parseInt(b.dataset.roleDate);
                                case 'name-asc':
                                    return a.dataset.roleName.localeCompare(b.dataset.roleName);
                                case 'name-desc':
                                    return b.dataset.roleName.localeCompare(a.dataset.roleName);
                                case 'permissions':
                                    return parseInt(b.dataset.permissionCount) - parseInt(a.dataset.permissionCount);
                                default:
                                    return 0;
                            }
                        });

                        // Re-append sorted rows
                        rows.forEach(row => tbody.appendChild(row));
                    },

                    resetFilters() {
                        this.searchQuery = '';
                        this.selectedFilter = 'all';
                        this.sortBy = 'newest';
                        this.filterTable();
                    },

                    toggleAllCheckboxes(event) {
                        const checkboxes = document.querySelectorAll('.row-checkbox');
                        checkboxes.forEach(checkbox => {
                            const row = checkbox.closest('.role-row');
                            if (row.style.display !== 'none') {
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