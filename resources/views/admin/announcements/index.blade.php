@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengumuman</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola pengumuman untuk karyawan</p>
            </div>
            <a href="{{ route('access-control.announcements.create') }}"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Buat
                Pengumuman</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($announcements->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pinned</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Dibuat</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($announcements as $announcement)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('access-control.announcements.show', $announcement) }}"
                                        class="font-medium text-gray-900 hover:text-indigo-600">{{ $announcement->title }}</a>
                                    <p class="text-sm text-gray-500 truncate max-w-xs">
                                        {!! Str::limit(strip_tags($announcement->content), 50) !!}
                                    </p>
                                </td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $announcement->type_badge_class }}">{{ $announcement->type_label }}</span>
                                </td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $announcement->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $announcement->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                </td>
                                <td class="px-6 py-4">@if($announcement->is_pinned)<span class="text-indigo-600">📌</span>@endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $announcement->created_at->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('access-control.announcements.edit', $announcement) }}"
                                        class="text-indigo-600 hover:text-indigo-800 mr-2">Edit</a>
                                    <form action="{{ route('access-control.announcements.destroy', $announcement) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Hapus pengumuman ini?');">@csrf
                                        @method('DELETE')<button class="text-red-600 hover:text-red-800">Hapus</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $announcements->links() }}</div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <p class="text-gray-500">Belum ada pengumuman</p>
                    <a href="{{ route('access-control.announcements.create') }}"
                        class="mt-4 inline-block text-indigo-600 hover:text-indigo-800">Buat pengumuman pertama →</a>
                </div>
            @endif
        </div>
    </div>
@endsection