@extends('layouts.admin')

@section('title', 'Sertifikasi Karyawan')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Sertifikasi Karyawan</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola sertifikasi yang dimiliki karyawan</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('hris.training.employee-certifications.expiring') }}"
                    class="px-4 py-2.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-sm font-medium rounded-xl">Akan
                    Kadaluarsa</a>
                <a href="{{ route('hris.training.employee-certifications.create') }}"
                    class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Tambah
                    Sertifikasi</a>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Total</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ $totalCertifications }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Aktif</p>
                <h3 class="text-2xl font-bold text-green-600 mt-2">{{ $activeCertifications }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Kadaluarsa</p>
                <h3 class="text-2xl font-bold text-red-600 mt-2">{{ $expiredCertifications }}</h3>
            </div>
            <div class="bg-white rounded-xl p-6 shadow-sm border">
                <p class="text-sm text-gray-500">Akan Kadaluarsa</p>
                <h3 class="text-2xl font-bold text-yellow-600 mt-2">{{ $expiringCertifications }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            @if($employeeCertifications->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Sertifikasi</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal Terbit</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Kadaluarsa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($employeeCertifications as $empCert)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $empCert->employee->full_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $empCert->certification->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $empCert->issue_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $empCert->expiry_date ? $empCert->expiry_date->format('d M Y') : '-' }}</td>
                                <td class="px-6 py-4"><span
                                        class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $empCert->status_badge_class }}">{{ $empCert->status_label }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($empCert->status === 'pending')
                                        <form action="{{ route('hris.training.employee-certifications.verify', $empCert->id) }}"
                                            method="POST" class="inline">@csrf<button
                                                class="text-green-600 hover:text-green-800 mr-2">Verify</button></form>
                                    @endif
                                    <a href="{{ route('hris.training.employee-certifications.edit', $empCert->id) }}"
                                        class="text-indigo-600 hover:text-indigo-800 mr-2">Edit</a>
                                    <form action="{{ route('hris.training.employee-certifications.destroy', $empCert->id) }}"
                                        method="POST" class="inline" onsubmit="return confirm('Hapus?');">@csrf
                                        @method('DELETE')<button class="text-red-600 hover:text-red-800">Hapus</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $employeeCertifications->links() }}</div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">Belum ada sertifikasi karyawan</p>
                </div>
            @endif
        </div>
    </div>
@endsection