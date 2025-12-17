<div class="tree-item" x-data="{ expanded: {{ $level === 0 ? 'true' : 'false' }} }">
    <div class="flex items-center gap-3 p-4 rounded-xl hover:bg-gray-50 transition-colors group"
        style="margin-left: {{ $level * 2 }}rem;">

        {{-- Expand/Collapse Button --}}
        @if($location->children->count() > 0)
            <button @click="expanded = !expanded" type="button"
                class="w-6 h-6 flex items-center justify-center rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 text-gray-600 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @else
            <div class="w-6 h-6"></div>
        @endif

        {{-- Location Icon & Info --}}
        <div class="flex items-center gap-3 flex-1">
            <div
                class="w-12 h-12 rounded-xl {{ $level === 0 ? 'bg-gradient-to-br from-indigo-500 to-purple-600' : 'bg-gradient-to-br from-blue-500 to-indigo-600' }} flex items-center justify-center text-white font-semibold shadow-sm">
                {{ strtoupper(substr($location->name, 0, 2)) }}
            </div>

            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h4 class="text-sm font-semibold text-gray-900">{{ $location->name }}</h4>
                    <code class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                        {{ $location->code }}
                    </code>

                    @php
                        $typeColors = [
                            'office' => 'bg-blue-50 text-blue-700 border-blue-100',
                            'branch' => 'bg-purple-50 text-purple-700 border-purple-100',
                            'warehouse' => 'bg-amber-50 text-amber-700 border-amber-100',
                            'site' => 'bg-green-50 text-green-700 border-green-100',
                        ];
                        $typeLabels = [
                            'office' => 'Kantor',
                            'branch' => 'Cabang',
                            'warehouse' => 'Gudang',
                            'site' => 'Site',
                        ];
                    @endphp
                    <span
                        class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium border {{ $typeColors[$location->type] ?? 'bg-gray-50 text-gray-700 border-gray-100' }}">
                        {{ $typeLabels[$location->type] ?? ucfirst($location->type) }}
                    </span>

                    @if($location->is_active)
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                            Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-100">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            Nonaktif
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-4 mt-1">
                    {{-- Coordinates --}}
                    @if($location->latitude && $location->longitude)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            <span class="text-xs text-gray-600 font-mono">
                                {{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}
                            </span>
                        </div>
                    @endif

                    {{-- Radius --}}
                    @if($location->radius_meters)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            <span class="text-xs text-gray-600">{{ number_format($location->radius_meters) }}m</span>
                        </div>
                    @endif

                    {{-- Sub Locations Count --}}
                    @if($location->children->count() > 0)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="text-xs text-gray-600">{{ $location->children->count() }} sub lokasi</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button"
                onclick="window.openEditModalFromTree({{ $location->id }}, '{{ addslashes($location->name) }}', '{{ $location->code }}', '{{ $location->type }}', '{{ addslashes($location->address ?? '') }}', {{ $location->latitude ?? 'null' }}, {{ $location->longitude ?? 'null' }}, {{ $location->radius_meters ?? 'null' }}, {{ $location->is_active ? 'true' : 'false' }}, {{ $location->parent_id ?? 'null' }})"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                title="Edit">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>

            <button type="button"
                onclick="window.openAddSubLocationModal({{ $location->id }}, '{{ addslashes($location->name) }}')"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                title="Tambah Sub Lokasi">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </button>

            @if($location->latitude && $location->longitude)
                <button type="button"
                    onclick="window.open('https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}', '_blank')"
                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    title="Lihat di Maps">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </button>
            @endif

            <form action="{{ route('master-data.general.locations.destroy', $location->id) }}" method="POST"
                class="inline-block"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus lokasi ini?{{ $location->children->count() > 0 ? ' Sub lokasi juga akan terhapus!' : '' }}');">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Hapus">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </form>
        </div>
    </div>

    {{-- Children (Recursive) --}}
    @if($location->children->count() > 0)
        <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2">
            <div class="space-y-2 mt-2">
                @foreach($location->children as $child)
                    @include('master-data.general.locations.partials.tree-item', ['location' => $child, 'level' => $level + 1])
                @endforeach
            </div>
        </div>
    @endif
</div>