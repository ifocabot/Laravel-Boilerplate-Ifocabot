@extends('layouts.admin')

@section('title', 'Attendance Adjustments')

@section('content')
    <div class="space-y-6" x-data="{ showDeleteModal: false, selectedId: null }">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Attendance Adjustments</h1>
                <p class="text-sm text-gray-500 mt-1">Log perubahan kehadiran (leave, overtime, koreksi manual)</p>
            </div>
            <a href="{{ route('hris.attendance.adjustments.create') }}"
                class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-sm transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Koreksi Manual
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Adjustments</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $adjustments->total() }}</h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Leave/Cuti</p>
                        <h3 class="text-2xl font-bold text-blue-600 mt-2">
                            {{ $adjustments->where('type', 'leave')->count() }}
                        </h3>
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
                        <p class="text-sm font-medium text-gray-500">Overtime</p>
                        <h3 class="text-2xl font-bold text-green-600 mt-2">
                            {{ $adjustments->where('type', 'overtime_add')->count() }}
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Koreksi Manual</p>
                        <h3 class="text-2xl font-bold text-orange-600 mt-2">
                            {{ $adjustments->where('type', 'manual_override')->count() }}
                        </h3>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Karyawan</label>
                    <select name="employee_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500">
                        <option value="">Semua Karyawan</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->nik }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[150px]">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe</label>
                    <select name="type" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500">
                        <option value="">Semua Tipe</option>
                        @foreach($adjustmentTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500">
                </div>
                <div class="min-w-[140px]">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500">
                </div>
                <button type="submit"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Filter
                </button>
                <a href="{{ route('hris.attendance.adjustments.index') }}"
                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Reset
                </a>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @if($adjustments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Karyawan</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status Override
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Minutes</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dibuat Oleh</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($adjustments as $adjustment)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        {{ $adjustment->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ $adjustment->employee->full_name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $adjustment->employee->nik ?? '' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                                        @if($adjustment->type === 'leave') bg-blue-100 text-blue-700
                                                        @elseif($adjustment->type === 'sick') bg-purple-100 text-purple-700
                                                        @elseif($adjustment->type === 'overtime_add') bg-green-100 text-green-700
                                                        @elseif($adjustment->type === 'manual_override') bg-orange-100 text-orange-700
                                                        @else bg-gray-100 text-gray-700
                                                        @endif">
                                            {{ $adjustment->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $adjustment->status_override ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($adjustment->adjustment_minutes != 0)
                                            <span class="{{ $adjustment->adjustment_minutes > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $adjustment->adjustment_minutes > 0 ? '+' : '' }}{{ $adjustment->adjustment_minutes }}m
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-500">
                                        @if($adjustment->source_type)
                                            {{ class_basename($adjustment->source_type) }} #{{ $adjustment->source_id }}
                                        @else
                                            Manual
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $adjustment->createdBy->name ?? '-' }}
                                        <br><span class="text-xs">{{ $adjustment->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('hris.attendance.adjustments.show', $adjustment) }}"
                                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                                Detail
                                            </a>
                                            @if($adjustment->type === 'manual_override')
                                                <button type="button"
                                                    @click="showDeleteModal = true; selectedId = {{ $adjustment->id }}"
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $adjustments->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Belum ada adjustment</p>
                </div>
            @endif
        </div>

        {{-- Delete Modal --}}
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
            x-transition>
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" @click.away="showDeleteModal = false">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Hapus Adjustment</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Yakin ingin menghapus adjustment ini? Attendance summary akan di-recalculate ulang.
                </p>
                <form :action="'/hris/attendance/adjustments/' + selectedId" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showDeleteModal = false"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Toast --}}
        @if(session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-white rounded-xl shadow-lg border">
                <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                                    {{ session('success') ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ session('success') ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}" />
                    </svg>
                </div>
                <div class="ml-3 text-sm font-medium text-gray-700">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        @endif
    </div>
@endsection