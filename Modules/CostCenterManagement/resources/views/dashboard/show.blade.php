<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $costCenter->name }} - Dashboard
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $costCenter->code }} | {{ ucfirst(str_replace('_', ' ', $costCenter->type)) }}</p>
            </div>
            <div class="flex gap-2">
                <select id="periodMonth" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endfor
                </select>
                <select id="periodYear" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <a href="{{ route('ccm.dashboard.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    Back to Overview
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Budget Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Total Budget</div>
                    <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($budgetSummary['total_budget'], 0, ',', '.') }}</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Total Actual</div>
                    <div class="text-2xl font-bold text-gray-900">Rp {{ number_format($budgetSummary['total_actual'], 0, ',', '.') }}</div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Variance</div>
                    <div class="text-2xl font-bold {{ $budgetSummary['total_variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rp {{ number_format(abs($budgetSummary['total_variance']), 0, ',', '.') }}
                        <span class="text-sm">{{ $budgetSummary['total_variance'] > 0 ? '(Over)' : '(Under)' }}</span>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600">Utilization</div>
                    <div class="text-2xl font-bold {{ $budgetSummary['average_utilization'] > 100 ? 'text-red-600' : ($budgetSummary['average_utilization'] > 80 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ number_format($budgetSummary['average_utilization'], 2) }}%
                    </div>
                </div>
            </div>

            <!-- Budget vs Actual Chart (Livewire Component) -->
            @livewire('cost-center-management::budget-variance-chart', [
                'costCenterId' => $costCenter->id,
                'year' => $year,
                'month' => $month
            ])

            <!-- Variance Analysis by Category -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Variance Analysis</h3>
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
                                @if(isset($variances[$category]))
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucfirst($category) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($variances[$category]['budget'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($variances[$category]['actual'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $variances[$category]['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        Rp {{ number_format(abs($variances[$category]['variance']), 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $variances[$category]['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format(abs($variances[$category]['variance_percentage']), 2) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($variances[$category]['classification'] === 'favorable') bg-green-100 text-green-800
                                            @elseif($variances[$category]['classification'] === 'unfavorable') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($variances[$category]['classification']) }}
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            <tr class="bg-gray-50 font-bold">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Total</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($variances['total']['budget'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($variances['total']['actual'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $variances['total']['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format(abs($variances['total']['variance']), 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $variances['total']['variance'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format(abs($variances['total']['variance_percentage']), 2) }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($variances['total']['classification'] === 'favorable') bg-green-100 text-green-800
                                        @elseif($variances['total']['classification'] === 'unfavorable') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($variances['total']['classification']) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Trend Analysis Chart (Livewire Component) -->
            @livewire('cost-center-management::trend-line-chart', [
                'costCenterId' => $costCenter->id,
                'months' => 12
            ])

            <!-- Cost Distribution Pie Chart (Livewire Component) -->
            @livewire('cost-center-management::cost-distribution-chart', [
                'costCenterId' => $costCenter->id,
                'year' => $year,
                'month' => $month
            ])

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Transactions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentTransactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->transaction_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($transaction->category) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $transaction->description ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No transactions found for this period
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Period change handler
        document.getElementById('periodMonth').addEventListener('change', updatePeriod);
        document.getElementById('periodYear').addEventListener('change', updatePeriod);

        function updatePeriod() {
            const month = document.getElementById('periodMonth').value;
            const year = document.getElementById('periodYear').value;
            
            // Emit Livewire event untuk update semua chart components
            Livewire.dispatch('periodChanged', { year: parseInt(year), month: parseInt(month) });
            
            // Reload page dengan parameter baru
            const url = new URL(window.location.href);
            url.searchParams.set('month', month);
            url.searchParams.set('year', year);
            window.location.href = url.toString();
        }
    </script>
    @endpush
</x-app-layout>
