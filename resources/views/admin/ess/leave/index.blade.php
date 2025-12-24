@extends('layouts.admin')

@section('title', 'Pengajuan Cuti')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengajuan Cuti</h1>
                <p class="text-sm text-gray-500 mt-1">Lihat saldo dan riwayat cuti Anda</p>
            </div>
            <a href="{{ route('ess.leave.create') }}"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">Ajukan
                Cuti</a>
        </div>

        {{-- Leave Balance Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($leaveBalances as $balance)
                <div class="bg-white rounded-xl p-4 shadow-sm border">
                    <p class="text-sm text-gray-500">{{ $balance->leaveType->name }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <span class="text-3xl font-bold text-indigo-600">{{ $balance->remaining }}</span>
                        <span class="text-sm text-gray-400">/ {{ $balance->total_quota }} hari</span>
                    </div>
                    <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full"
                            style="width: {{ $balance->total_quota > 0 ? ($balance->remaining / $balance->total_quota) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Leave History --}}
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b">
                <h2 class="font-semibold text-gray-900">Riwayat Pengajuan</h2>
            </div>
            @if($leaveRequests->count() > 0)
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Jenis Cuti</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Jumlah Hari</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Alasan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($leaveRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $request->leaveType->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $request->start_date->format('d M') }} -
                                    {{ $request->end_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $request->total_days }} hari</td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $request->reason }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                        @if($request->status === 'approved') bg-green-100 text-green-700
                                        @elseif($request->status === 'rejected') bg-red-100 text-red-700
                                        @elseif($request->status === 'cancelled') bg-gray-100 text-gray-600
                                        @else bg-yellow-100 text-yellow-700 @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($request->status === 'pending')
                                        <form action="{{ route('ess.leave.cancel', $request->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Batalkan pengajuan ini?');">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Batalkan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t">{{ $leaveRequests->links() }}</div>
            @else
                <div class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-500">Belum ada riwayat pengajuan cuti</p>
                </div>
            @endif
        </div>
    </div>
@endsection