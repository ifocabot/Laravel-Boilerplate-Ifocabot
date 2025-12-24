@extends('layouts.admin')

@section('title', 'Pendaftaran Training')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pendaftaran Training</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola pendaftaran karyawan ke program training</p>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Total</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalEnrollments }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Pending</p>
                <h3 class="text-2xl font-bold text-yellow-600 mt-2">{{ $pendingEnrollments }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">In Progress</p>
                <h3 class="text-2xl font-bold text-blue-600 mt-2">{{ $inProgressEnrollments }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Selesai</p>
                <h3 class="text-2xl font-bold text-green-600 mt-2">{{ $completedEnrollments }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($enrollments->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Program</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal Daftar</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Progress</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($enrollments as $enrollment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $enrollment->employee->full_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $enrollment->employee->nik }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $enrollment->program->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $enrollment->status_badge_class }}">{{ $enrollment->status_label }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full"
                                            style="width: {{ $enrollment->progress_percentage }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $enrollment->progress_percentage }}%</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($enrollment->status === 'pending')
                                        <form action="{{ route('hris.training.enrollments.approve', $enrollment->id) }}" method="POST"
                                            class="inline">@csrf<button
                                                class="text-green-600 hover:text-green-800 mr-2">Approve</button></form>
                                    @endif
                                    <form action="{{ route('hris.training.enrollments.cancel', $enrollment->id) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Batalkan?');">@csrf<button
                                            class="text-red-600 hover:text-red-800">Batal</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $enrollments->links() }}</div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">Belum ada pendaftaran</p>
                </div>
            @endif
        </div>
    </div>
@endsection