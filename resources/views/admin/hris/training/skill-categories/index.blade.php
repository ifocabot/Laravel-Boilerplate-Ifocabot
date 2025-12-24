@extends('layouts.admin')

@section('title', 'Kategori Skill')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kategori Skill</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola kategori untuk mengelompokkan skill</p>
            </div>
            <a href="{{ route('hris.training.skill-categories.create') }}"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Tambah
                Kategori</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($categories->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Jumlah Skill</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($categories as $category)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">{{ $category->code }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $category->skills_count ?? 0 }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $category->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('hris.training.skill-categories.edit', $category) }}"
                                        class="text-indigo-600 hover:text-indigo-800 mr-2">Edit</a>
                                    <form action="{{ route('hris.training.skill-categories.destroy', $category) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button
                                            class="text-red-600 hover:text-red-800">Hapus</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $categories->links() }}</div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">Belum ada kategori</p>
                </div>
            @endif
        </div>
    </div>
@endsection