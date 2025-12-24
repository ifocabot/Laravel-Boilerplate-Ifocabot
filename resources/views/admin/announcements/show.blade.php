@extends('layouts.admin')

@section('title', $announcement->title)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('access-control.announcements.index') }}"
                    class="text-indigo-600 hover:text-indigo-800 text-sm mb-2 inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $announcement->title }}</h1>
                <div class="flex items-center gap-3 mt-2">
                    <span
                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $announcement->type_badge_class }}">{{ $announcement->type_label }}</span>
                    @if($announcement->is_pinned)<span class="text-indigo-600">📌 Pinned</span>@endif
                    @if(!$announcement->is_active)<span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">Tidak
                    Aktif</span>@endif
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('access-control.announcements.edit', $announcement) }}"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Edit</a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-6">
            <div class="prose max-w-none">
                {!! $announcement->content !!}
            </div>

            <div class="mt-6 pt-6 border-t grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Dibuat oleh</p>
                    <p class="font-medium">{{ $announcement->creator?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Dibuat pada</p>
                    <p class="font-medium">{{ $announcement->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Publish</p>
                    <p class="font-medium">{{ $announcement->published_at?->format('d M Y H:i') ?? 'Langsung' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Kadaluarsa</p>
                    <p class="font-medium">{{ $announcement->expires_at?->format('d M Y H:i') ?? 'Tidak ada' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection