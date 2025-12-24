@extends('layouts.admin')

@section('title', 'Detail Dokumen - ' . $document->title)

@section('content')
    <div class="space-y-6" x-data="documentDetail()">
        {{-- Header Section --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $document->title }}</h1>
                    @php
                        $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'pending_approval' => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'expired' => 'bg-orange-100 text-orange-800',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$document->status] ?? 'bg-gray-100 text-gray-800' }}">
                        {{ $document->status_label }}
                    </span>
                    @if($document->is_confidential)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Confidential
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500">Detail dokumen karyawan dan riwayat akses</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hris.documents.download', $document) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download
                </a>
                <a href="{{ route('hris.documents.edit', $document) }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('hris.documents.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Document Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Document Information Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Informasi Dokumen</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Employee Info --}}
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg">
                                {{ $document->employee->initials ?? 'U' }}
                            </div>
                            <div>
                                <div class="text-lg font-semibold text-gray-900">{{ $document->employee->full_name }}</div>
                                <div class="text-sm text-gray-500">NIK: {{ $document->employee->nik }}</div>
                            </div>
                        </div>

                        {{-- Basic Information Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Kategori</label>
                                <div class="text-sm font-semibold text-gray-900">{{ $document->documentCategory->full_path }}</div>
                            </div>

                            @if($document->document_number)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Dokumen</label>
                                <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded">{{ $document->document_number }}</code>
                            </div>
                            @endif

                            @if($document->document_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Dokumen</label>
                                <div class="text-sm text-gray-900">{{ $document->document_date->format('d M Y') }}</div>
                            </div>
                            @endif

                            @if($document->expiry_date)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Kadaluarsa</label>
                                <div class="text-sm text-gray-900 flex items-center gap-2">
                                    {{ $document->expiry_date->format('d M Y') }}
                                    @if($document->is_expired)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            Expired
                                        </span>
                                    @elseif($document->expires_soon)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                            Expires Soon
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($document->issuer)
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Penerbit</label>
                                <div class="text-sm text-gray-900">{{ $document->issuer }}</div>
                            </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Uploaded By</label>
                                <div class="text-sm text-gray-900">{{ $document->uploadedBy->name ?? 'Unknown' }}</div>
                            </div>
                        </div>

                        @if($document->description)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-2">Deskripsi</label>
                            <div class="text-sm text-gray-900 leading-relaxed">{{ $document->description }}</div>
                        </div>
                        @endif

                        {{-- File Information --}}
                        <div class="border-t border-gray-200 pt-6">
                            <label class="block text-sm font-medium text-gray-500 mb-3">Informasi File</label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">Original Filename</div>
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $document->original_filename }}</div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2M7 4h6M7 4L5 6m14-2V2a1 1 0 00-1-1h-4a1 1 0 00-1 1v2m6 0h-6m6 0l2 2m-2-2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">File Size</div>
                                        <div class="text-sm font-medium text-gray-900">{{ $document->file_size_formatted }}</div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500">File Type</div>
                                        <div class="text-sm font-medium text-gray-900 uppercase">{{ $document->file_extension }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approval Section --}}
                @if($document->status === 'pending_approval' && auth()->user()->hasAnyRole(['hr-admin', 'super-admin']))
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-yellow-50 border-b border-yellow-100">
                        <h3 class="text-lg font-semibold text-yellow-800">Pending Approval</h3>
                        <p class="text-sm text-yellow-600 mt-1">Dokumen ini memerlukan persetujuan Anda</p>
                    </div>
                    <div class="p-6">
                        <form method="POST" class="space-y-4" x-data="{ action: '' }" @submit="return handleApproval($event)">
                            @csrf
                            <div>
                                <label for="approval_notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Catatan Approval
                                </label>
                                <textarea name="notes" id="approval_notes" rows="3" required x-show="action === 'reject'"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Masukkan catatan untuk keputusan approval..."></textarea>
                                <textarea name="notes" id="approval_notes_approve" rows="3" x-show="action === 'approve'"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Masukkan catatan approval (opsional)..."></textarea>
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="button" @click="action = 'approve'; submitForm('{{ route('hris.documents.approve', $document) }}')"
                                    class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </span>
                                </button>
                                <button type="button" @click="action = 'reject'; setTimeout(() => document.getElementById('approval_notes').focus(), 100)"
                                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject
                                    </span>
                                </button>
                            </div>

                            <div x-show="action" class="pt-4 border-t border-gray-200">
                                <div class="flex items-center gap-3">
                                    <button type="submit"
                                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                        <span x-text="action === 'approve' ? 'Confirm Approval' : 'Confirm Rejection'"></span>
                                    </button>
                                    <button type="button" @click="action = ''"
                                        class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Approval History --}}
                @if($document->approved_by || $document->status !== 'pending_approval')
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Riwayat Approval</h3>
                    </div>
                    <div class="p-6">
                        @if($document->approved_by)
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0 mt-1">
                                {{ $document->approvedBy->initials ?? 'A' }}
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-900">{{ $document->approvedBy->name }}</div>
                                <div class="text-xs text-gray-500 mb-2">{{ $document->approved_at->format('d M Y, H:i') }}</div>
                                @if($document->approval_notes)
                                    <div class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">
                                        {{ $document->approval_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <div class="text-sm text-gray-500">Belum ada riwayat approval</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Versions --}}
                @if($document->versions->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Version History</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($document->versions as $version)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-indigo-100 text-indigo-600 rounded text-xs font-bold">
                                        v{{ $version->version }}
                                    </span>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $version->title }}</div>
                                        <div class="text-xs text-gray-500">{{ $version->created_at->format('d M Y, H:i') }}</div>
                                    </div>
                                </div>
                                <a href="{{ route('hris.documents.download', $version) }}"
                                    class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                                    Download
                                </a>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Stats --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Info</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Upload Date</span>
                            <span class="text-sm font-medium text-gray-900">{{ $document->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Version</span>
                            <span class="text-sm font-medium text-gray-900">{{ $document->version }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Access Count</span>
                            <span class="text-sm font-medium text-gray-900">{{ $document->accessLogs->count() }}</span>
                        </div>
                        @if($document->notify_expiry && $document->notify_days_before)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">Notify Before</span>
                            <span class="text-sm font-medium text-gray-900">{{ $document->notify_days_before }} hari</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Access Logs --}}
                @if($document->accessLogs->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            @foreach($document->accessLogs->take(5) as $log)
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        @switch($log->action)
                                            @case('view')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                @break
                                            @case('download')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                @break
                                            @default
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        @endswitch
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-medium">{{ $log->user->name }}</span> {{ $log->action_label }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $log->accessed_at->format('d M, H:i') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function documentDetail() {
                return {
                    handleApproval(event) {
                        return true; // Allow form submission
                    },

                    submitForm(actionUrl) {
                        const form = event.target.closest('form');
                        form.action = actionUrl;

                        // Get the appropriate textarea value
                        const action = this.action;
                        const notesTextarea = action === 'approve' ?
                            document.getElementById('approval_notes_approve') :
                            document.getElementById('approval_notes');

                        if (action === 'reject' && !notesTextarea.value.trim()) {
                            alert('Catatan wajib diisi untuk penolakan dokumen.');
                            notesTextarea.focus();
                            return;
                        }

                        form.submit();
                    }
                }
            }
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush
@endsection