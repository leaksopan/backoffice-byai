@extends('layouts.module')

@section('title', 'Variance Analysis Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    @if(!request()->has('period_start'))
    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Report Parameters</h2>
        
        <form method="GET" action="{{ route('ccm.reports.variance-analysis') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="period_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period Start</label>
                    <input type="date" name="period_start" id="period_start" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="period_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period End</label>
                    <input type="date" name="period_end" id="period_end" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="cost_center_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Center Type</label>
                    <select name="cost_center_type" id="cost_center_type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Types</option>
                        <option value="medical">Medical</option>
                        <option value="non_medical">Non-Medical</option>
                        <option value="administrative">Administrative</option>
                        <option value="profit_center">Profit Center</option>
                    </select>
                </div>

                <div>
                    <label for="variance_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Variance Threshold % (Optional)</label>
                    <input type="number" name="variance_threshold" id="variance_threshold" step="0.01" placeholder="e.g., 10"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Only show cost centers with variance above this threshold</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('ccm.reports.index') }}" 
                   class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    ← Back to Reports
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                    Generate Report
                </button>
            </div>
        </form>
    </div>
    @else
    <!-- Report Output -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <!-- Report Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $report_title }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Period: {{ $period_start->format('d M Y') }} - {{ $period_end->format('d M Y') }}
                    </p>
                    @if(isset($filters['variance_threshold']) && $filters['variance_threshold'])
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        Showing only variances above {{ $filters['variance_threshold'] }}%
                    </p>
                    @endif
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ccm.reports.variance-analysis', array_merge(request()->all(), ['format' => 'excel'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                        Excel
                    </a>
                    <a href="{{ route('ccm.reports.variance-analysis', array_merge(request()->all(), ['format' => 'csv'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div class="px-6 py-4 space-y-4">
            @forelse($report_data as $row)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="bg-gray-100 dark:bg-gray-700 px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $row['cost_center']->code }} - {{ $row['cost_center']->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $row['cost_center']->type)) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Variance</div>
                            <div class="text-lg font-bold {{ $row['total_variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format(abs($row['total_variance_percentage']), 2) }}%
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full {{ $row['variance_classification'] === 'favorable' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ ucfirst($row['variance_classification']) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Budget</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($row['total_budget'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Actual</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                Rp {{ number_format($row['total_actual'], 0, ',', '.') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Variance Amount</div>
                            <div class="text-lg font-semibold {{ $row['total_variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rp {{ number_format(abs($row['total_variance']), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Category Breakdown -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Budget</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Actual</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Variance</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">%</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($row['variances'] as $category => $variance)
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-900 dark:text-white">{{ ucfirst($category) }}</td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-900 dark:text-white">
                                        Rp {{ number_format($variance['budget'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-right text-gray-900 dark:text-white">
                                        Rp {{ number_format($variance['actual'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-right {{ $variance['variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                        Rp {{ number_format(abs($variance['variance']), 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-right {{ $variance['variance_percentage'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format(abs($variance['variance_percentage']), 2) }}%
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                No variances found matching the criteria
            </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('ccm.reports.variance-analysis') }}" 
               class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← Generate New Report
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
