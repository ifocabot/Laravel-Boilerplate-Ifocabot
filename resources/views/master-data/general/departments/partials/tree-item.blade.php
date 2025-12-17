<div class="tree-item" x-data="{ expanded: {{ $level === 0 ? 'true' : 'false' }} }">
    <div class="flex items-center gap-3 p-4 rounded-xl hover:bg-gray-50 transition-colors group"
        style="margin-left: {{ $level * 2 }}rem;">
        
        {{-- Expand/Collapse Button --}}
        @if($department->children->count() > 0)
            <button @click="expanded = !expanded" type="button"
                class="w-6 h-6 flex items-center justify-center rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 text-gray-600 transition-transform" :class="expanded ? 'rotate-90' : ''"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @else
            <div class="w-6 h-6"></div>
        @endif

        {{-- Department Icon & Info --}}
        <div class="flex items-center gap-3 flex-1">
            <div class="w-12 h-12 rounded-xl {{ $level === 0 ? 'bg-gradient-to-br from-indigo-500 to-purple-600' : 'bg-gradient-to-br from-blue-500 to-indigo-600' }} flex items-center justify-center text-white font-semibold shadow-sm">
                {{ strtoupper(substr($department->name, 0, 2)) }}
            </div>
            
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h4 class="text-sm font-semibold text-gray-900">{{ $department->name }}</h4>
                    <code class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                        {{ $department->code }}
                    </code>
                    
                    @if($level === 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                            Induk
                        </span>
                    @endif
                </div>
                
                <div class="flex items-center gap-4 mt-1">
                    {{-- Manager --}}
                    @if($department->manager)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs text-gray-600">{{ $department->manager->name }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-xs text-gray-400 italic">Belum ada manager</span>
                        </div>
                    @endif

                    {{-- Sub Departments Count --}}
                    @if($department->children->count() > 0)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                            <span class="text-xs text-gray-600">{{ $department->children->count() }} sub departemen</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            {{-- Edit Button --}}
            <button type="button"
                onclick="window.openEditModalFromTree({{ $department->id }}, '{{ addslashes($department->name) }}', '{{ $department->code }}', {{ $department->parent_id ?? 'null' }}, {{ $department->manager_id ?? 'null' }})"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                title="Edit">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </button>
            
            {{-- Add Sub Department Button --}}
            <button type="button"
                onclick="window.openAddSubDepartmentModal({{ $department->id }}, '{{ addslashes($department->name) }}')"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                title="Tambah Sub Departemen">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
            </button>
            
            {{-- Delete Button --}}
            <form action="{{ route('master-data.general.departments.destroy', $department->id) }}" method="POST"
                class="inline-block"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen ini?{{ $department->children->count() > 0 ? ' Sub departemen juga akan terhapus!' : '' }}');">
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
    @if($department->children->count() > 0)
        <div x-show="expanded" x-collapse>
            <div class="space-y-2 mt-2">
                @foreach($department->children as $child)
                    @include('master-data.general.departments.partials.tree-item', ['department' => $child, 'level' => $level + 1])
                @endforeach
            </div>
        </div>
    @endif
</div>


