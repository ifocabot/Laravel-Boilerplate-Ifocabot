@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('content')
    <div class="space-y-6" x-data="auditLogManager()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Log Aktivitas Sistem</h1>
                <p class="mt-1 text-sm text-gray-500">Pantau semua aktivitas dan perubahan yang terjadi dalam sistem secara real-time untuk keamanan dan audit.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="exportLogs()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Ekspor Log
                </button>
                <button @click="clearOldLogs()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Log Lama
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Logs --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Log</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold">{{ $audits->total() }}</h3>
                        </div>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Created Events --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Created</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $createdCount ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Updated Events --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Updated</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $updatedCount ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Deleted Events --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Deleted</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $deletedCount ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                            placeholder="Cari user, event, atau perubahan...">
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Event Type Filter --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                <span x-text="eventFilterLabel"></span>
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
                                    <button @click="eventFilter = 'all'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="eventFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Semua Event
                                    </button>
                                    <button @click="eventFilter = 'created'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="eventFilter === 'created' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Created
                                    </button>
                                    <button @click="eventFilter = 'updated'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="eventFilter === 'updated' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Updated
                                    </button>
                                    <button @click="eventFilter = 'deleted'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="eventFilter === 'deleted' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Deleted
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Date Range Filter --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span x-text="dateFilterLabel"></span>
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
                                    <button @click="dateFilter = 'all'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="dateFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Semua Waktu
                                    </button>
                                    <button @click="dateFilter = 'today'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="dateFilter === 'today' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Hari Ini
                                    </button>
                                    <button @click="dateFilter = 'week'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="dateFilter === 'week' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        7 Hari Terakhir
                                    </button>
                                    <button @click="dateFilter = 'month'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="dateFilter === 'month' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        30 Hari Terakhir
                                    </button>
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
                                User</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Event</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Model</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Perubahan</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">
                                Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="auditsTable">
                        @forelse($audits as $audit)
                            <tr class="hover:bg-gray-50/50 transition-colors group audit-row"
                                data-user="{{ strtolower($audit->user->name ?? 'system') }}"
                                data-event="{{ $audit->event }}"
                                data-model="{{ strtolower(class_basename($audit->auditable_type)) }}"
                                data-date="{{ $audit->created_at->timestamp }}"
                                data-search="{{ strtolower($audit->user->name ?? 'system') }} {{ $audit->event }} {{ strtolower(class_basename($audit->auditable_type)) }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 row-checkbox">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($audit->user->name ?? 'SY', 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $audit->user->name ?? 'System' }}</div>
                                            <div class="text-xs text-gray-500">{{ $audit->user->email ?? 'system@auto' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($audit->event == 'created')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Created
                                        </span>
                                    @elseif($audit->event == 'updated')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Updated
                                        </span>
                                    @elseif($audit->event == 'deleted')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Deleted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-700 border border-gray-100">
                                            {{ ucfirst($audit->event) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ class_basename($audit->auditable_type) }}</div>
                                            <div class="text-xs text-gray-500">ID: #{{ $audit->auditable_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($audit->getModified() && count($audit->getModified()) > 0)
                                        <button @click="openChangesModal({{ json_encode($audit->getModified()) }}, '{{ class_basename($audit->auditable_type) }}', {{ $audit->auditable_id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-medium rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat {{ count($audit->getModified()) }} Perubahan
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Tidak ada perubahan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-sm text-gray-900">{{ $audit->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $audit->created_at->format('H:i') }}</div>
                                    <div class="text-xs text-gray-400">{{ $audit->created_at->diffForHumans() }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyState">
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada log aktivitas</p>
                                        <p class="text-gray-400 text-sm">Log akan muncul ketika ada perubahan dalam sistem</p>
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
            @if ($audits->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $audits->links() }}
                </div>
            @endif
        </div>

        {{-- Changes Detail Modal --}}
        <div x-show="showChangesModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
            role="dialog" aria-modal="true" @keydown.escape.window="closeChangesModal()">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showChangesModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeChangesModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showChangesModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-white" id="modal-title">Detail Perubahan</h3>
                                    <p class="text-sm text-purple-100" x-text="selectedModel + ' #' + selectedId"></p>
                                </div>
                            </div>
                            <button @click="closeChangesModal()" type="button"
                                class="text-white/80 hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-6 max-h-[60vh] overflow-y-auto">
                        <div class="space-y-4">
                            <template x-for="(change, attribute) in selectedChanges" :key="attribute">
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200">
                                        <span class="text-sm font-semibold text-gray-900" x-text="attribute"></span>
                                    </div>
                                    <div class="grid grid-cols-2 divide-x divide-gray-200">
                                        {{-- Old Value --}}
                                        <div class="p-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                                    Sebelum
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 break-words">
                                                <template x-if="typeof change.old === 'object' && change.old !== null">
                                                    <pre class="bg-gray-50 p-2 rounded text-xs overflow-auto" x-text="JSON.stringify(change.old, null, 2)"></pre>
                                                </template>
                                                <template x-if="typeof change.old !== 'object'">
                                                    <span x-text="change.old || 'Kosong'"></span>
                                                </template>
                                            </div>
                                        </div>
                                        {{-- New Value --}}
                                        <div class="p-4">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                                    Sesudah
                                                </span>
                                            </div>
                                            <div class="text-sm text-gray-600 break-words">
                                                <template x-if="typeof change.new === 'object' && change.new !== null">
                                                    <pre class="bg-gray-50 p-2 rounded text-xs overflow-auto" x-text="JSON.stringify(change.new, null, 2)"></pre>
                                                </template>
                                                <template x-if="typeof change.new !== 'object'">
                                                    <span x-text="change.new || 'Kosong'"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                        <button @click="closeChangesModal()" type="button"
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
            function auditLogManager() {
                return {
                    showChangesModal: false,
                    selectedChanges: {},
                    selectedModel: '',
                    selectedId: '',

                    openChangesModal(changes, model, id) {
                        this.selectedChanges = changes;
                        this.selectedModel = model;
                        this.selectedId = id;
                        this.showChangesModal = true;
                    },

                    closeChangesModal() {
                        this.showChangesModal = false;
                        setTimeout(() => {
                            this.selectedChanges = {};
                            this.selectedModel = '';
                            this.selectedId = '';
                        }, 300);
                    },

                    exportLogs() {
                        alert('Export functionality - to be implemented');
                    },

                    clearOldLogs() {
                        if (confirm('Apakah Anda yakin ingin menghapus log lama? Tindakan ini tidak dapat dibatalkan.')) {
                            alert('Clear old logs functionality - to be implemented');
                        }
                    }
                }
            }

            function tableFilters() {
                return {
                    searchQuery: '',
                    eventFilter: 'all',
                    dateFilter: 'all',
                    sortBy: 'newest',
                    noResults: false,
                    sortOptions: {
                        'newest': 'Terbaru',
                        'oldest': 'Terlama'
                    },

                    get eventFilterLabel() {
                        const labels = {
                            'all': 'Semua Event',
                            'created': 'Created',
                            'updated': 'Updated',
                            'deleted': 'Deleted'
                        };
                        return labels[this.eventFilter] || 'Semua Event';
                    },

                    get dateFilterLabel() {
                        const labels = {
                            'all': 'Semua Waktu',
                            'today': 'Hari Ini',
                            'week': '7 Hari Terakhir',
                            'month': '30 Hari Terakhir'
                        };
                        return labels[this.dateFilter] || 'Semua Waktu';
                    },

                    filterTable() {
                        const rows = document.querySelectorAll('.audit-row');
                        let visibleCount = 0;
                        const now = Date.now() / 1000; // Current timestamp in seconds

                        rows.forEach(row => {
                            const searchData = row.dataset.search;
                            const event = row.dataset.event;
                            const date = parseInt(row.dataset.date);
                            const searchTerm = this.searchQuery.toLowerCase();

                            // Search match
                            const matchesSearch = !searchTerm || searchData.includes(searchTerm);

                            // Event filter match
                            const matchesEvent = this.eventFilter === 'all' || event === this.eventFilter;

                            // Date filter match
                            let matchesDate = true;
                            if (this.dateFilter === 'today') {
                                const oneDayAgo = now - (24 * 60 * 60);
                                matchesDate = date >= oneDayAgo;
                            } else if (this.dateFilter === 'week') {
                                const oneWeekAgo = now - (7 * 24 * 60 * 60);
                                matchesDate = date >= oneWeekAgo;
                            } else if (this.dateFilter === 'month') {
                                const oneMonthAgo = now - (30 * 24 * 60 * 60);
                                matchesDate = date >= oneMonthAgo;
                            }

                            if (matchesSearch && matchesEvent && matchesDate) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        this.noResults = visibleCount === 0;

                        const emptyState = document.getElementById('emptyState');
                        if (emptyState && (this.searchQuery || this.eventFilter !== 'all' || this.dateFilter !== 'all')) {
                            emptyState.style.display = 'none';
                        }

                        this.sortTable();
                    },

                    sortTable() {
                        const tbody = document.getElementById('auditsTable');
                        const rows = Array.from(tbody.querySelectorAll('.audit-row'));

                        rows.sort((a, b) => {
                            const dateA = parseInt(a.dataset.date);
                            const dateB = parseInt(b.dataset.date);

                            if (this.sortBy === 'newest') {
                                return dateB - dateA;
                            } else {
                                return dateA - dateB;
                            }
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    },

                    resetFilters() {
                        this.searchQuery = '';
                        this.eventFilter = 'all';
                        this.dateFilter = 'all';
                        this.sortBy = 'newest';
                        this.filterTable();
                    },

                    toggleAllCheckboxes(event) {
                        const checkboxes = document.querySelectorAll('.row-checkbox');
                        checkboxes.forEach(checkbox => {
                            const row = checkbox.closest('.audit-row');
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