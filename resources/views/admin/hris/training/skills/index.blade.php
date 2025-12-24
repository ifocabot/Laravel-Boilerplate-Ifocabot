@extends('layouts.admin')

@section('title', 'Master Skill')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Master Skill</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola daftar skill/kompetensi karyawan</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('hris.training.skill-categories.index') }}"
                    class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl">Kategori</a>
                <a href="{{ route('hris.training.skills.create') }}"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Tambah
                    Skill</a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Total Skill</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalSkills }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Skill Aktif</p>
                <h3 class="text-2xl font-bold text-green-600 mt-2">{{ $activeSkills }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($skills->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Skill</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($skills as $skill)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">{{ $skill->code }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $skill->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $skill->category->name ?? '-' }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $skill->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $skill->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('hris.training.skills.edit', $skill) }}"
                                        class="text-indigo-600 hover:text-indigo-800 mr-2">Edit</a>
                                    <form action="{{ route('hris.training.skills.destroy', $skill) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button
                                            class="text-red-600 hover:text-red-800">Hapus</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $skills->links() }}</div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">Belum ada skill</p>
                </div>
            @endif
        </div>
    </div>
@endsection