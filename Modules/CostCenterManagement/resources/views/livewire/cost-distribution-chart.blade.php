<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Cost Distribution by Category</h3>
        @if($selectedCategory)
            <button wire:click="clearSelection" class="text-sm text-blue-600 hover:text-blue-800">
                ← Back to Overview
            </button>
        @endif
    </div>
    
    @if(!$selectedCategory)
        <!-- Pie Chart View -->
        <div class="flex justify-center mb-4">
            <canvas id="costDistChart-{{ $costCenterId }}" width="400" height="400"></canvas>
        </div>
        
        <!-- Category Summary Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->chartData['labels'] as $index => $label)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $label }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format($this->chartData['values'][$index], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            {{ number_format($this->chartData['percentages'][$index], 2) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button 
                                wire:click="selectCategory('{{ strtolower($label) }}')"
                                class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                            >
                                View Details →
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-bold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format(array_sum($this->chartData['values']), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">100.00%</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <!-- Drill-Down View -->
        <div class="space-y-4">
            <!-- Category Summary -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-lg font-semibold text-blue-900">{{ ucfirst($drillDownData['category']) }}</h4>
                        <p class="text-sm text-blue-700">{{ $drillDownData['count'] }} transactions</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-900">
                            Rp {{ number_format($drillDownData['total'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transaction Details -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($drillDownData['transactions'] as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction['date'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ucfirst(str_replace('_', ' ', $transaction['type'])) }}
                                @if($transaction['reference_type'])
                                    <span class="text-xs text-gray-400">({{ $transaction['reference_type'] }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                Rp {{ number_format($transaction['amount'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $transaction['description'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                No transactions found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('periodChanged', () => {
                // Chart akan di-render ulang otomatis oleh Livewire
            });
        });
        
        // Initialize chart setelah component di-render
        @if(!$selectedCategory)
        const ctx = document.getElementById('costDistChart-{{ $costCenterId }}');
        if (ctx) {
            const chart = new Chart(ctx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: @json($this->chartData['labels']),
                    datasets: [{
                        data: @json($this->chartData['values']),
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(107, 114, 128, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    onClick: (event, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const category = @json($this->chartData['labels'])[index].toLowerCase();
                            @this.selectCategory(category);
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(2);
                                    return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID') + ' (' + percentage + '%)';
                                }
                            }
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        @endif
    </script>
    @endpush
</div>
