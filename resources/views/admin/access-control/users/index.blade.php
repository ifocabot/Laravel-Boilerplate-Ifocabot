@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="space-y-6" x-data="userManager()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Pengguna</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola pengguna dan role mereka dalam sistem. Tetapkan, ubah, atau hapus role pengguna dengan mudah.</p>
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
                    Tambah Pengguna
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Users --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Pengguna</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold">{{ $users->total() }}</h3>
                        </div>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Users --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Pengguna Aktif</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $activeUsers ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Users with Roles --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Dengan Role</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $usersWithRoles ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Users without Roles --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tanpa Role</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $usersWithoutRoles ?? 0 }}</h3>
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
                            placeholder="Cari nama pengguna atau email...">
                    </div>

                    {{-- Role Filter --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                            <span x-text="selectedFilter === 'all' ? 'Semua Pengguna' : selectedFilter === 'with-roles' ? 'Dengan Role' : 'Tanpa Role'"></span>
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
                                    Semua Pengguna
                                </button>
                                <button @click="selectedFilter = 'with-roles'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="selectedFilter === 'with-roles' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Dengan Role
                                </button>
                                <button @click="selectedFilter = 'without-roles'; filterTable(); open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                    :class="selectedFilter === 'without-roles' ? 'bg-indigo-50 text-indigo-700' : ''">
                                    Tanpa Role
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
                                    <button @click="sortBy = 'oldest'; sortTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="sortBy === 'oldest' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Terlama
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
                                Pengguna</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Email</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Role Saat Ini</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Status</th>
                            <th
                                class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="usersTable">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50/50 transition-colors group user-row"
                                data-user-name="{{ strtolower($user->name) }}"
                                data-user-email="{{ strtolower($user->email) }}"
                                data-user-date="{{ $user->created_at ? $user->created_at->timestamp : 0 }}"
                                data-has-roles="{{ $user->roles->count() > 0 ? 'with-roles' : 'without-roles' }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 row-checkbox">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                @if($user->created_at)
                                                    Bergabung {{ $user->created_at->diffForHumans() }}
                                                @else
                                                    Pengguna baru
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-600">{{ $user->email }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Belum ada role</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->roles->count() > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                            Perlu Role
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="openManageRolesModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ json_encode($user->roles->pluck('id')) }})"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Kelola Role">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                        </button>
                                        <button
                                            @click="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('access-control.users.destroy', $user->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
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
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada pengguna</p>
                                        <p class="text-gray-400 text-sm mb-4">Mulai dengan menambahkan pengguna pertama</p>
                                        <button @click="openCreateModal()"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Tambah Pengguna
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
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        {{-- Manage Roles Modal --}}
        <div x-show="showManageRolesModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="closeManageRolesModal()">

            {{-- Backdrop --}}
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showManageRolesModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeManageRolesModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal panel --}}
                <div x-show="showManageRolesModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form action="{{ route('access-control.users.assign-role', ':userId') }}" method="POST" @submit="updateFormAction($event)">
                        @csrf

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white" id="modal-title">Kelola Role Pengguna</h3>
                                        <p class="text-sm text-indigo-100" x-text="'Tetapkan role untuk ' + selectedUserName"></p>
                                    </div>
                                </div>
                                <button @click="closeManageRolesModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-6 max-h-[60vh] overflow-y-auto">
                            {{-- Search Roles --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" x-model="searchRole" @input="filterRoles()"
                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Cari role...">
                            </div>

                            {{-- Roles List --}}
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Pilih Role</span>
                                    <div class="flex gap-2">
                                        <button type="button" @click="selectAllRoles()"
                                            class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                                            Pilih Semua
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" @click="deselectAllRoles()"
                                            class="text-xs font-medium text-gray-600 hover:text-gray-700">
                                            Hapus Semua
                                        </button>
                                    </div>
                                </div>

                                <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                                    @forelse($roles ?? [] as $role)
                                        <label
                                            class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors role-item"
                                            :style="filteredRoles.includes({{ $role->id }}) ? '' : 'display: none;'">
                                            <input type="checkbox" name="roles[]"
                                                value="{{ $role->id }}"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 role-checkbox"
                                                x-model="selectedRoles">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900 role-name">
                                                    {{ $role->name }}
                                                </div>
                                                @if($role->permissions->count() > 0)
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ $role->permissions->count() }} izin
                                                    </div>
                                                @endif
                                            </div>
                                            <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded">
                                                {{ Str::slug($role->name) }}
                                            </span>
                                        </label>
                                    @empty
                                        <div class="px-4 py-8 text-center text-gray-500 text-sm">
                                            Tidak ada role tersedia. Silakan buat role terlebih dahulu.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <p class="text-xs text-gray-500">
                                <span x-text="selectedRoles.length"></span> role dipilih
                            </p>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeManageRolesModal()" type="button"
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
                                    Simpan Role
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
            function userManager() {
                return {
                    showManageRolesModal: false,
                    showCreateModal: false,
                    showEditModal: false,
                    selectedUserId: null,
                    selectedUserName: '',
                    selectedRoles: [],
                    allRoles: @json($roles->pluck('id')),
                    filteredRoles: @json($roles->pluck('id')),
                    searchRole: '',

                    openManageRolesModal(userId, userName, userRoles) {
                        this.selectedUserId = userId;
                        this.selectedUserName = userName;
                        this.selectedRoles = [...userRoles];
                        this.searchRole = '';
                        this.filteredRoles = [...this.allRoles];
                        this.showManageRolesModal = true;
                    },

                    closeManageRolesModal() {
                        this.showManageRolesModal = false;
                        setTimeout(() => {
                            this.selectedUserId = null;
                            this.selectedUserName = '';
                            this.selectedRoles = [];
                            this.searchRole = '';
                        }, 300);
                    },

                    updateFormAction(event) {
                        const form = event.target;
                        const action = form.action.replace(':userId', this.selectedUserId);
                        form.action = action;
                    },

                    selectAllRoles() {
                        this.selectedRoles = [...this.filteredRoles];
                    },

                    deselectAllRoles() {
                        this.selectedRoles = [];
                    },

                    filterRoles() {
                        const searchTerm = this.searchRole.toLowerCase();
                        if (!searchTerm) {
                            this.filteredRoles = [...this.allRoles];
                            return;
                        }

                        const roleElements = document.querySelectorAll('.role-item');
                        this.filteredRoles = [];
                        
                        roleElements.forEach(element => {
                            const name = element.querySelector('.role-name').textContent.toLowerCase();
                            const checkbox = element.querySelector('.role-checkbox');
                            const roleId = parseInt(checkbox.value);
                            
                            if (name.includes(searchTerm)) {
                                this.filteredRoles.push(roleId);
                            }
                        });
                    },

                    openCreateModal() {
                        alert('Create user modal - to be implemented');
                    },

                    openEditModal(userId, userName, userEmail) {
                        alert('Edit user modal - to be implemented');
                    }
                }
            }

            function tableFilters() {
                return {
                    searchQuery: '',
                    selectedFilter: 'all',
                    sortBy: 'name-asc',
                    noResults: false,
                    sortOptions: {
                        'name-asc': 'Nama (A-Z)',
                        'name-desc': 'Nama (Z-A)',
                        'newest': 'Terbaru',
                        'oldest': 'Terlama'
                    },

                    filterTable() {
                        const rows = document.querySelectorAll('.user-row');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const name = row.dataset.userName;
                            const email = row.dataset.userEmail;
                            const hasRoles = row.dataset.hasRoles;
                            const searchTerm = this.searchQuery.toLowerCase();

                            const matchesSearch = !searchTerm || 
                                name.includes(searchTerm) || 
                                email.includes(searchTerm);

                            const matchesFilter = this.selectedFilter === 'all' || 
                                hasRoles === this.selectedFilter;

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
                        const tbody = document.getElementById('usersTable');
                        const rows = Array.from(tbody.querySelectorAll('.user-row'));
                        
                        rows.sort((a, b) => {
                            switch(this.sortBy) {
                                case 'newest':
                                    return parseInt(b.dataset.userDate) - parseInt(a.dataset.userDate);
                                case 'oldest':
                                    return parseInt(a.dataset.userDate) - parseInt(b.dataset.userDate);
                                case 'name-asc':
                                    return a.dataset.userName.localeCompare(b.dataset.userName);
                                case 'name-desc':
                                    return b.dataset.userName.localeCompare(a.dataset.userName);
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
                            const row = checkbox.closest('.user-row');
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