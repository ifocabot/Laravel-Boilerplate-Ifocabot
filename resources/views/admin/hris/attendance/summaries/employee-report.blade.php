@extends('layouts.admin')

@section('title', 'Laporan Absensi - ' . $employee->full_name)

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('hris.attendance.summaries.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Laporan Absensi Karyawan</h1>
                </div>
                <p class="text-sm text-gray-500 ml-11">{{ $startDate->translatedFormat('F Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>

        {{-- Employee Info Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start gap-6">
                <div
                    class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-2xl font-bold text-white">
                        {{ strtoupper(substr($employee->full_name, 0, 2)) }}
                    </span>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900">{{ $employee->full_name }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <p class="text-xs text-gray-500">NIK</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $employee->nik }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Departemen</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                {{ $employee->currentCareer?->department?->name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Posisi</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                {{ $employee->currentCareer?->position?->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Month Navigation --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <a href="{{ route('hris.attendance.summaries.employee-report', $employee->id) }}?year={{ $month - 1 <= 0 ? $year - 1 : $year }}&month={{ $month - 1 <= 0 ? 12 : $month - 1 }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Bulan Sebelumnya
                </a>

                <h3 class="text-lg font-bold text-gray-900">
                    {{ $startDate->translatedFormat('F Y') }}
                </h3>

                <a href="{{ route('hris.attendance.summaries.employee-report', $employee->id) }}?year={{ $month + 1 > 12 ? $year + 1 : $year }}&month={{ $month + 1 > 12 ? 1 : $month + 1 }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                    Bulan Berikutnya
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Payroll Summary Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Hari</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $payrollSummary['total_days'] }}</h3>
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
                        <p class="text-sm font-medium text-gray-500">Hari Hadir</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">{{ $payrollSummary['present_days'] }}</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $payrollSummary['total_days'] > 0 ? round(($payrollSummary['present_days'] / $payrollSummary['total_days']) * 100, 1) : 0 }}%
                            kehadiran
                        </p>
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
                        <p class="text-sm font-medium text-gray-500">Total Jam Kerja</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($payrollSummary['total_work_hours'], 1) }}</h3>
                        <p class="text-xs text-gray-500 mt-1">Jam</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Overtime</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-2">
                            {{ number_format($payrollSummary['overtime_pay_eligible_hours'], 1) }}</h3>
                        <p class="text-xs text-gray-500 mt-1">Jam (Approved)</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">Terlambat</p>
                <p class="text-2xl font-bold text-orange-600">{{ $payrollSummary['late_days'] }}</p>
                <p class="text-xs text-gray-400 mt-1">hari</p>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">Tidak Hadir</p>
                <p class="text-2xl font-bold text-red-600">{{ $payrollSummary['absent_days'] }}</p>
                <p class="text-xs text-gray-400 mt-1">hari</p>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">Cuti</p>
                <p class="text-2xl font-bold text-blue-600">{{ $payrollSummary['leave_days'] }}</p>
                <p class="text-xs text-gray-400 mt-1">hari</p>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">Sakit</p>
                <p class="text-2xl font-bold text-purple-600">{{ $payrollSummary['sick_days'] }}</p>
                <p class="text-xs text-gray-400 mt-1">hari</p>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">WFH</p>
                <p class="text-2xl font-bold text-indigo-600">{{ $payrollSummary['wfh_days'] }}</p>
                <p class="text-xs text-gray-400 mt-1">hari</p>
            </div>

            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 text-center">
                <p class="text-xs text-gray-500 mb-1">Dinas Luar</p>
                <p class="text-2xl font-bold text-cyan-600">{{ $payrollSummary['business_trip_days'] }}</p>
                <p class="text-xs text-gray-400 mt-1">hari</p>
            </div>
        </div>

        {{-- Calendar View --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Kalender Absensi</h2>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-7 gap-2">
                    {{-- Day Headers --}}
                    @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                        <div class="text-center py-2">
                            <span class="text-xs font-bold text-gray-600 uppercase">{{ $day }}</span>
                        </div>
                    @endforeach

                    {{-- Empty cells for first week --}}
                    @php
                        $firstDayOfWeek = $startDate->copy()->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
                    @endphp
                    @for($i = 1; $i < $firstDayOfWeek; $i++)
                        <div class="aspect-square"></div>
                    @endfor

                    {{-- Calendar Days --}}
                    @foreach($calendar as $day)
                        @php
                            $summary = $day['summary'];
                            $isWeekend = $day['is_weekend'];
                            $isToday = $day['is_today'];
                        @endphp
                        <div
                            class="aspect-square border rounded-lg p-2 transition-all hover:shadow-md {{ $isWeekend ? 'bg-red-50 border-red-100' : 'bg-white border-gray-200' }} {{ $isToday ? 'ring-2 ring-indigo-500' : '' }}">
                            <div class="flex flex-col h-full">
                                {{-- Date Number --}}
                                <div class="text-sm font-bold {{ $isToday ? 'text-indigo-600' : 'text-gray-900' }}">
                                    {{ $day['date']->format('d') }}
                                </div>

                                {{-- Summary Info --}}
                                @if($summary)
                                                <div class="mt-1 flex-1 flex flex-col gap-1">
                                                    {{-- Status Badge --}}
                                                    <div
                                                        class="text-[9px] font-bold px-1.5 py-0.5 rounded {{ $summary->status_badge_class }} text-center">
                                                        {{ $summary->status === 'present' ? 'Hadir' :
                                    ($summary->status === 'late' ? 'Terlambat' :
                                        ($summary->status === 'wfh' ? 'WFH' :
                                            ($summary->status === 'business_trip' ? 'Dinas' :
                                                ($summary->status === 'leave' ? 'Cuti' :
                                                    ($summary->status === 'sick' ? 'Sakit' : 'Absent'))))) }}
                                                    </div>

                                                    {{-- Work Hours --}}
                                                    @if($summary->total_work_minutes > 0)
                                                        <div class="text-[8px] text-gray-600 text-center">
                                                            {{ number_format($summary->total_work_hours, 1) }}j
                                                        </div>
                                                    @endif

                                                    {{-- Late Badge --}}
                                                    @if($summary->late_minutes > 0)
                                                        <div class="text-[8px] bg-orange-100 text-orange-700 px-1 py-0.5 rounded text-center">
                                                            +{{ $summary->late_minutes }}m
                                                        </div>
                                                    @endif

                                                    {{-- Overtime Badge --}}
                                                    @if($summary->approved_overtime_minutes > 0)
                                                        <div class="text-[8px] bg-green-100 text-green-700 px-1 py-0.5 rounded text-center">
                                                            OT {{ round($summary->approved_overtime_minutes / 60, 1) }}j
                                                        </div>
                                                    @endif
                                                </div>
                                @else
                                    <div class="mt-1 flex-1 flex items-center justify-center">
                                        <span class="text-[10px] text-gray-300">-</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Legend --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                <h3 class="text-xs font-bold text-gray-700 mb-3">Keterangan:</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-green-100 border border-green-200"></div>
                        <span class="text-xs text-gray-600">Hadir</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-orange-100 border border-orange-200"></div>
                        <span class="text-xs text-gray-600">Terlambat</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-blue-100 border border-blue-200"></div>
                        <span class="text-xs text-gray-600">Cuti</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-purple-100 border border-purple-200"></div>
                        <span class="text-xs text-gray-600">Sakit</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-indigo-100 border border-indigo-200"></div>
                        <span class="text-xs text-gray-600">WFH</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-cyan-100 border border-cyan-200"></div>
                        <span class="text-xs text-gray-600">Dinas Luar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-red-100 border border-red-200"></div>
                        <span class="text-xs text-gray-600">Tidak Hadir</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-red-50 border border-red-100"></div>
                        <span class="text-xs text-gray-600">Weekend</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Detail Harian</h2>
            </div>

            @if($summaries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Shift</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Jam Kerja</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Terlambat</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Overtime</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($summaries as $summary)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $summary->formatted_date }}</p>
                                            <p class="text-xs text-gray-500">{{ $summary->day_name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($summary->shift)
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $summary->shift->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $summary->shift->time_range }}</p>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $summary->status_badge_class }}">
                                            {{ $summary->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">{{ $summary->formatted_total_work }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($summary->late_minutes > 0)
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-700">
                                                {{ $summary->formatted_late }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($summary->approved_overtime_minutes > 0)
                                            <div>
                                                <p class="text-sm font-semibold text-green-600">
                                                    {{ $summary->formatted_approved_overtime }}</p>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700 mt-1">
                                                    Approved
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($summary->notes || $summary->system_notes)
                                            <p class="text-xs text-gray-600">
                                                {{ $summary->notes ?? $summary->system_notes }}
                                            </p>
                                        @else
                                            <span class="text-sm text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-400 text-sm">Tidak ada data absensi untuk bulan ini</p>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                body {
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                }
            }
        </style>
    @endpush
@endsection