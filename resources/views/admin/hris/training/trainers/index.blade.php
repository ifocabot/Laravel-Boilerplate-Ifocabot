@extends('layouts.admin')

@section('title', 'Daftar Trainer')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Daftar Trainer</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola trainer internal dan eksternal</p>
            </div>
            <a href="{{ route('hris.training.trainers.create') }}"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Tambah
                Trainer</a>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Total Trainer</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalTrainers }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Internal</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-2">{{ $internalTrainers }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Eksternal</p>
                <h3 class="text-2xl font-bold text-green-600 mt-2">{{ $externalTrainers }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($trainers->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Organisasi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Keahlian</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($trainers as $trainer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $trainer->display_name }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $trainer->type_badge_class }}">{{ $trainer->type_label }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $trainer->organization ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($trainer->expertise, 30) ?? '-' }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $trainer->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $trainer->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('hris.training.trainers.edit', $trainer) }}"
                                        class="text-indigo-600 hover:text-indigo-800 mr-2">Edit</a>
                                    <form action="{{ route('hris.training.trainers.destroy', $trainer) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button
                                            class="text-red-600 hover:text-red-800">Hapus</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $trainers->links() }}</div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">Belum ada trainer</p>
                </div>
            @endif
        </div>
    </div>
@endsection