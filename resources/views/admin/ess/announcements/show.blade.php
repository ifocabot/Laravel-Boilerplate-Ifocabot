@extends('layouts.admin')

@section('title', $announcement->title)

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('ess.dashboard') }}"
                class="text-indigo-600 hover:text-indigo-800 text-sm mb-2 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Dashboard
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $announcement->title }}</h1>
            <div class="flex items-center gap-3 mt-2">
                <span
                    class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $announcement->type_badge_class }}">{{ $announcement->type_label }}</span>
                @if($announcement->is_pinned)<span class="text-indigo-600 text-sm">📌 Pinned</span>@endif
                <span
                    class="text-gray-400 text-sm">{{ $announcement->published_at?->diffForHumans() ?? $announcement->created_at->diffForHumans() }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="prose max-w-none">
                {!! $announcement->content !!}
            </div>
        </div>
    </div>
@endsection