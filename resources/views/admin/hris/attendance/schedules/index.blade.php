@extends('layouts.admin')

@section('title', 'Roster Kerja Karyawan')

@section('content')
    <div class="space-y-6" x-data="scheduleCalendar()">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Roster Kerja Karyawan</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola jadwal shift karyawan</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="openBulkGenerateModal()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Generate Bulk
                </button>
                <button @click="openHolidayModal()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mark Holiday
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Jadwal</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalSchedules }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Hari Kerja</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $workingDays }}</h3>
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
                        <p class="text-sm font-medium text-gray-500">Hari Libur</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $dayOffs }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Libur Nasional</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $holidays }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters & Month Navigation --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                {{-- Month Navigation --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('hris.attendance.schedules.index', array_merge(request()->all(), ['month' => $month - 1 <= 0 ? 12 : $month - 1, 'year' => $month - 1 <= 0 ? $year - 1 : $year])) }}"
                        class="inline-flex items-center justify-center w-10 h-10 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <div class="text-center">
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
                        </h2>
                    </div>

                    <a href="{{ route('hris.attendance.schedules.index', array_merge(request()->all(), ['month' => $month + 1 > 12 ? 1 : $month + 1, 'year' => $month + 1 > 12 ? $year + 1 : $year])) }}"
                        class="inline-flex items-center justify-center w-10 h-10 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                    <a href="{{ route('hris.attendance.schedules.index', array_merge(request()->all(), ['year' => now()->year, 'month' => now()->month])) }}"
                        class="ml-3 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        Bulan Ini
                    </a>
                </div>

                {{-- Filters --}}
                <form method="GET" class="flex items-end gap-3">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">

                    <div class="flex-1 min-w-[200px]">
                        <select name="employee_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Semua Karyawan</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }} ({{ $emp->nik }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[180px]">
                        <select name="department_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-1 min-w-[150px]">
                        <select name="shift_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Semua Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Filter
                    </button>

                    @if(request('employee_id') || request('department_id') || request('shift_id'))
                        <a href="{{ route('hris.attendance.schedules.index', ['year' => $year, 'month' => $month]) }}"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Calendar View --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th
                                class="sticky left-0 z-10 bg-gray-50 px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-200 min-w-[200px]">
                                Karyawan
                            </th>
                            @foreach($dates as $date)
                                <th
                                    class="px-3 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider border-r border-gray-100 min-w-[90px]
                                                                                                {{ $date->isWeekend() ? 'bg-red-50' : '' }}
                                                                                                {{ $date->isToday() ? 'bg-indigo-50' : '' }}">
                                    <div>{{ $date->format('d') }}</div>
                                    <div class="text-[10px] font-normal text-gray-500 mt-0.5">
                                        {{ $date->translatedFormat('D') }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="sticky left-0 z-10 bg-white px-4 py-3 border-r border-gray-200">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $employee->full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $employee->nik }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ $employee->currentCareer?->department?->name ?? '-' }}
                                        </p>
                                    </div>
                                </td>
                                @foreach($dates as $date)
                                    @php
                                        $dateStr = $date->format('Y-m-d');
                                        $employeeSchedules = $schedules->get($employee->id);
                                        $schedule = $employeeSchedules
                                            ? $employeeSchedules->first(fn($s) => $s->date->format('Y-m-d') === $dateStr)
                                            : null;
                                    @endphp
                                    <td
                                        class="px-1 py-1 text-center border-r border-gray-100 group
                                                                                                                                            {{ $date->isWeekend() ? 'bg-red-50' : '' }} 
                                                                                                                                            {{ $date->isToday() ? 'ring-2 ring-indigo-500 ring-inset' : '' }}">
                                        <button
                                            @click="openScheduleModal({{ $employee->id }}, '{{ $employee->full_name }}', '{{ $date->format('Y-m-d') }}', {{ $schedule ? $schedule->id : 'null' }})"
                                            type="button"
                                            class="w-full h-full min-h-[75px] p-1.5 rounded-lg transition-all
                                                                                                                                                    {{ $schedule ? 'hover:bg-gray-100 hover:shadow-sm' : 'hover:bg-indigo-50 hover:border hover:border-indigo-300' }}">
                                            @if($schedule)
                                                {{-- HAS SCHEDULE --}}
                                                <div class="space-y-1">
                                                    @if($schedule->is_leave)
                                                        {{-- APPROVED LEAVE --}}
                                                        <div
                                                            class="text-[10px] font-bold text-yellow-700 bg-yellow-100 rounded-md px-2 py-1.5 border border-yellow-300">
                                                            <div class="flex items-center justify-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                <span>CUTI</span>
                                                            </div>
                                                            @php $leaveReq = $schedule->getApprovedLeaveRequest(); @endphp
                                                            @if($leaveReq && $leaveReq->leaveType)
                                                                <div class="text-[8px] mt-0.5 text-yellow-600">{{ $leaveReq->leaveType->name }}</div>
                                                            @endif
                                                        </div>
                                                    @elseif($schedule->is_holiday)
                                                        <div
                                                            class="text-[10px] font-bold text-red-700 bg-red-100 rounded-md px-2 py-1.5 border border-red-200">
                                                            <div class="flex items-center justify-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <span>LIBUR</span>
                                                            </div>
                                                        </div>
                                                    @elseif($schedule->is_day_off)
                                                        <div
                                                            class="text-[10px] font-bold text-gray-700 bg-gray-200 rounded-md px-2 py-1.5 border border-gray-300">
                                                            <div class="flex items-center justify-center gap-1">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                                </svg>
                                                                <span>OFF</span>
                                                            </div>
                                                        </div>
                                                    @elseif($schedule->shift)
                                                        <div class="bg-indigo-100 rounded-md px-2 py-1.5 border border-indigo-200">
                                                            <div class="text-[11px] font-bold text-indigo-800">
                                                                {{ $schedule->shift->code }}
                                                            </div>
                                                            <div class="text-[9px] text-indigo-600 font-medium mt-0.5">
                                                                {{ $schedule->shift->formatted_start_time }}
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Notes (if exists) --}}
                                                    @if($schedule->notes)
                                                        <div class="text-[8px] text-gray-500 truncate px-1 mt-1"
                                                            title="{{ $schedule->notes }}">
                                                            {{ Str::limit($schedule->notes, 15) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                {{-- NO SCHEDULE --}}
                                                <div
                                                    class="flex flex-col items-center justify-center h-full py-3 text-gray-300 group-hover:text-indigo-500 transition-colors">
                                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    <span class="text-[8px] mt-1 group-hover:font-medium">Tambah</span>
                                                </div>
                                            @endif
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dates) + 1 }}" class="px-6 py-12 text-center">
                                    <div class="text-gray-400 text-sm">Tidak ada karyawan yang ditampilkan</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Legend --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-900 mb-3">Keterangan</h3>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-indigo-100 border border-indigo-200 rounded"></div>
                    <span class="text-sm text-gray-600">Working Day</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gray-100 border border-gray-200 rounded"></div>
                    <span class="text-sm text-gray-600">Day Off</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-100 border border-red-200 rounded"></div>
                    <span class="text-sm text-gray-600">Libur Nasional</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-red-50 border border-red-100 rounded"></div>
                    <span class="text-sm text-gray-600">Weekend</span>
                </div>
            </div>
        </div>

        {{-- MODAL 1: Schedule Modal (Create/Edit) --}}
        <div x-show="showScheduleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="closeScheduleModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showScheduleModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    @click="closeScheduleModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showScheduleModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form @submit.prevent="saveSchedule()">
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">
                                            <span x-show="selectedScheduleId">Edit Jadwal</span>
                                            <span x-show="!selectedScheduleId">Tambah Jadwal</span>
                                        </h3>
                                        <p class="text-sm text-indigo-100"
                                            x-text="selectedEmployeeName + ' - ' + formatDate(selectedDate)"></p>
                                    </div>
                                </div>
                                <button @click="closeScheduleModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-4">
                            {{-- Shift Selection --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Shift</label>
                                <select x-model="scheduleForm.shift_id"
                                    :disabled="scheduleForm.is_day_off || scheduleForm.is_holiday"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100">
                                    <option value="">Pilih Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">
                                            {{ $shift->name }} ({{ $shift->formatted_start_time }} -
                                            {{ $shift->formatted_end_time }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Day Off Checkbox --}}
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" x-model="scheduleForm.is_day_off"
                                        class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium text-gray-700">Tandai sebagai hari libur (Off)</span>
                                </label>
                            </div>

                            {{-- Holiday Checkbox --}}
                            <div>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" x-model="scheduleForm.is_holiday"
                                        class="w-5 h-5 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700">Tandai sebagai libur nasional</span>
                                </label>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                                <textarea x-model="scheduleForm.notes" rows="3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Catatan tambahan (opsional)"></textarea>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between gap-3 border-t border-gray-200">
                            <button x-show="selectedScheduleId" @click.prevent="deleteSchedule()" type="button"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Hapus
                            </button>
                            <div class="flex items-center gap-3" :class="{'ml-auto': !selectedScheduleId}">
                                <button @click="closeScheduleModal()" type="button"
                                    class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                    <span x-show="selectedScheduleId">Update</span>
                                    <span x-show="!selectedScheduleId">Simpan</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL 2: Bulk Generate Modal --}}
        <div x-show="showBulkGenerateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="closeBulkGenerateModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showBulkGenerateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    @click="closeBulkGenerateModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showBulkGenerateModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">

                    <form @submit.prevent="generateBulk()">
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Generate Jadwal Bulk</h3>
                                        <p class="text-sm text-purple-100">Buat jadwal untuk banyak karyawan sekaligus</p>
                                    </div>
                                </div>
                                <button @click="closeBulkGenerateModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-4">
                            {{-- Employee Selection --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Karyawan <span
                                        class="text-red-500">*</span></label>
                                <div class="border border-gray-300 rounded-xl p-4 max-h-60 overflow-y-auto">
                                    <label class="flex items-center gap-3 mb-3 cursor-pointer">
                                        <input type="checkbox" @change="toggleSelectAllEmployees($event)"
                                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm font-semibold text-gray-900">Pilih Semua</span>
                                    </label>
                                    <div class="space-y-2 border-t border-gray-200 pt-3">
                                        @foreach($employees as $emp)
                                            <label
                                                class="flex items-center gap-3 cursor-pointer hover:bg-gray-50 p-2 rounded-lg">
                                                <input type="checkbox" value="{{ $emp->id }}"
                                                    @change="toggleEmployee({{ $emp->id }})"
                                                    :checked="bulkForm.employee_ids.includes({{ $emp->id }})"
                                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700">{{ $emp->full_name }}
                                                    ({{ $emp->nik }})</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    <span x-text="bulkForm.employee_ids.length"></span> karyawan dipilih
                                </p>
                            </div>

                            {{-- Period --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun <span
                                            class="text-red-500">*</span></label>
                                    <input type="number" x-model="bulkForm.year" required min="2020" max="2100"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan <span
                                            class="text-red-500">*</span></label>
                                    <select x-model="bulkForm.month" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Shift --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Shift <span
                                        class="text-red-500">*</span></label>
                                <select x-model="bulkForm.shift_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Pilih Shift</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->time_range }})</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Info --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-blue-900 mb-1">Informasi</p>
                                        <ul class="text-xs text-blue-700 space-y-1">
                                            <li>• Jadwal akan dibuat sesuai pola hari kerja shift yang dipilih</li>
                                            <li>• Jadwal yang sudah ada tidak akan ditimpa</li>
                                            <li>• Hari di luar pola kerja shift akan otomatis ditandai OFF</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeBulkGenerateModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                Generate Jadwal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- DEBUG INFO - Remove after testing --}}
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
            <h4 class="font-bold text-yellow-900 mb-2">Debug Info:</h4>
            <div class="text-xs text-yellow-800 space-y-1">
                <p><strong>Total Employees:</strong> {{ $employees->count() }}</p>
                <p><strong>Total Schedules in DB:</strong>
                    {{ \App\Models\EmployeeSchedule::whereYear('date', $year)->whereMonth('date', $month)->count() }}</p>
                <p><strong>Schedules Grouped:</strong> {{ $schedules->count() }} groups</p>
                @if($schedules->count() > 0)
                    <p><strong>Sample Employee IDs with schedules:</strong> {{ $schedules->keys()->take(3)->implode(', ') }}</p>
                @endif
            </div>
        </div>
        {{-- MODAL 3: Mark Holiday Modal --}}
        <div x-show="showHolidayModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="closeHolidayModal()">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showHolidayModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    @click="closeHolidayModal()">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div x-show="showHolidayModal" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <form @submit.prevent="markHoliday()">
                        {{-- Modal Header --}}
                        <div class="bg-gradient-to-r from-red-500 to-pink-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">Tandai Hari Libur Nasional</h3>
                                        <p class="text-sm text-red-100">Tandai tanggal tertentu sebagai libur</p>
                                    </div>
                                </div>
                                <button @click="closeHolidayModal()" type="button"
                                    class="text-white/80 hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Modal Body --}}
                        <div class="px-6 py-6 space-y-4">
                            {{-- Date --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal <span
                                        class="text-red-500">*</span></label>
                                <input type="date" x-model="holidayForm.date" required
                                    min="{{ $dates->first()->format('Y-m-d') }}" max="{{ $dates->last()->format('Y-m-d') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent">
                                <p class="mt-1.5 text-xs text-gray-500">Pilih tanggal dalam bulan
                                    {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y') }}
                                </p>
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan <span
                                        class="text-red-500">*</span></label>
                                <input type="text" x-model="holidayForm.notes" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Contoh: Tahun Baru Imlek">
                            </div>

                            {{-- Warning --}}
                            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-orange-900 mb-1">Perhatian</p>
                                        <p class="text-xs text-orange-700">
                                            Ini akan menandai tanggal tersebut sebagai hari libur nasional untuk SEMUA
                                            karyawan yang memiliki jadwal pada tanggal ini.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
                            <button @click="closeHolidayModal()" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                                Tandai Sebagai Libur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function scheduleCalendar() {
                return {
                    showScheduleModal: false,
                    showBulkGenerateModal: false,
                    showHolidayModal: false,
                    selectedEmployeeId: null,
                    selectedEmployeeName: '',
                    selectedDate: null,
                    selectedScheduleId: null,
                    scheduleForm: {
                        employee_id: null,
                        date: null,
                        shift_id: '',
                        is_day_off: false,
                        is_holiday: false,
                        notes: ''
                    },
                    bulkForm: {
                        employee_ids: [],
                        year: {{ $year }},
                        month: {{ $month }},
                        shift_id: ''
                    },
                    holidayForm: {
                        date: '{{ $dates->first()->format('Y-m-d') }}',
                        notes: ''
                    },

                    async openScheduleModal(employeeId, employeeName, date, scheduleId) {
                        this.selectedEmployeeId = employeeId;
                        this.selectedEmployeeName = employeeName;
                        this.selectedDate = date;
                        this.selectedScheduleId = scheduleId;

                        this.scheduleForm = {
                            employee_id: employeeId,
                            date: date,
                            shift_id: '',
                            is_day_off: false,
                            is_holiday: false,
                            notes: ''
                        };

                        if (scheduleId) {
                            try {
                                const response = await fetch(`/hris/attendance/schedules/${scheduleId}`);
                                const data = await response.json();

                                if (data.success) {
                                    this.scheduleForm.shift_id = data.schedule.shift_id || '';
                                    this.scheduleForm.is_day_off = data.schedule.is_day_off;
                                    this.scheduleForm.is_holiday = data.schedule.is_holiday;
                                    this.scheduleForm.notes = data.schedule.notes || '';
                                }
                            } catch (error) {
                                console.error('Error loading schedule:', error);
                            }
                        }

                        this.showScheduleModal = true;
                    },

                    closeScheduleModal() {
                        this.showScheduleModal = false;
                        setTimeout(() => {
                            this.selectedEmployeeId = null;
                            this.selectedEmployeeName = '';
                            this.selectedDate = null;
                            this.selectedScheduleId = null;
                        }, 300);
                    },

                    async saveSchedule() {
                        if (!this.scheduleForm.shift_id && !this.scheduleForm.is_day_off && !this.scheduleForm.is_holiday) {
                            alert('Pilih shift atau tandai sebagai hari libur');
                            return;
                        }

                        // IMPORTANT: Convert to proper boolean
                        const payload = {
                            employee_id: this.scheduleForm.employee_id,
                            date: this.scheduleForm.date,
                            shift_id: this.scheduleForm.shift_id || null,
                            is_day_off: this.scheduleForm.is_day_off === true,
                            is_holiday: this.scheduleForm.is_holiday === true,
                            notes: this.scheduleForm.notes || ''
                        };

                        console.log('Sending payload:', payload); // Debug

                        try {
                            const response = await fetch('{{ route('hris.attendance.schedules.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(payload)
                            });

                            const data = await response.json();
                            console.log('Response:', data); // Debug

                            if (data.success) {
                                this.closeScheduleModal();
                                this.showToast('success', data.message);
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                if (data.errors) {
                                    const errorMessages = Object.values(data.errors).flat().join('\n');
                                    alert(errorMessages);
                                } else {
                                    alert(data.message || 'Terjadi kesalahan');
                                }
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat menyimpan jadwal');
                        }
                    },
                    async deleteSchedule() {
                        if (!confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) return;

                        try {
                            const response = await fetch(`/hris/attendance/schedules/${this.selectedScheduleId}`, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.closeScheduleModal();
                                this.showToast('success', data.message);
                                setTimeout(() => window.location.reload(), 800);
                            } else {
                                alert(data.message || 'Terjadi kesalahan');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat menghapus jadwal');
                        }
                    },

                    openBulkGenerateModal() {
                        this.bulkForm.employee_ids = [];
                        this.bulkForm.shift_id = '';
                        this.showBulkGenerateModal = true;
                    },

                    closeBulkGenerateModal() {
                        this.showBulkGenerateModal = false;
                    },

                    toggleSelectAllEmployees(event) {
                        if (event.target.checked) {
                            this.bulkForm.employee_ids = @json($employees->pluck('id'));
                        } else {
                            this.bulkForm.employee_ids = [];
                        }
                    },

                    toggleEmployee(employeeId) {
                        const index = this.bulkForm.employee_ids.indexOf(employeeId);
                        if (index > -1) {
                            this.bulkForm.employee_ids.splice(index, 1);
                        } else {
                            this.bulkForm.employee_ids.push(employeeId);
                        }
                    },

                    async generateBulk() {
                        if (this.bulkForm.employee_ids.length === 0) {
                            alert('Pilih minimal 1 karyawan');
                            return;
                        }

                        if (!this.bulkForm.shift_id) {
                            alert('Pilih shift');
                            return;
                        }

                        if (!confirm(`Generate jadwal untuk ${this.bulkForm.employee_ids.length} karyawan?`)) {
                            return;
                        }

                        try {
                            const response = await fetch('{{ route('hris.attendance.schedules.generate-bulk') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.bulkForm)
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.closeBulkGenerateModal();
                                this.showToast('success', data.message);
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                alert(data.message || 'Terjadi kesalahan');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat generate jadwal');
                        }
                    },

                    openHolidayModal() {
                        this.holidayForm = {
                            date: '{{ $dates->first()->format('Y-m-d') }}',
                            notes: ''
                        };
                        this.showHolidayModal = true;
                    },

                    closeHolidayModal() {
                        this.showHolidayModal = false;
                    },

                    async markHoliday() {
                        if (!this.holidayForm.date || !this.holidayForm.notes) {
                            alert('Lengkapi semua field');
                            return;
                        }

                        if (!confirm(`Tandai ${this.formatDate(this.holidayForm.date)} sebagai hari libur nasional?`)) {
                            return;
                        }

                        try {
                            const response = await fetch('{{ route('hris.attendance.schedules.mark-holiday') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(this.holidayForm)
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.closeHolidayModal();
                                this.showToast('success', data.message);
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                alert(data.message || 'Terjadi kesalahan');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan');
                        }
                    },

                    formatDate(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                        return date.toLocaleDateString('id-ID', options);
                    },

                    showToast(type, message) {
                        const toast = document.createElement('div');
                        toast.className = `fixed bottom-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-green-600' : 'bg-red-600'
                            } text-white text-sm font-medium`;
                        toast.textContent = message;
                        document.body.appendChild(toast);

                        setTimeout(() => {
                            toast.remove();
                        }, 3000);
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