{{-- Recursive node component for org chart --}}
@php
    $levelColor = match (true) {
        $node['level_order'] >= 70 => 'from-indigo-600 to-purple-600',
        $node['level_order'] >= 60 => 'from-blue-600 to-indigo-600',
        $node['level_order'] >= 50 => 'from-cyan-600 to-blue-600',
        $node['level_order'] >= 40 => 'from-emerald-600 to-cyan-600',
        $node['level_order'] >= 30 => 'from-green-600 to-emerald-600',
        default => 'from-gray-500 to-gray-600',
    };
    $hasChildren = count($node['children']) > 0;
    $childCount = count($node['children']);
@endphp

<div class="org-node-wrapper flex flex-col items-center">
    {{-- Node Card --}}
    <div class="org-node-card bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-200"
        style="min-width: 180px; max-width: 220px;">
        {{-- Header with gradient --}}
        <div class="h-2 bg-gradient-to-r {{ $levelColor }}"></div>

        <div class="p-4">
            <div class="flex items-start gap-3">
                {{-- Avatar --}}
                <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $levelColor }} flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($node['name'], 0, 2)) }}
                </div>

                <div class="flex-1 min-w-0">
                    {{-- Name --}}
                    <h4 class="text-sm font-bold text-gray-900 truncate" title="{{ $node['name'] }}">
                        {{ $node['name'] }}
                    </h4>

                    {{-- Position --}}
                    <p class="text-xs text-gray-600 truncate" title="{{ $node['position'] }}">
                        {{ $node['position'] }}
                    </p>

                    {{-- Department Badge --}}
                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                            {{ $node['department_code'] ?: $node['department'] }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                            Lv.{{ $node['level_order'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Direct Reports Count with Toggle --}}
            @if($hasChildren)
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between cursor-pointer select-none"
                    onclick="var container = this.closest('.org-node-wrapper').querySelector('.org-children-container'); container.style.display = container.style.display === 'none' ? 'block' : 'none'; this.querySelector('.toggle-icon').classList.toggle('rotate-180');">
                    <span class="text-xs text-gray-500">
                        👥 {{ $node['direct_reports_count'] }} direct report{{ $node['direct_reports_count'] > 1 ? 's' : '' }}
                    </span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 toggle-icon"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            @endif
        </div>
    </div>

    {{-- Children Container --}}
    @if($hasChildren)
        <div class="org-children-container" style="display: block;">
            {{-- Vertical line from parent down --}}
            <div style="display: flex; justify-content: center;">
                <div style="width: 2px; height: 24px; background-color: #d1d5db;"></div>
            </div>
            
            {{-- Horizontal bar + Children --}}
            <div style="position: relative;">
                {{-- Horizontal connector bar (only if multiple children) --}}
                @if($childCount > 1)
                    <div style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); height: 2px; background-color: #d1d5db; width: calc(100% - 180px);"></div>
                @endif
                
                {{-- Children nodes row --}}
                <div style="display: flex; justify-content: center; align-items: flex-start; gap: 16px;">
                    @foreach($node['children'] as $child)
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            {{-- Vertical line from horizontal bar to child node --}}
                            <div style="width: 2px; height: 24px; background-color: #d1d5db;"></div>
                            {{-- Recursive child node --}}
                            @include('admin.hris.organization.partials.node', ['node' => $child, 'depth' => $depth + 1])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>