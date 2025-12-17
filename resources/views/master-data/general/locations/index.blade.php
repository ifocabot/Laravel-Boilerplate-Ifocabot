@extends('layouts.admin')

@section('title', 'Location Management')

@section('content')
    <div class="space-y-6" x-data="locationManager()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Lokasi</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola lokasi kerja, cabang, dan area dengan geo-fence untuk sistem absensi.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="toggleView()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="viewMode === 'tree'">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="viewMode === 'list'" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
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
                    Tambah Lokasi
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Total Locations --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Lokasi</p>
                        <h3 class="text-3xl font-bold mt-2">{{ $locations->count() }}</h3>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Locations --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Aktif</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $activeLocations ?? 0 }}</h3>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Parent Locations --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Induk Lokasi</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $parentLocations ?? 0 }}</h3>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Sub Locations --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sub Lokasi</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $subLocations ?? 0 }}</h3>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- With Geo-fence --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Geo-fence</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $withGeofence ?? 0 }}</h3>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tree View --}}
        <div x-show="viewMode === 'tree'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Struktur Lokasi</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Hierarki lokasi dalam bentuk tree</p>
                    </div>
                    <button @click="expandAll()" type="button"
                        class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                        <span x-text="allExpanded ? 'Collapse All' : 'Expand All'"></span>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-2">
                    @foreach($locations->whereNull('parent_id') as $location)
                        @include('master-data.general.locations.partials.tree-item', ['location' => $location, 'level' => 0])
                    @endforeach
                </div>

                @if($locations->whereNull('parent_id')->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                        </div>
                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada lokasi</p>
                        <p class="text-gray-400 text-sm mb-4">Mulai dengan membuat lokasi pertama</p>
                        <button @click="openCreateModal()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Lokasi
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- List View --}}
        <div x-show="viewMode === 'list'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="tableFilters()">
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
                            placeholder="Cari nama atau kode lokasi...">
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Type Filter --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                <span x-text="typeFilterLabel"></span>
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
                                    <button @click="typeFilter = 'all'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="typeFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Semua Tipe
                                    </button>
                                    <button @click="typeFilter = 'office'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="typeFilter === 'office' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Kantor
                                    </button>
                                    <button @click="typeFilter = 'warehouse'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="typeFilter === 'warehouse' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Gudang
                                    </button>
                                    <button @click="typeFilter = 'store'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="typeFilter === 'store' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Retail
                                    </button>
                                    <button @click="typeFilter = 'bin'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="typeFilter === 'bin' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Bin/Rak
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Status Filter --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                <span x-text="statusFilter === 'all' ? 'Semua Status' : statusFilter === 'active' ? 'Aktif' : 'Nonaktif'"></span>
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
                                    <button @click="statusFilter = 'all'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="statusFilter === 'all' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Semua Status
                                    </button>
                                    <button @click="statusFilter = 'active'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="statusFilter === 'active' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Aktif
                                    </button>
                                    <button @click="statusFilter = 'inactive'; filterTable(); open = false"
                                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                                        :class="statusFilter === 'inactive' ? 'bg-indigo-50 text-indigo-700' : ''">
                                        Nonaktif
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
                                Lokasi</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Tipe</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Koordinat</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Radius</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                Status</th>
                            <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50 text-right">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="locationsTable">
                        @forelse($locations as $location)
                            <tr class="hover:bg-gray-50/50 transition-colors group location-row"
                                data-location-name="{{ strtolower($location->name) }}"
                                data-location-code="{{ strtolower($location->code) }}"
                                data-location-type="{{ $location->type }}"
                                data-location-status="{{ $location->is_active ? 'active' : 'inactive' }}"
                                data-location-date="{{ $location->created_at ? $location->created_at->timestamp : 0 }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 row-checkbox">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($location->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900">{{ $location->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                <code class="bg-gray-100 px-1.5 py-0.5 rounded">{{ $location->code }}</code>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeColors = [
                                            'office' => 'bg-blue-50 text-blue-700 border-blue-100',
                                            'warehouse' => 'bg-amber-50 text-amber-700 border-amber-100',
                                            'store' => 'bg-purple-50 text-purple-700 border-purple-100',
                                            'bin' => 'bg-green-50 text-green-700 border-green-100',
                                        ];
                                        $typeLabels = [
                                            'office' => 'Kantor',
                                            'warehouse' => 'Gudang',
                                            'store' => 'Retail',
                                            'bin' => 'Bin/Rak',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border {{ $typeColors[$location->type] ?? 'bg-gray-50 text-gray-700 border-gray-100' }}">
                                        {{ $typeLabels[$location->type] ?? ucfirst($location->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($location->latitude && $location->longitude)
                                        <div class="flex flex-col gap-1">
                                            <div class="text-xs text-gray-600">
                                                <span class="font-mono">{{ number_format($location->latitude, 6) }}</span>
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                <span class="font-mono">{{ number_format($location->longitude, 6) }}</span>
                                            </div>
                                        </div>
                                        <button @click="showOnMap({{ $location->latitude }}, {{ $location->longitude }})"
                                            class="mt-1 text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            Lihat di Maps
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum di-set</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($location->radius_meters)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                                            </svg>
                                            {{ number_format($location->radius_meters) }}m
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($location->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button
                                            onclick="window.openEditModalFromList({{ $location->id }}, '{{ addslashes($location->name) }}', '{{ $location->code }}', '{{ $location->type }}', '{{ addslashes($location->address ?? '') }}', {{ $location->latitude ?? 'null' }}, {{ $location->longitude ?? 'null' }}, {{ $location->radius_meters ?? 'null' }}, {{ $location->is_active ? 'true' : 'false' }}, {{ $location->parent_id ?? 'null' }})"
                                            class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('master-data.general.locations.destroy', $location->id) }}" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?');">
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
                                            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-500 text-sm font-medium mb-1">Belum ada lokasi</p>
                                        <p class="text-gray-400 text-sm mb-4">Mulai dengan membuat lokasi pertama</p>
                                        <button @click="openCreateModal()"
                                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                            </svg>
                                            Tambah Lokasi
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
            @if ($locations->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $locations->links() }}
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
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">

                    <form :action="modalMode === 'create' ? '{{ route('master-data.general.locations.store') }}' : '{{ url('/master-data/general/locations') }}/' + editLocationId" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="modalMode === 'edit' ? 'PUT' : 'POST'">

                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white" id="modal-title" 
                                            x-text="modalMode === 'create' ? 'Tambah Lokasi Baru' : 'Edit Lokasi'"></h3>
                                        <p class="text-sm text-indigo-100" 
                                            x-text="modalMode === 'create' ? 'Buat lokasi baru dengan koordinat dan geo-fence' : 'Perbarui informasi lokasi'"></p>
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Location Name --}}
                                <div class="md:col-span-2">
                                    <label for="location_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nama Lokasi <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="location_name" required x-model="locationName"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Contoh: Kantor Pusat Jakarta">
                                </div>

                                {{-- Location Code --}}
                                <div>
                                    <label for="location_code" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Kode Lokasi <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="code" id="location_code" required x-model="locationCode"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Contoh: JKT-HQ">
                                </div>

                                {{-- Location Type --}}
                                <div>
                                    <label for="location_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Tipe Lokasi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="type" id="location_type" required x-model="locationType"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all @error('type') border-red-500 @enderror">
                                        <option value="">Pilih Tipe</option>
                                        <option value="office" {{ old('type') == 'office' ? 'selected' : '' }}>Kantor</option>
                                        <option value="warehouse" {{ old('type') == 'warehouse' ? 'selected' : '' }}>Gudang</option>
                                        <option value="store" {{ old('type') == 'store' ? 'selected' : '' }}>Retail</option>
                                        <option value="bin" {{ old('type') == 'bin' ? 'selected' : '' }}>Bin/Rak</option>
                                    </select>
                                    @error('type')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Parent Location --}}
                                <div class="md:col-span-2">
                                    <label for="parent_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Induk Lokasi <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                                    </label>
                                    <select name="parent_id" id="parent_id" x-model="parentId"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                        <option value="">Tidak ada (Lokasi Utama)</option>
                                        @foreach($locations->whereNull('parent_id') as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->code }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Address --}}
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Alamat Lengkap
                                    </label>
                                    <textarea name="address" id="address" rows="2" x-model="address"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="Masukkan alamat lengkap lokasi..."></textarea>
                                </div>

                                {{-- Latitude --}}
                                <div>
                                    <label for="latitude" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Latitude
                                    </label>
                                    <input type="text" name="latitude" id="latitude" x-model="latitude"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="-6.200000">
                                    <p class="mt-1.5 text-xs text-gray-500">Koordinat lintang (latitude)</p>
                                </div>

                                {{-- Longitude --}}
                                <div>
                                    <label for="longitude" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Longitude
                                    </label>
                                    <input type="text" name="longitude" id="longitude" x-model="longitude"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="106.816666">
                                    <p class="mt-1.5 text-xs text-gray-500">Koordinat bujur (longitude)</p>
                                </div>

                                {{-- Radius --}}
                                <div>
                                    <label for="radius_meters" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Radius Geo-fence (meter)
                                    </label>
                                    <input type="number" name="radius_meters" id="radius_meters" x-model="radiusMeters" min="0"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                                        placeholder="100">
                                    <p class="mt-1.5 text-xs text-gray-500">Area valid untuk absensi</p>
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label for="is_active" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Status
                                    </label>
                                    <select name="is_active" id="is_active" x-model="isActive"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                                        <option value="1">Aktif</option>
                                        <option value="0">Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Get Current Location Button --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-blue-900 mb-2">Dapatkan Lokasi Saat Ini</h4>
                                        <button type="button" @click="getCurrentLocation()"
                                            class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            Gunakan Lokasi Saya
                                        </button>
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
                                    <span x-text="modalMode === 'create' ? 'Simpan Lokasi' : 'Update Lokasi'"></span>
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
            let locationManagerInstance = null;

            function locationManager() {
                return {
                    showModal: false,
                    viewMode: 'tree',
                    modalMode: 'create',
                    editLocationId: null,
                    locationName: '',
                    locationCode: '',
                    locationType: '',
                    address: '',
                    latitude: '',
                    longitude: '',
                    radiusMeters: '',
                    isActive: 1,
                    parentId: '',
                    allExpanded: false,

                    init() {
                        locationManagerInstance = this;
                    },

                    toggleView() {
                        this.viewMode = this.viewMode === 'tree' ? 'list' : 'tree';
                    },

                    openCreateModal() {
                        this.modalMode = 'create';
                        this.locationName = '';
                        this.locationCode = '';
                        this.locationType = '';
                        this.address = '';
                        this.latitude = '';
                        this.longitude = '';
                        this.radiusMeters = '';
                        this.isActive = 1;
                        this.parentId = '';
                        this.showModal = true;
                    },

                    openEditModal(id, name, code, type, address, lat, lng, radius, active, parentId) {
                        this.modalMode = 'edit';
                        this.editLocationId = id;
                        this.locationName = name;
                        this.locationCode = code;
                        this.locationType = type;
                        this.address = address || '';
                        this.latitude = lat || '';
                        this.longitude = lng || '';
                        this.radiusMeters = radius || '';
                        this.isActive = active ? 1 : 0;
                        this.parentId = parentId || '';
                        this.showModal = true;
                    },

                    closeModal() {
                        this.showModal = false;
                        setTimeout(() => {
                            this.modalMode = 'create';
                            this.editLocationId = null;
                            this.locationName = '';
                            this.locationCode = '';
                            this.locationType = '';
                            this.address = '';
                            this.latitude = '';
                            this.longitude = '';
                            this.radiusMeters = '';
                            this.isActive = 1;
                            this.parentId = '';
                        }, 300);
                    },

                    getCurrentLocation() {
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    this.latitude = position.coords.latitude.toFixed(6);
                                    this.longitude = position.coords.longitude.toFixed(6);
                                    alert('Lokasi berhasil didapatkan!');
                                },
                                (error) => {
                                    alert('Gagal mendapatkan lokasi: ' + error.message);
                                }
                            );
                        } else {
                            alert('Browser Anda tidak mendukung Geolocation');
                        }
                    },

                    showOnMap(lat, lng) {
                        window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
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

            // Global functions for tree/list view buttons
            window.openEditModalFromTree = function(id, name, code, type, address, lat, lng, radius, active, parentId) {
                if (locationManagerInstance) {
                    locationManagerInstance.openEditModal(id, name, code, type, address, lat, lng, radius, active, parentId);
                }
            };

            window.openEditModalFromList = function(id, name, code, type, address, lat, lng, radius, active, parentId) {
                if (locationManagerInstance) {
                    locationManagerInstance.openEditModal(id, name, code, type, address, lat, lng, radius, active, parentId);
                }
            };

            window.openAddSubLocationModal = function(parentId, parentName) {
                if (locationManagerInstance) {
                    locationManagerInstance.openCreateModal();
                    
                    setTimeout(() => {
                        locationManagerInstance.parentId = parentId;
                        const parentSelect = document.getElementById('parent_id');
                        if (parentSelect) {
                            parentSelect.value = parentId;
                        }
                    }, 100);
                }
            };

            function tableFilters() {
                return {
                    searchQuery: '',
                    typeFilter: 'all',
                    statusFilter: 'all',
                    sortBy: 'name-asc',
                    noResults: false,
                    sortOptions: {
                        'name-asc': 'Nama (A-Z)',
                        'name-desc': 'Nama (Z-A)',
                        'newest': 'Terbaru'
                    },

                    get typeFilterLabel() {
                        const labels = {
                            'all': 'Semua Tipe',
                            'office': 'Kantor',
                            'warehouse': 'Gudang',
                            'store': 'Retail',
                            'bin': 'Bin/Rak'
                        };
                        return labels[this.typeFilter] || 'Semua Tipe';
                    },

                    filterTable() {
                        const rows = document.querySelectorAll('.location-row');
                        let visibleCount = 0;

                        rows.forEach(row => {
                            const name = row.dataset.locationName || '';
                            const code = row.dataset.locationCode || '';
                            const type = row.dataset.locationType || '';
                            const status = row.dataset.locationStatus || '';
                            const searchTerm = this.searchQuery.toLowerCase();

                            const matchesSearch = !searchTerm || 
                                name.includes(searchTerm) || 
                                code.includes(searchTerm);

                            const matchesType = this.typeFilter === 'all' || 
                                type === this.typeFilter;

                            const matchesStatus = this.statusFilter === 'all' || 
                                status === this.statusFilter;

                            if (matchesSearch && matchesType && matchesStatus) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });

                        this.noResults = visibleCount === 0;
                        
                        const emptyState = document.getElementById('emptyState');
                        if (emptyState && (this.searchQuery || this.typeFilter !== 'all' || this.statusFilter !== 'all')) {
                            emptyState.style.display = 'none';
                        }

                        this.sortTable();
                    },

                    sortTable() {
                        const tbody = document.getElementById('locationsTable');
                        if (!tbody) return;
                        
                        const rows = Array.from(tbody.querySelectorAll('.location-row'));
                        
                        rows.sort((a, b) => {
                            switch(this.sortBy) {
                                case 'newest':
                                    return parseInt(b.dataset.locationDate || 0) - parseInt(a.dataset.locationDate || 0);
                                case 'name-asc':
                                    return (a.dataset.locationName || '').localeCompare(b.dataset.locationName || '');
                                case 'name-desc':
                                    return (b.dataset.locationName || '').localeCompare(a.dataset.locationName || '');
                                default:
                                    return 0;
                            }
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    },

                    resetFilters() {
                        this.searchQuery = '';
                        this.typeFilter = 'all';
                        this.statusFilter = 'all';
                        this.sortBy = 'name-asc';
                        this.filterTable();
                    },

                    toggleAllCheckboxes(event) {
                        const checkboxes = document.querySelectorAll('.row-checkbox');
                        checkboxes.forEach(checkbox => {
                            const row = checkbox.closest('.location-row');
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