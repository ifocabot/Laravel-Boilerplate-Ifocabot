@extends('layouts.admin')

@section('title', 'Kelola Shift Kerja')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Shift Kerja</h1>
                <p class="text-sm text-gray-500 mt-1">Atur jadwal shift untuk attendance karyawan</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hris.attendance.shifts.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Shift
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Shift</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalShifts }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Shift Aktif</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $activeShifts }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Fixed Shift</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $fixedShifts }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Flexible Shift</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $flexibleShifts }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="GET" action="{{ route('hris.attendance.shifts.index') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Shift</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nama atau kode shift..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div class="flex-1">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipe Shift</label>
                    <select name="type" id="type" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Semua Tipe</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Fixed</option>
                        <option value="flexible" {{ request('type') == 'flexible' ? 'selected' : '' }}>Flexible</option>
                    </select>
                </div>

                <div class="flex-1">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Filter
                </button>

                @if(request('search') || request('type') || request('status'))
                    <a href="{{ route('hris.attendance.shifts.index') }}"
                        class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Shifts List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Daftar Shift ({{ $shifts->total() }})</h2>
            </div>

            @if($shifts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Shift
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Tipe
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Jam Kerja
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Istirahat
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Durasi Wajib
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Toleransi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($shifts as $shift)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $shift->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $shift->code }}</p>
                                                @if($shift->is_overnight)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700 mt-1">
                                                        Overnight
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $shift->type_badge_class }}">
                                            {{ $shift->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $shift->time_range }}</p>
                                        <p class="text-xs text-gray-500">{{ $shift->formatted_start_time }} - {{ $shift->formatted_end_time }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($shift->break_start && $shift->break_end)
                                            <p class="text-sm text-gray-900">
                                                {{ Carbon\Carbon::parse($shift->break_start)->format('H:i') }} - 
                                                {{ Carbon\Carbon::parse($shift->break_end)->format('H:i') }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $shift->break_duration }} menit</p>
                                        @else
                                            <p class="text-sm text-gray-400">-</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $shift->formatted_work_hours }}</p>
                                        <p class="text-xs text-gray-500">{{ $shift->work_hours_required }} menit</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ $shift->late_tolerance_minutes }} menit</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $shift->status_badge_class }}">
                                            {{ $shift->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('hris.attendance.shifts.edit', $shift->id) }}"
                                                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <form action="{{ route('hris.attendance.shifts.destroy', $shift->id) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus shift ini?');">
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $shifts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium mb-1">Belum ada shift</p>
                    <p class="text-gray-400 text-sm mb-4">Mulai dengan membuat shift pertama</p>
                    <a href="{{ route('hris.attendance.shifts.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Shift
                    </a>
                </div>
            @endif
        </div>

        {{-- Toast Notification --}}
        @if(session('success') || session('error') || session('info'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed bottom-4 right-4 z-50 flex items-center w-full max-w-xs p-4 text-gray-500 bg-white rounded-xl shadow-lg border border-gray-100"
                role="alert">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg
                    {{ session('success') ? 'text-green-500 bg-green-100' : '' }}
                    {{ session('error') ? 'text-red-500 bg-red-100' : '' }}
                    {{ session('info') ? 'text-blue-500 bg-blue-100' : '' }}">
                    @if(session('success'))
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    @elseif(session('error'))
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                </div>
                <div class="ml-3 text-sm font-medium text-gray-700">
                    {{ session('success') ?? session('error') ?? session('info') }}
                </div>
                <button type="button" @click="show = false"
                    class="ml-auto -mx-1.5 -my-1.5 bg-white text-gray-400 hover:text-gray-900 rounded-lg p-1.5 hover:bg-gray-50 inline-flex items-center justify-center h-8 w-8">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 14 14" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endsection