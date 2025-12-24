@extends('layouts.admin')

@section('title', 'Employee Self-Service')

@section('content')
    <div class="space-y-6">
        {{-- Welcome Banner --}}
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Selamat Datang, {{ $employee->full_name }}!</h1>
                    <p class="text-indigo-100 mt-1">{{ $employee->current_position?->name ?? 'Karyawan' }} •
                        {{ $employee->current_department?->name ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-indigo-200 text-sm">{{ now()->translatedFormat('l, d F Y') }}</p>
                    <p class="text-2xl font-bold mt-1">{{ now()->format('H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('ess.leave.create') }}"
                class="bg-white rounded-xl p-4 shadow-sm border hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">Ajukan Cuti</h3>
                <p class="text-xs text-gray-500 mt-1">Pengajuan cuti baru</p>
            </a>
            <a href="{{ route('ess.payroll.index') }}"
                class="bg-white rounded-xl p-4 shadow-sm border hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">Slip Gaji</h3>
                <p class="text-xs text-gray-500 mt-1">Lihat slip gaji</p>
            </a>
            <a href="{{ route('ess.profile.index') }}"
                class="bg-white rounded-xl p-4 shadow-sm border hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">Profil Saya</h3>
                <p class="text-xs text-gray-500 mt-1">Lihat & edit profil</p>
            </a>
            <a href="{{ route('ess.leave.index') }}"
                class="bg-white rounded-xl p-4 shadow-sm border hover:shadow-md transition-all group">
                <div
                    class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-yellow-200 transition-colors">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">Riwayat Cuti</h3>
                <p class="text-xs text-gray-500 mt-1">Status pengajuan</p>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Announcements --}}
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Pengumuman</h2>
                @forelse($announcements as $announcement)
                    <a href="{{ route('ess.announcements.show', $announcement) }}"
                        class="block bg-white rounded-xl p-4 shadow-sm border hover:shadow-md transition-shadow {{ $announcement->is_pinned ? 'ring-2 ring-indigo-500' : '' }}">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center {{ $announcement->type_badge_class }}">
                                {!! $announcement->type_icon !!}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900 hover:text-indigo-600">{{ $announcement->title }}</h3>
                                    @if($announcement->is_pinned)
                                        <span
                                            class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded-full">Pinned</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">{!! Str::limit(strip_tags($announcement->content), 100) !!}</p>
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ $announcement->published_at?->diffForHumans() ?? $announcement->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @empty
                    <div class="bg-white rounded-xl p-8 shadow-sm border text-center">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                        <p class="text-gray-500">Belum ada pengumuman</p>
                    </div>
                @endforelse
            </div>

            {{-- Leave Balance & Recent Requests --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-900">Saldo Cuti</h2>
                <div class="bg-white rounded-xl shadow-sm border divide-y">
                    @forelse($leaveBalances as $balance)
                        <div class="p-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $balance->leaveType->name }}</p>
                                <p class="text-xs text-gray-500">Terpakai: {{ $balance->used }} hari</p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-indigo-600">{{ $balance->remaining }}</span>
                                <p class="text-xs text-gray-500">hari tersisa</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500 text-sm">Tidak ada data saldo cuti</div>
                    @endforelse
                </div>

                <h2 class="text-lg font-semibold text-gray-900 mt-6">Pengajuan Terakhir</h2>
                <div class="bg-white rounded-xl shadow-sm border divide-y">
                    @forelse($recentLeaveRequests as $request)
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-medium text-gray-900">{{ $request->leaveType->name }}</span>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                    @if($request->status === 'approved') bg-green-100 text-green-700
                                    @elseif($request->status === 'rejected') bg-red-100 text-red-700
                                    @elseif($request->status === 'cancelled') bg-gray-100 text-gray-600
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $request->start_date->format('d M') }} -
                                {{ $request->end_date->format('d M Y') }}</p>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500 text-sm">Belum ada pengajuan</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection