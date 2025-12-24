@extends('layouts.admin')

@section('title', 'Detail Attendance Log')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.attendance.logs.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Attendance Log</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $log->formatted_date }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Employee Info --}}
            <div class="space-y-6">
                {{-- Employee Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Karyawan</h3>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-xl font-bold text-indigo-600">
                                {{ strtoupper(substr($log->employee->full_name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-900">{{ $log->employee->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $log->employee->nik }}</p>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Departemen:</span>
                            <span class="font-medium text-gray-900">{{ $log->employee->currentCareer?->department?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Posisi:</span>
                            <span class="font-medium text-gray-900">{{ $log->employee->currentCareer?->position?->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Shift Info --}}
                @if($log->shift)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Shift</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nama Shift:</span>
                                <span class="font-medium text-gray-900">{{ $log->shift->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Jam Kerja:</span>
                                <span class="font-medium text-gray-900">{{ $log->shift->time_range }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Durasi Kerja:</span>
                                <span class="font-medium text-gray-900">{{ $log->shift->work_hours_required }} jam</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Location Info --}}
                @if($log->location)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Lokasi Kerja</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nama:</span>
                                <span class="font-medium text-gray-900">{{ $log->location->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Radius:</span>
                                <span class="font-medium text-gray-900">{{ $log->location->geofence_radius }}m</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column - Attendance Details --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Clock In Details --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">Clock In</h3>
                    </div>
                    <div class="p-6">
                        @if($log->has_clocked_in)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Time Info --}}
                                <div>
                                    <p class="text-xs text-gray-500 mb-2">Waktu Clock In</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $log->formatted_clock_in_time }}</p>
                                    @if($log->is_late)
                                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-700">
                                            Terlambat {{ $log->late_duration_minutes }} menit
                                        </div>
                                    @else
                                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                                            Tepat Waktu
                                        </div>
                                    @endif
                                </div>

                                {{-- Location Info --}}
                                <div>
                                    <p class="text-xs text-gray-500 mb-2">Lokasi</p>
                                    <div class="space-y-1 text-sm">
                                        <p class="text-gray-900">
                                            <span class="font-medium">Latitude:</span> {{ $log->clock_in_latitude }}
                                        </p>
                                        <p class="text-gray-900">
                                            <span class="font-medium">Longitude:</span> {{ $log->clock_in_longitude }}
                                        </p>
                                        @if($log->clock_in_latitude && $log->clock_in_longitude)
                                            <a href="https://www.google.com/maps?q={{ $log->clock_in_latitude }},{{ $log->clock_in_longitude }}" 
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 mt-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Lihat di Google Maps
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- Photo --}}
                                @if($log->clock_in_photo)
                                    <div class="md:col-span-2">
                                        <p class="text-xs text-gray-500 mb-2">Foto Clock In</p>
                                        <img src="{{ Storage::url($log->clock_in_photo) }}" 
                                            alt="Clock In Photo"
                                            class="w-full md:w-1/2 rounded-xl border border-gray-200 shadow-sm">
                                    </div>
                                @endif

                                {{-- Notes --}}
                                @if($log->clock_in_notes)
                                    <div class="md:col-span-2">
                                        <p class="text-xs text-gray-500 mb-2">Catatan</p>
                                        <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-xl">{{ $log->clock_in_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-400 text-sm">Belum clock in</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Clock Out Details --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">Clock Out</h3>
                    </div>
                    <div class="p-6">
                        @if($log->has_clocked_out)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Time Info --}}
                                <div>
                                    <p class="text-xs text-gray-500 mb-2">Waktu Clock Out</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $log->formatted_clock_out_time }}</p>
                                    @if($log->is_early_out)
                                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-700">
                                            Pulang Awal
                                        </div>
                                    @else
                                        <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700">
                                            Sesuai Jadwal
                                        </div>
                                    @endif
                                </div>

                                {{-- Location Info --}}
                                <div>
                                    <p class="text-xs text-gray-500 mb-2">Lokasi</p>
                                    <div class="space-y-1 text-sm">
                                        <p class="text-gray-900">
                                            <span class="font-medium">Latitude:</span> {{ $log->clock_out_latitude }}
                                        </p>
                                        <p class="text-gray-900">
                                            <span class="font-medium">Longitude:</span> {{ $log->clock_out_longitude }}
                                        </p>
                                        @if($log->clock_out_latitude && $log->clock_out_longitude)
                                            <a href="https://www.google.com/maps?q={{ $log->clock_out_latitude }},{{ $log->clock_out_longitude }}" 
                                                target="_blank"
                                                class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-700 mt-2">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                Lihat di Google Maps
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- Photo --}}
                                @if($log->clock_out_photo)
                                    <div class="md:col-span-2">
                                        <p class="text-xs text-gray-500 mb-2">Foto Clock Out</p>
                                        <img src="{{ Storage::url($log->clock_out_photo) }}" 
                                            alt="Clock Out Photo"
                                            class="w-full md:w-1/2 rounded-xl border border-gray-200 shadow-sm">
                                    </div>
                                @endif

                                {{-- Notes --}}
                                @if($log->clock_out_notes)
                                    <div class="md:col-span-2">
                                        <p class="text-xs text-gray-500 mb-2">Catatan</p>
                                        <p class="text-sm text-gray-900 bg-gray-50 p-4 rounded-xl">{{ $log->clock_out_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8">
                                <p class="text-gray-400 text-sm">Belum clock out</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Work Duration Summary --}}
                @if($log->has_clocked_out)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Ringkasan Kerja</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Total Work Duration --}}
                            <div class="text-center p-4 bg-indigo-50 rounded-xl">
                                <p class="text-xs text-gray-500 mb-2">Total Durasi Kerja</p>
                                <p class="text-2xl font-bold text-indigo-600">{{ $log->formatted_work_duration }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $log->work_duration_hours }} jam</p>
                            </div>

                            {{-- Late Duration --}}
                            <div class="text-center p-4 bg-orange-50 rounded-xl">
                                <p class="text-xs text-gray-500 mb-2">Keterlambatan</p>
                                <p class="text-2xl font-bold text-orange-600">
                                    {{ $log->late_duration_minutes > 0 ? $log->late_duration_minutes . ' menit' : '-' }}
                                </p>
                            </div>

                            {{-- Overtime --}}
                            <div class="text-center p-4 bg-blue-50 rounded-xl">
                                <p class="text-xs text-gray-500 mb-2">Overtime</p>
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ $log->overtime_minutes > 0 ? round($log->overtime_minutes / 60, 1) . ' jam' : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection