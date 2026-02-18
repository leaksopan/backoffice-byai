<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Trend Analysis</h3>
        <div class="flex gap-2">
            <!-- Period Selector -->
            <select wire:model.live="months" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="6">Last 6 Months</option>
                <option value="12">Last 12 Months</option>
                <option value="24">Last 24 Months</option>
            </select>
            
            <!-- View Mode Selector -->
            <select wire:model.live="viewMode" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                <option value="both">Budget & Actual</option>
                <option value="budget">Budget Only</option>
                <option value="actual">Actual Only</option>
                <option value="variance">Variance Only</option>
            </select>
            
            @if($selectedPeriod)
                <button wire:click="clearSelection" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Back to Chart
                </button>
            @endif
        </div>
    </div>
    
    @if(!$selectedPeriod)
        <!-- Trend Chart -->
        <div class="mb-4">
            <canvas id="trendChart-{{ $costCenterId }}" height="80"></canvas>
        </div>
        
        <!-- Trend Summary Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance %</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($this->trendData['labels'] as $index => $label)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">{{ $label }}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format($this->trendData['budget'][$index], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format($this->trendData['actual'][$index], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-right {{ $this->trendData['variance'][$index] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format(abs($this->trendData['variance'][$index]), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-right {{ $this->trendData['variance'][$index] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format(abs($this->trendData['variance_percentage'][$index]), 2) }}%
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-center">
                            <button 
                                wire:click="selectPeriod('{{ $label }}')"
                                class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                            >
                                Details →
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <!-- Period Details Drill-Down -->
        <div class="space-y-4">
            <!-- Period Header -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-blue-900">{{ $periodDetails['period'] }}</h4>
                <p class="text-sm text-blue-700">
                    {{ Carbon\Carbon::parse($periodDetails['period_start'])->format('d M Y') }} - 
                    {{ Carbon\Carbon::parse($periodDetails['period_end'])->format('d M Y') }}
                </p>
            </div>
            
            <!-- Variance by Category -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance %</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach(['personnel', 'supplies', 'services', 'depreciation', 'overhead', 'other'] as $category)
                            @if(isset($periodDetails['variances'][$category]))
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucfirst($category) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    Rp {{ number_format($periodDetails['variances'][$category]['budget'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    Rp {{ number_format($periodDetails['variances'][$category]['actual'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $periodDetails['variances'][$category]['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format(abs($periodDetails['variances'][$category]['variance']), 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $periodDetails['variances'][$category]['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format(abs($periodDetails['variances'][$category]['variance_percentage']), 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($periodDetails['variances'][$category]['classification'] === 'favorable') bg-green-100 text-green-800
                                        @elseif($periodDetails['variances'][$category]['classification'] === 'unfavorable') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($periodDetails['variances'][$category]['classification']) }}
                                    </span>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                        <tr class="bg-gray-50 font-bold">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                Rp {{ number_format($periodDetails['variances']['total']['budget'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                Rp {{ number_format($periodDetails['variances']['total']['actual'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $periodDetails['variances']['total']['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rp {{ number_format(abs($periodDetails['variances']['total']['variance']), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $periodDetails['variances']['total']['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format(abs($periodDetails['variances']['total']['variance_percentage']), 2) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($periodDetails['variances']['total']['classification'] === 'favorable') bg-green-100 text-green-800
                                    @elseif($periodDetails['variances']['total']['classification'] === 'unfavorable') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($periodDetails['variances']['total']['classification']) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    
    @push('scripts')
    <script>
        @if(!$selectedPeriod)
        const trendCtx = document.getElementById('trendChart-{{ $costCenterId }}');
        if (trendCtx) {
            const datasets = [];
            const viewMode = '{{ $viewMode }}';
            
            if (viewMode === 'both' || viewMode === 'budget') {
                datasets.push({
                    label: 'Budget',
                    data: @json($this->trendData['budget']),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                });
            }
            
            if (viewMode === 'both' || viewMode === 'actual') {
                datasets.push({
                    label: 'Actual',
                    data: @json($this->trendData['actual']),
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                });
            }
            
            if (viewMode === 'variance') {
                datasets.push({
                    label: 'Variance',
                    data: @json($this->trendData['variance']),
                    borderColor: 'rgb(139, 92, 246)',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4
                });
            }
            
            const trendChart = new Chart(trendCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($this->trendData['labels']),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    onClick: (event, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const period = @json($this->trendData['labels'])[index];
                            @this.selectPeriod(period);
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
