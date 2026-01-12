@extends('layouts.admin')

@section('title', 'Detail Adjustment')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Adjustment</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $adjustment->employee->full_name }} -
                    {{ $adjustment->date->format('d F Y') }}</p>
            </div>
            <a href="{{ route('hris.attendance.adjustments.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Adjustment Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Informasi Adjustment</h2>

                <dl class="space-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Karyawan</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $adjustment->employee->full_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">NIK</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $adjustment->employee->nik }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Tanggal</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $adjustment->date->format('d F Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Tipe</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                    @if($adjustment->type === 'leave') bg-blue-100 text-blue-700
                                    @elseif($adjustment->type === 'manual_override') bg-orange-100 text-orange-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                {{ $adjustment->type_label }}
                            </span>
                        </dd>
                    </div>
                    @if($adjustment->status_override)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Status Override</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $adjustment->status_override }}</dd>
                        </div>
                    @endif
                    @if($adjustment->adjustment_minutes != 0)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Adjustment Minutes</dt>
                            <dd
                                class="text-sm font-medium {{ $adjustment->adjustment_minutes > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $adjustment->adjustment_minutes > 0 ? '+' : '' }}{{ $adjustment->adjustment_minutes }} menit
                            </dd>
                        </div>
                    @endif
                    @if($adjustment->reason)
                        <div class="pt-4 border-t border-gray-100">
                            <dt class="text-sm text-gray-500 mb-1">Alasan</dt>
                            <dd class="text-sm text-gray-900">{{ $adjustment->reason }}</dd>
                        </div>
                    @endif
                </dl>

                {{-- Source Reference --}}
                @if($adjustment->source_type)
                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">Source Reference</h3>
                        <p class="text-sm text-gray-600">
                            {{ class_basename($adjustment->source_type) }} #{{ $adjustment->source_id }}
                        </p>
                    </div>
                @endif

                {{-- Audit Info --}}
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Audit</h3>
                    <p class="text-sm text-gray-600">
                        Dibuat oleh: <strong>{{ $adjustment->createdBy->name ?? '-' }}</strong>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $adjustment->created_at->format('d F Y, H:i') }}
                    </p>
                </div>
            </div>

            {{-- Related Summary --}}
            @if($summary)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Attendance Summary</h2>

                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $summary->status_badge_class }}">
                                    {{ $summary->status_label }}
                                </span>
                            </dd>
                        </div>
                        @if($summary->clock_in_at)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Clock In</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $summary->clock_in_at->format('H:i') }}</dd>
                            </div>
                        @endif
                        @if($summary->clock_out_at)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Clock Out</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $summary->clock_out_at->format('H:i') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Total Work</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $summary->formatted_total_work }}</dd>
                        </div>
                        @if($summary->late_minutes > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Terlambat</dt>
                                <dd class="text-sm font-medium text-red-600">{{ $summary->formatted_late }}</dd>
                            </div>
                        @endif
                        @if($summary->approved_overtime_minutes > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Approved OT</dt>
                                <dd class="text-sm font-medium text-green-600">{{ $summary->formatted_approved_overtime }}</dd>
                            </div>
                        @endif
                        @if($summary->source_flags)
                            <div class="pt-4 border-t border-gray-100">
                                <dt class="text-sm text-gray-500 mb-2">Source Flags</dt>
                                <dd class="flex flex-wrap gap-1">
                                    @foreach($summary->source_flags as $flag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700">
                                            {{ $flag }}
                                        </span>
                                    @endforeach
                                </dd>
                            </div>
                        @endif
                        @if($summary->notes)
                            <div class="pt-4 border-t border-gray-100">
                                <dt class="text-sm text-gray-500 mb-1">Notes</dt>
                                <dd class="text-sm text-gray-900">{{ $summary->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif
        </div>
    </div>
@endsection