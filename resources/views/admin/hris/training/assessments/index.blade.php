@extends('layouts.admin')

@section('title', 'Penilaian Skill')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Penilaian Skill</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola penilaian kompetensi karyawan</p>
            </div>
            <a href="{{ route('hris.training.assessments.create') }}"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Tambah
                Penilaian</a>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Total Penilaian</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalAssessments }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Bulan Ini</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-2">{{ $recentAssessments }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Rata-rata Level</p>
                <h3 class="text-2xl font-bold text-green-600 mt-2">{{ $averageLevel ?? '-' }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($assessments->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Skill</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Level</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($assessments as $assessment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $assessment->employee->full_name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $assessment->skill->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $assessment->assessment_date->format('d M Y') }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $assessment->type_badge_class }}">{{ $assessment->type_label }}</span>
                                </td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $assessment->level_badge_class }}">{{ $assessment->proficiency_level }}
                                        - {{ $assessment->proficiency_label }}</span></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('hris.training.assessments.edit', $assessment->id) }}"
                                        class="text-indigo-600 hover:text-indigo-800 mr-2">Edit</a>
                                    <form action="{{ route('hris.training.assessments.destroy', $assessment->id) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')<button
                                            class="text-red-600 hover:text-red-800">Hapus</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $assessments->links() }}</div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">Belum ada penilaian</p>
                </div>
            @endif
        </div>
    </div>
@endsection