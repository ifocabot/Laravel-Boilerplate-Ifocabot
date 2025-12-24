@extends('layouts.admin')

@section('title', 'Struktur Organisasi')

@section('content')
    <div class="space-y-6" x-data="organizationPage()">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Struktur Organisasi</h1>
                <p class="text-sm text-gray-500 mt-1">Visualisasi hierarki dan struktur perusahaan</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                {{-- Department Filter --}}
                <form method="GET" action="{{ route('hris.organization.index') }}" class="flex items-center gap-2">
                    <select name="department_id" onchange="this.form.submit()"
                        class="block w-48 px-3 py-2 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($departmentId)
                        <a href="{{ route('hris.organization.index') }}"
                            class="inline-flex items-center gap-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Clear
                        </a>
                    @endif
                </form>

                <button @click="toggleView()" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span x-text="viewMode === 'chart' ? 'Matrix View' : 'Chart View'"></span>
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Karyawan</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_employees'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Jumlah Level</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ count($levels) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Departemen</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ count($departments) }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Manager/Atasan</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['managers_count'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Avg Span of Control</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['avg_span_of_control'] }}</h3>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-cyan-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button @click="activeTab = 'chart'"
                        :class="activeTab === 'chart' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        📊 Org Chart
                    </button>
                    <button @click="activeTab = 'matrix'"
                        :class="activeTab === 'matrix' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        📋 Department Matrix
                    </button>
                    <button @click="activeTab = 'levels'"
                        :class="activeTab === 'levels' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        📈 Level Distribution
                    </button>
                </nav>
            </div>

            {{-- Org Chart Tab --}}
            <div x-show="activeTab === 'chart'" x-cloak class="p-6">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm text-gray-500">Klik node untuk expand/collapse</p>
                    <button @click="expandAll = !expandAll" type="button"
                        class="text-sm text-indigo-600 hover:text-indigo-800">
                        <span x-text="expandAll ? 'Collapse All' : 'Expand All'"></span>
                    </button>
                </div>

                {{-- Org Chart Container --}}
                <div class="org-chart-container overflow-x-auto pb-8">
                    <div class="org-chart flex flex-col items-center min-w-max">
                        @if(count($chartData) > 0)
                            @foreach($chartData as $node)
                                @include('admin.hris.organization.partials.node', ['node' => $node, 'depth' => 0])
                            @endforeach
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data karyawan</h3>
                                <p class="mt-1 text-sm text-gray-500">Tambahkan karyawan dan set manager relationship.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Department Matrix Tab --}}
            <div x-show="activeTab === 'matrix'" x-cloak class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider sticky left-0 bg-gray-50 z-10">
                                    Level
                                </th>
                                @foreach($departments as $dept)
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        {{ $dept->code }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($stats['level_stats'] as $level)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap sticky left-0 bg-white z-10">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                                                {{ $level['order'] >= 50 ? 'bg-indigo-100 text-indigo-700' : '' }}
                                                {{ $level['order'] >= 30 && $level['order'] < 50 ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $level['order'] < 30 ? 'bg-gray-100 text-gray-700' : '' }}">
                                                {{ $level['order'] }}
                                            </span>
                                            <span class="text-sm font-medium text-gray-900">{{ $level['name'] }}</span>
                                        </div>
                                    </td>
                                    @foreach($departments as $dept)
                                        @php
                                            $count = \App\Models\EmployeeCareer::where('level_id', $level['id'])
                                                ->where('department_id', $dept->id)
                                                ->where('is_current', true)
                                                ->where('is_active', true)
                                                ->count();
                                        @endphp
                                        <td class="px-4 py-3 text-center">
                                            @if($count > 0)
                                                <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $count }}
                                                </span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Level Distribution Tab --}}
            <div x-show="activeTab === 'levels'" x-cloak class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Level Chart --}}
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-900">Distribusi per Level</h3>
                        @foreach($stats['level_stats'] as $level)
                            @php
                                $percentage = $stats['total_employees'] > 0
                                    ? ($level['count'] / $stats['total_employees']) * 100
                                    : 0;
                            @endphp
                            <div class="flex items-center gap-4">
                                <div class="w-32 text-sm text-gray-600">{{ $level['name'] }}</div>
                                <div class="flex-1">
                                    <div class="h-6 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                                            style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="w-16 text-right">
                                    <span class="text-sm font-semibold text-gray-900">{{ $level['count'] }}</span>
                                    <span class="text-xs text-gray-500">({{ number_format($percentage, 0) }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Department Chart --}}
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-900">Distribusi per Department</h3>
                        @foreach($stats['department_stats'] as $dept)
                            @php
                                $percentage = $stats['total_employees'] > 0
                                    ? ($dept['count'] / $stats['total_employees']) * 100
                                    : 0;
                            @endphp
                            <div class="flex items-center gap-4">
                                <div class="w-32 text-sm text-gray-600 truncate" title="{{ $dept['name'] }}">{{ $dept['name'] }}</div>
                                <div class="flex-1">
                                    <div class="h-6 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full transition-all duration-500"
                                            style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                                <div class="w-16 text-right">
                                    <span class="text-sm font-semibold text-gray-900">{{ $dept['count'] }}</span>
                                    <span class="text-xs text-gray-500">({{ number_format($percentage, 0) }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function organizationPage() {
                return {
                    activeTab: 'chart',
                    viewMode: 'chart',
                    expandAll: true,

                    toggleView() {
                        this.viewMode = this.viewMode === 'chart' ? 'matrix' : 'chart';
                        this.activeTab = this.viewMode;
                    }
                }
            }
        </script>

        <style>
            [x-cloak] { display: none !important; }

            /* Org Chart Container */
            .org-chart-container {
                padding: 2rem 0;
            }

            .org-chart {
                padding: 1rem;
            }

            /* Smooth transitions for node cards */
            .org-node-card {
                transition: all 0.2s ease;
            }

            .org-node-card:hover {
                transform: translateY(-2px);
            }

            /* Hidden class for collapse functionality */
            .org-children-container.hidden {
                display: none;
            }
        </style>
    @endpush
@endsection
