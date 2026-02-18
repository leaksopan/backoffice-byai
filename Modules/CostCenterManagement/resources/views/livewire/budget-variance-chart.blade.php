<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Budget vs Actual by Category</h3>
        @if($selectedCategory)
            <button wire:click="clearSelection" class="text-sm text-blue-600 hover:text-blue-800">
                ← Back to Overview
            </button>
        @endif
    </div>
    
    @if(!$selectedCategory)
        <!-- Bar Chart View -->
        <div class="mb-4">
            <canvas id="budgetVarianceChart-{{ $costCenterId }}" height="80"></canvas>
        </div>
        
        <!-- Category Summary Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->chartData['labels'] as $index => $label)
                    @php
                        $budget = $this->chartData['budget'][$index];
                        $actual = $this->chartData['actual'][$index];
                        $variance = $this->chartData['variance'][$index];
                        $utilization = $budget > 0 ? ($actual / $budget) * 100 : 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $label }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format($budget, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format($actual, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $variance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format(abs($variance), 0, ',', '.') }}
                            <span class="text-xs">({{ $variance > 0 ? 'Over' : 'Under' }})</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($utilization < 50) bg-blue-100 text-blue-800
                                @elseif($utilization < 80) bg-green-100 text-green-800
                                @elseif($utilization < 100) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ number_format($utilization, 2) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button 
                                wire:click="selectCategory('{{ strtolower($label) }}')"
                                class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                            >
                                Details →
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="bg-gray-50 font-bold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format(array_sum($this->chartData['budget']), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format(array_sum($this->chartData['actual']), 0, ',', '.') }}
                        </td>
                        @php
                            $totalVariance = array_sum($this->chartData['variance']);
                            $totalBudget = array_sum($this->chartData['budget']);
                            $totalActual = array_sum($this->chartData['actual']);
                            $totalUtilization = $totalBudget > 0 ? ($totalActual / $totalBudget) * 100 : 0;
                        @endphp
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $totalVariance > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format(abs($totalVariance), 0, ',', '.') }}
                            <span class="text-xs">({{ $totalVariance > 0 ? 'Over' : 'Under' }})</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                @if($totalUtilization < 50) bg-blue-100 text-blue-800
                                @elseif($totalUtilization < 80) bg-green-100 text-green-800
                                @elseif($totalUtilization < 100) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ number_format($totalUtilization, 2) }}%
                            </span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <!-- Category Details Drill-Down -->
        <div class="space-y-4">
            <!-- Category Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="text-sm text-blue-700">Budget</div>
                    <div class="text-2xl font-bold text-blue-900">
                        Rp {{ number_format($categoryDetails['budget'], 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="text-sm text-green-700">Actual</div>
                    <div class="text-2xl font-bold text-green-900">
                        Rp {{ number_format($categoryDetails['actual'], 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="bg-{{ $categoryDetails['variance'] > 0 ? 'red' : 'green' }}-50 border border-{{ $categoryDetails['variance'] > 0 ? 'red' : 'green' }}-200 rounded-lg p-4">
                    <div class="text-sm text-{{ $categoryDetails['variance'] > 0 ? 'red' : 'green' }}-700">Variance</div>
                    <div class="text-2xl font-bold text-{{ $categoryDetails['variance'] > 0 ? 'red' : 'green' }}-900">
                        Rp {{ number_format(abs($categoryDetails['variance']), 0, ',', '.') }}
                        <span class="text-sm">({{ $categoryDetails['variance'] > 0 ? 'Over' : 'Under' }})</span>
                    </div>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <div class="text-sm text-purple-700">Utilization</div>
                    <div class="text-2xl font-bold text-purple-900">
                        {{ number_format($categoryDetails['utilization'], 2) }}%
                    </div>
                    <div class="text-xs text-purple-600 mt-1">
                        Remaining: Rp {{ number_format($categoryDetails['remaining'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
            
            <!-- Status Indicator -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900">{{ ucfirst($categoryDetails['category']) }}</h4>
                        <p class="text-sm text-gray-600">{{ count($categoryDetails['transactions']) }} transactions</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($categoryDetails['status'] === 'low') bg-blue-100 text-blue-800
                            @elseif($categoryDetails['status'] === 'normal') bg-green-100 text-green-800
                            @elseif($categoryDetails['status'] === 'warning') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($categoryDetails['status']) }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            @if($categoryDetails['classification'] === 'favorable') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($categoryDetails['classification']) }}
                        </span>
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
                        @forelse($categoryDetails['transactions'] as $transaction)
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
        @if(!$selectedCategory)
        const budgetVarianceCtx = document.getElementById('budgetVarianceChart-{{ $costCenterId }}');
        if (budgetVarianceCtx) {
            const budgetVarianceChart = new Chart(budgetVarianceCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($this->chartData['labels']),
                    datasets: [
                        {
                            label: 'Budget',
                            data: @json($this->chartData['budget']),
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1
                        },
                        {
                            label: 'Actual',
                            data: @json($this->chartData['actual']),
                            backgroundColor: 'rgba(239, 68, 68, 0.5)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1
                        }
                    ]
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
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            }
                        },
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        }
        @endif
    </script>
    @endpush
</div>
