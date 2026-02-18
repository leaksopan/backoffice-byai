@php
    $hasChildren = $node->children()->count() > 0;
    $isExpanded = in_array($node->id, $expandedNodes);
    $indentClass = 'pl-' . ($level * 6);
    
    $typeColors = [
        'medical' => 'bg-green-500',
        'non_medical' => 'bg-blue-500',
        'administrative' => 'bg-purple-500',
        'profit_center' => 'bg-yellow-500',
    ];
    
    $typeColor = $typeColors[$node->type] ?? 'bg-gray-500';
@endphp

<div class="relative group" style="padding-left: {{ $level * 24 }}px">
    {{-- Node Row --}}
    <div class="flex items-center gap-2 py-2 px-3 rounded-md hover:bg-gray-50 transition-colors cursor-pointer">
        {{-- Expand/Collapse Button --}}
        <div class="flex-shrink-0 w-6">
            @if($hasChildren)
                <button 
                    wire:click="toggleNode({{ $node->id }})"
                    class="text-gray-500 hover:text-gray-700 focus:outline-none"
                >
                    @if($isExpanded)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    @endif
                </button>
            @endif
        </div>

        {{-- Type Indicator --}}
        <div class="flex-shrink-0">
            <span class="inline-block w-3 h-3 rounded-full {{ $typeColor }}"></span>
        </div>

        {{-- Node Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3">
                <span class="font-mono text-sm font-semibold text-gray-700">{{ $node->code }}</span>
                <span class="text-sm text-gray-900 truncate">{{ $node->name }}</span>
                
                @if(!$node->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                        Inactive
                    </span>
                @endif
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
            <div class="flex items-center gap-1">
                <a 
                    href="/admin/cost-centers/{{ $node->id }}/edit"
                    class="p-1 text-blue-600 hover:text-blue-800"
                    title="Edit"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Hover Details Tooltip --}}
    <div class="absolute left-full top-0 ml-4 z-50 hidden group-hover:block">
        <div class="bg-gray-900 text-white text-xs rounded-lg shadow-lg p-4 w-80">
            <div class="space-y-2">
                <div class="border-b border-gray-700 pb-2 mb-2">
                    <h4 class="font-semibold text-sm">{{ $node->name }}</h4>
                    <p class="text-gray-400 text-xs">{{ $node->code }}</p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-gray-400">Tipe:</span>
                        <span class="ml-1 font-medium">{{ ucfirst(str_replace('_', ' ', $node->type)) }}</span>
                    </div>

                    @if($node->classification)
                    <div>
                        <span class="text-gray-400">Klasifikasi:</span>
                        <span class="ml-1 font-medium">{{ $node->classification }}</span>
                    </div>
                    @endif

                    <div>
                        <span class="text-gray-400">Level:</span>
                        <span class="ml-1 font-medium">{{ $node->level }}</span>
                    </div>

                    <div>
                        <span class="text-gray-400">Status:</span>
                        <span class="ml-1 font-medium {{ $node->is_active ? 'text-green-400' : 'text-red-400' }}">
                            {{ $node->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>

                @if($node->organizationUnit)
                <div class="pt-2 border-t border-gray-700">
                    <span class="text-gray-400">Unit Organisasi:</span>
                    <span class="ml-1 font-medium">{{ $node->organizationUnit->name }}</span>
                </div>
                @endif

                @if($node->manager)
                <div>
                    <span class="text-gray-400">Manager:</span>
                    <span class="ml-1 font-medium">{{ $node->manager->name }}</span>
                </div>
                @endif

                @if($node->description)
                <div class="pt-2 border-t border-gray-700">
                    <span class="text-gray-400">Deskripsi:</span>
                    <p class="mt-1 text-xs">{{ Str::limit($node->description, 100) }}</p>
                </div>
                @endif

                <div class="pt-2 border-t border-gray-700 text-xs text-gray-400">
                    <div>Efektif: {{ $node->effective_date->format('d M Y') }}</div>
                    @if($hasChildren)
                    <div>Jumlah Child: {{ $node->children()->count() }}</div>
                    @endif
                </div>
            </div>

            {{-- Tooltip Arrow --}}
            <div class="absolute top-4 -left-2 w-0 h-0 border-t-8 border-t-transparent border-b-8 border-b-transparent border-r-8 border-r-gray-900"></div>
        </div>
    </div>
</div>

{{-- Render Children if Expanded --}}
@if($hasChildren && $isExpanded)
    @php
        $children = $this->loadChildren($node->id);
    @endphp
    
    @foreach($children as $childNode)
        @include('costcentermanagement::livewire.partials.tree-node', ['node' => $childNode, 'level' => $level + 1])
    @endforeach
@endif
