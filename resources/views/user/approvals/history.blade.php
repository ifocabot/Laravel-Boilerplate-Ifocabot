@extends('layouts.admin')

@section('title', 'History Approval')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">History Approval</h1>
                <p class="text-sm text-gray-500 mt-1">Riwayat persetujuan yang telah Anda proses</p>
            </div>
            <a href="{{ route('approvals.pending') }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Pending
            </a>
        </div>

        {{-- History List --}}
        @if($history->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Workflow</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Pemohon</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($history as $step)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ $step->actioned_at?->format('d M Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $step->actioned_at?->format('H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $step->approvalRequest->workflow->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">Step {{ $step->step_order }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-900">{{ $step->approvalRequest->requester->full_name ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $step->status_badge_class }}">
                                            {{ $step->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-600">{{ $step->notes ?? '-' }}</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 text-sm font-medium mb-1">Belum ada riwayat</p>
                <p class="text-gray-400 text-sm">Anda belum memproses pengajuan apapun</p>
            </div>
        @endif
    </div>
@endsection
