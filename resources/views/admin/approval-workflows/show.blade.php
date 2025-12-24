@extends('layouts.admin')

@section('title', 'Detail Workflow - ' . $workflow->name)

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <a href="{{ route('admin.approval-workflows.index') }}"
                        class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                        {{ $workflow->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ $workflow->is_active ? 'Aktif' : 'Tidak Aktif' }}
                </span>
                <a href="{{ route('admin.approval-workflows.edit', $workflow->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Info --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Workflow</h3>

                    <dl class="space-y-4">
                        <div class="flex items-start gap-4">
                            <dt class="text-sm font-medium text-gray-500 w-32">Nama</dt>
                            <dd class="text-sm text-gray-900">{{ $workflow->name }}</dd>
                        </div>
                        <div class="flex items-start gap-4">
                            <dt class="text-sm font-medium text-gray-500 w-32">Tipe</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $workflow->type === 'leave' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $workflow->type === 'overtime' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $workflow->type === 'reimbursement' ? 'bg-purple-100 text-purple-700' : '' }}">
                                    {{ ucfirst($workflow->type) }}
                                </span>
                            </dd>
                        </div>
                        @if($workflow->description)
                            <div class="flex items-start gap-4">
                                <dt class="text-sm font-medium text-gray-500 w-32">Deskripsi</dt>
                                <dd class="text-sm text-gray-900">{{ $workflow->description }}</dd>
                            </div>
                        @endif
                        <div class="flex items-start gap-4">
                            <dt class="text-sm font-medium text-gray-500 w-32">Dibuat</dt>
                            <dd class="text-sm text-gray-900">{{ $workflow->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Steps --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Langkah Approval</h3>

                    <div class="space-y-4">
                        @foreach($workflow->steps as $step)
                            <div class="flex items-start gap-3">
                                <span
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-sm font-bold flex-shrink-0">
                                    {{ $step->step_order }}
                                </span>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $step->approver_type_label }}</p>
                                    @if($step->approver_value)
                                        <p class="text-xs text-gray-500">ID: {{ $step->approver_value }}</p>
                                    @endif
                                    @if($step->can_skip_if_same)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600 mt-1">
                                            Skip jika sama
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if(!$loop->last)
                                <div class="flex items-center gap-3">
                                    <div class="w-7 flex justify-center">
                                        <div class="w-0.5 h-4 bg-gray-200"></div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection