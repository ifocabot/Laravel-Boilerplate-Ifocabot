@extends('layouts.admin')

@section('title', 'Audit Period Timeline - ' . $employee->full_name)

@section('content')
    <div class="p-6 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">📅 Period Timeline</h1>
                <p class="text-gray-600">
                    {{ $employee->full_name }} &bull;
                    {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                </p>
            </div>
            <a href="{{ route('hris.attendance.audit.discrepancies') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                ← Kembali
            </a>
        </div>

        {{-- Timeline by Date --}}
        @forelse($eventsByDate as $date => $events)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span
                            class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
                        @if(isset($summaries[$date]))
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $summaries[$date]->status_badge_class }}">
                                {{ $summaries[$date]->status_label }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('hris.attendance.audit.timeline', [$employee->id, $date]) }}"
                        class="text-sm text-indigo-600 hover:text-indigo-800">
                        View Details →
                    </a>
                </div>

                <div class="p-4">
                    @if($events->isEmpty())
                        <p class="text-gray-400 text-sm italic">Tidak ada event tercatat</p>
                    @else
                        <div class="space-y-2">
                            @foreach($events as $event)
                                <div class="flex items-start gap-3">
                                    <span class="text-xs text-gray-400 w-16 flex-shrink-0">
                                        {{ $event->created_at->format('H:i') }}
                                    </span>
                                    <span class="w-2 h-2 mt-1.5 rounded-full bg-{{ $event->type_color }}-500 flex-shrink-0"></span>
                                    <div class="flex-1 text-sm">
                                        <span class="font-medium text-gray-900">{{ $event->type_label }}</span>
                                        <span class="text-gray-500">- {{ $event->description }}</span>
                                        @if($event->createdBy)
                                            <span class="text-xs text-gray-400">(by {{ $event->createdBy->name }})</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-gray-50 rounded-xl p-8 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500">Tidak ada event dalam periode ini</p>
            </div>
        @endforelse

        {{-- Summary Stats --}}
        <div class="bg-indigo-50 rounded-xl p-4">
            <h3 class="font-semibold text-indigo-900 mb-2">Statistik Periode</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-indigo-600">Total Hari:</span>
                    <span class="font-semibold">{{ $startDate->diffInDays($endDate) + 1 }}</span>
                </div>
                <div>
                    <span class="text-indigo-600">Total Events:</span>
                    <span class="font-semibold">{{ $eventsByDate->flatten()->count() }}</span>
                </div>
                <div>
                    <span class="text-indigo-600">Hari dengan Event:</span>
                    <span class="font-semibold">{{ $eventsByDate->filter(fn($e) => $e->isNotEmpty())->count() }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection