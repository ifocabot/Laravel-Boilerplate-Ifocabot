@extends('layouts.admin')

@section('title', 'Access Control Dashboard')

@section('content')
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Dashboard Kontrol Akses</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Ringkasan dan aktivitas sistem manajemen akses pengguna
                    <span class="inline-flex items-center gap-1 ml-2">
                        <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                        <span class="text-xs">Live</span>
                    </span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.location.reload()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
                <a href="{{ route('access-control.audit-logs.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Lihat Semua Log
                </a>
            </div>
        </div>

        {{-- Main Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Users --}}
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg hover:shadow-xl transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-indigo-100">Total Pengguna</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold">{{ $stats['users_count'] }}</h3>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-indigo-100">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                12%
                            </span>
                        </div>
                    </div>
                    <div class="bg-white/20 rounded-xl p-3">
                        <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('access-control.users.index') }}" 
                    class="inline-flex items-center gap-1 text-sm text-indigo-100 hover:text-white transition-colors">
                    Lihat semua
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- Total Roles --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Peran</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['roles_count'] }}</h3>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-green-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                8%
                            </span>
                        </div>
                    </div>
                    <div class="bg-purple-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('access-control.roles.index') }}" 
                    class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-indigo-600 transition-colors">
                    Kelola peran
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- Total Permissions --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Izin</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['permissions_count'] }}</h3>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-gray-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14" />
                                </svg>
                                0%
                            </span>
                        </div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                </div>
                <a href="{{ route('access-control.permissions.index') }}" 
                    class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-indigo-600 transition-colors">
                    Kelola izin
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            {{-- Active Sessions --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sesi Aktif</p>
                        <div class="flex items-baseline gap-2 mt-2">
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['active_sessions'] ?? 24 }}</h3>
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-green-600">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                Online
                            </span>
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-center gap-1 text-sm text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Update 2 menit lalu
                </div>
            </div>
        </div>

        {{-- Charts & Tables Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Role Distribution Chart --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Distribusi Peran</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Total pengguna per peran</p>
                    </div>
                    <button class="p-2 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div id="roleDistributionChart"></div>
                </div>
            </div>

            {{-- Permission Usage Chart --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Penggunaan Izin</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Top 5 izin yang paling banyak digunakan</p>
                    </div>
                    <button class="p-2 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <div id="permissionUsageChart"></div>
                </div>
            </div>
        </div>

        {{-- Recent Users & Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Users --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Pengguna Terbaru</h3>
                        <p class="text-sm text-gray-500 mt-0.5">5 pengguna yang baru terdaftar</p>
                    </div>
                    <a href="{{ route('access-control.users.index') }}"
                        class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Pengguna</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Peran</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Status</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50 text-right">
                                    Bergabung</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentUsers as $user)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($user->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($user->roles->first())
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                {{ $user->roles->first()->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada role</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $user->is_active ?? true ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ?? true ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                            {{ $user->is_active ?? true ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="text-sm text-gray-900">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $user->created_at ? $user->created_at->diffForHumans() : '-' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500">Belum ada pengguna terbaru</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Activity Logs --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h3>
                        <p class="text-sm text-gray-500 mt-0.5">Log aktivitas sistem terkini</p>
                    </div>
                    <a href="{{ route('access-control.audit-logs.index') }}"
                        class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50">User</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Event</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50">Model</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase bg-gray-50 text-right">
                                    Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentActivities as $activity)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-xs">
                                                {{ strtoupper(substr($activity->user->name ?? 'SY', 0, 2)) }}
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ Str::limit($activity->user->name ?? 'System', 15) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if($activity->event == 'created')
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Created
                                            </span>
                                        @elseif($activity->event == 'updated')
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Updated
                                            </span>
                                        @elseif($activity->event == 'deleted')
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Deleted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-700 border border-gray-100">
                                                {{ ucfirst($activity->event) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded bg-indigo-50 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-xs font-medium text-gray-900">{{ class_basename($activity->auditable_type) }}</div>
                                                <div class="text-xs text-gray-500">#{{ $activity->auditable_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="text-xs text-gray-900">{{ $activity->created_at->format('H:i') }}</div>
                                        <div class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm text-gray-500">Belum ada aktivitas</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold mb-2">Aksi Cepat</h3>
                    <p class="text-indigo-100 mb-6">Kelola sistem kontrol akses dengan cepat</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('access-control.users.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Tambah Pengguna
                        </a>
                        <a href="{{ route('access-control.roles.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Buat Peran
                        </a>
                        <a href="{{ route('access-control.permissions.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Kelola Izin
                        </a>
                        <a href="{{ route('access-control.audit-logs.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Lihat Audit Log
                        </a>
                    </div>
                </div>
                <div class="hidden lg:block">
                    <svg class="w-48 h-48 text-white/10" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Role Distribution Chart
                var roleOptions = {
                    series: @json($roleDistribution->pluck('count')),
                    chart: {
                        type: 'donut',
                        height: 300,
                        fontFamily: 'Inter, sans-serif'
                    },
                    labels: @json($roleDistribution->pluck('name')),
                    colors: ['#6366f1', '#a855f7', '#ec4899', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Pengguna',
                                        fontSize: '14px',
                                        fontWeight: 600,
                                        color: '#374151',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                        }
                                    },
                                    value: {
                                        fontSize: '24px',
                                        fontWeight: 700,
                                        color: '#111827'
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: { 
                        enabled: false 
                    },
                    legend: { 
                        position: 'bottom',
                        fontSize: '13px',
                        fontWeight: 500,
                        labels: {
                            colors: '#6B7280'
                        },
                        markers: {
                            width: 12,
                            height: 12,
                            radius: 4
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) { 
                                return val + " Pengguna" 
                            }
                        }
                    },
                    stroke: {
                        width: 2,
                        colors: ['#fff']
                    }
                };

                var roleChart = new ApexCharts(document.querySelector("#roleDistributionChart"), roleOptions);
                roleChart.render();

                // Permission Usage Chart
                var permissionOptions = {
                    series: [{
                        name: 'Penggunaan',
                        data: [85, 72, 68, 54, 45]
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: {
                            show: false
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 8,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        offsetX: 30,
                        style: {
                            fontSize: '12px',
                            fontWeight: 600,
                            colors: ['#6366f1']
                        },
                        formatter: function (val) {
                            return val + "%";
                        }
                    },
                    colors: ['#6366f1'],
                    xaxis: {
                        categories: ['View Users', 'Edit Users', 'Delete Users', 'Manage Roles', 'View Reports'],
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontWeight: 500,
                                colors: '#6B7280'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontWeight: 500,
                                colors: '#374151'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#E5E7EB',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val + "% digunakan";
                            }
                        }
                    }
                };

                var permissionChart = new ApexCharts(document.querySelector("#permissionUsageChart"), permissionOptions);
                permissionChart.render();
            });
        </script>
    @endpush
@endsection