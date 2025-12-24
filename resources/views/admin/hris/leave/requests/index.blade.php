@extends('layouts.admin')

@section('title', 'Pengajuan Cuti')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Pengajuan Cuti Saya</h1>
                <p class="text-sm text-gray-500 mt-1">Riwayat pengajuan cuti {{ $employee->full_name }}</p>
            </div>
            <a href="{{ route('hris.leave.requests.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Ajukan Cuti
            </a>
        </div>

        {{-- Leave Balances --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @forelse($balances as $balance)
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <p class="text-sm font-medium text-gray-500">{{ $balance->leaveType->name }}</p>
                    <div class="flex items-end justify-between mt-2">
                        <span class="text-2xl font-bold text-gray-900">{{ $balance->remaining }}</span>
                        <span class="text-xs text-gray-500">dari {{ $balance->total_quota }} hari</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                        <div class="bg-indigo-600 h-1.5 rounded-full"
                            style="width: {{ $balance->total_quota > 0 ? ($balance->remaining / $balance->total_quota) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-4 bg-yellow-50 rounded-xl p-4 text-center">
                    <p class="text-sm text-yellow-700">Belum ada saldo cuti yang ditetapkan untuk tahun ini.</p>
                </div>
            @endforelse
        </div>

        {{-- Leave Requests List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">Riwayat Pengajuan</h2>
                <form method="GET" class="flex items-center gap-3">
                    <select name="status" onchange="this.form.submit()"
                        class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </form>
            </div>

            @if($leaveRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Durasi</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Diajukan</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($leaveRequests as $request)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                            {{ $request->leaveType->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $request->formatted_date_range }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $request->total_days }} hari</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $request->status_badge_class }}">
                                            {{ $request->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $request->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        @if($request->status === 'pending')
                                            <form action="{{ route('hris.leave.requests.cancel', $request->id) }}" method="POST"
                                                onsubmit="return confirm('Batalkan pengajuan cuti ini?');" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('hris.leave.requests.show', $request->id) }}"
                                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                                Detail
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $leaveRequests->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">Belum ada pengajuan cuti</p>
                </div>
            @endif
        </div>

        {{-- Toast --}}
        @if(session('success') || session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-white rounded-xl shadow-lg border">
                <div class="inline-flex items-center justify-center w-8 h-8 rounded-lg
                            {{ session('success') ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ session('success') ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12' }}" />
                    </svg>
                </div>
                <div class="ml-3 text-sm font-medium text-gray-700">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        @endif
    </div>
@endsection