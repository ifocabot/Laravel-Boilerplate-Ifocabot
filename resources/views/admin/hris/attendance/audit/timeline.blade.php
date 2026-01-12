@extends('layouts.admin')

@section('title', 'Audit Timeline - ' . $employee->full_name)

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Audit Timeline</h1>
                <p class="text-gray-600">
                    {{ $employee->full_name }} &bull; {{ $date->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <a href="{{ url()->previous() }}" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                ❌ {{ session('error') }}
            </div>
        @endif

        {{-- Summary Card --}}
        @if($summary)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Hari Ini</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $summary->status_badge_class }}">
                            {{ $summary->status_label }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Clock In</p>
                        <p class="font-medium">{{ $summary->clock_in_at?->format('H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Clock Out</p>
                        <p class="font-medium">{{ $summary->clock_out_at?->format('H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Lifecycle</p>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $summary->lifecycle_status?->badgeClass() ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $summary->lifecycle_status?->label() ?? 'Pending' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Durasi Kerja</p>
                        <p class="font-medium">{{ $summary->formatted_total_work }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Keterlambatan</p>
                        <p class="font-medium {{ $summary->late_minutes > 0 ? 'text-red-600' : '' }}">
                            {{ $summary->formatted_late }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Overtime (Approved)</p>
                        <p class="font-medium {{ $summary->approved_overtime_minutes > 0 ? 'text-purple-600' : '' }}">
                            {{ $summary->formatted_approved_overtime }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Reviewed By</p>
                        <p class="font-medium">{{ $summary->reviewedBy?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Event Timeline --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-history text-indigo-500 mr-2"></i>
                Event Timeline ({{ $events->count() }} events)
            </h2>

            @if($events->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Tidak ada event tercatat untuk tanggal ini.</p>
                </div>
            @else
                <div class="relative">
                    {{-- Timeline line --}}
                    <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                    <div class="space-y-6">
                        @foreach($events as $event)
                            <div class="relative flex items-start">
                                {{-- Timeline dot --}}
                                <div
                                    class="absolute left-0 flex items-center justify-center w-8 h-8 rounded-full bg-{{ $event->type_color }}-100 text-{{ $event->type_color }}-600 ring-4 ring-white">
                                    <i class="fas fa-{{ $event->type_icon }} text-sm"></i>
                                </div>

                                {{-- Event content --}}
                                <div class="ml-12 flex-1">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-semibold text-gray-900">{{ $event->type_label }}</span>
                                            <span class="text-sm text-gray-500">{{ $event->created_at->format('H:i:s') }}</span>
                                        </div>
                                        <p class="text-gray-700">{{ $event->description }}</p>

                                        @if($event->createdBy)
                                            <p class="text-xs text-gray-500 mt-2">
                                                <i class="fas fa-user mr-1"></i> {{ $event->createdBy->name }}
                                            </p>
                                        @endif

                                        @if($event->payload && count($event->payload) > 0)
                                            <details class="mt-2">
                                                <summary class="text-xs text-indigo-600 cursor-pointer hover:underline">
                                                    Lihat detail payload
                                                </summary>
                                                <pre
                                                    class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ json_encode($event->payload, JSON_PRETTY_PRINT) }}</pre>
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex justify-end space-x-3">
            <button onclick="window.print()" class="btn-secondary">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
            <a href="{{ route('hris.attendance.audit.changes', ['employee' => $employee->id, 'date' => $date->format('Y-m-d')]) }}"
                class="btn-secondary" target="_blank">
                <i class="fas fa-code mr-2"></i> Export JSON
            </a>
            @if($summary && $summary->canEdit())
                <form method="POST" action="{{ route('hris.attendance.audit.rebuild') }}" class="inline">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                    <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition"
                        onclick="return confirm('Rebuild attendance dari events untuk tanggal ini?')">
                        🔄 Rebuild dari Events
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection