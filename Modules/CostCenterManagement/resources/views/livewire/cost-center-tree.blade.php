<div class="space-y-4">
    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="filters.search"
                    placeholder="Cari kode atau nama..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
                <select 
                    wire:model.live="filters.type"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua Tipe</option>
                    <option value="medical">Medical</option>
                    <option value="non_medical">Non-Medical</option>
                    <option value="administrative">Administrative</option>
                    <option value="profit_center">Profit Center</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select 
                    wire:model.live="filters.is_active"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Semua Status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button 
                    wire:click="expandAll"
                    class="px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition"
                >
                    Expand All
                </button>
                <button 
                    wire:click="collapseAll"
                    class="px-3 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 transition"
                >
                    Collapse All
                </button>
            </div>
        </div>
    </div>

    {{-- Tree View --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="space-y-1">
            @forelse($rootNodes as $node)
                @include('costcentermanagement::livewire.partials.tree-node', ['node' => $node, 'level' => 0])
            @empty
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p class="mt-2">Tidak ada cost center yang ditemukan</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Legend --}}
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Legend</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            <div class="flex items-center gap-2">
                <span class="inline-block w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-gray-600">Medical</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="text-gray-600">Non-Medical</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-3 h-3 rounded-full bg-purple-500"></span>
                <span class="text-gray-600">Administrative</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="text-gray-600">Profit Center</span>
            </div>
        </div>
    </div>
</div>
