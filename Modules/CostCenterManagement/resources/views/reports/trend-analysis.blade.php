@extends('layouts.module')

@section('title', 'Trend Analysis Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    @if(!request()->has('cost_center_id'))
    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Report Parameters</h2>
        
        <form method="GET" action="{{ route('ccm.reports.trend-analysis') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="cost_center_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cost Center</label>
                    <select name="cost_center_id" id="cost_center_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Select Cost Center</option>
                        @foreach(\Modules\CostCenterManagement\Models\CostCenter::active()->orderBy('code')->get() as $cc)
                        <option value="{{ $cc->id }}">{{ $cc->code }} - {{ $cc->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="months" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number of Months</label>
                    <select name="months" id="months"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="3">3 Months</option>
                        <option value="6">6 Months</option>
                        <option value="12" selected>12 Months</option>
                        <option value="18">18 Months</option>
                        <option value="24">24 Months</option>
                    </select>
                </div>

                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category (Optional)</label>
                    <select name="category" id="category"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Categories</option>
                        <option value="personnel">Personnel</option>
                        <option value="supplies">Supplies</option>
                        <option value="services">Services</option>
                        <option value="depreciation">Depreciation</option>
                        <option value="overhead">Overhead</option>
                        <option value="other">Other</option>
                    </select>
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
                        Cost Center: {{ $cost_center->code }} - {{ $cost_center->name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        Period: Last {{ $months }} months
                        @if($category)
                        | Category: {{ ucfirst($category) }}
                        @endif
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ccm.reports.trend-analysis', array_merge(request()->all(), ['format' => 'excel'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                        Excel
                    </a>
                    <a href="{{ route('ccm.reports.trend-analysis', array_merge(request()->all(), ['format' => 'csv'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Summary -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Statistics</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Avg Budget</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format($statistics['avg_budget'], 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Avg Actual</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format($statistics['avg_actual'], 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Max Actual</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format($statistics['max_actual'], 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Min Actual</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                        Rp {{ number_format($statistics['min_actual'], 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Trend Chart (Simple ASCII representation) -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Trend Visualization</h3>
            <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    Budget (Blue) vs Actual (Red) - Last {{ $months }} months
                </div>
                <div class="space-y-2">
                    @foreach($trends as $trend)
                    <div class="flex items-center space-x-2">
                        <div class="w-24 text-xs text-gray-600 dark:text-gray-400">{{ $trend['period_label'] }}</div>
                        <div class="flex-1 flex space-x-1">
                            @php
                                $maxValue = max($statistics['max_budget'], $statistics['max_actual']);
                                $budgetWidth = $maxValue > 0 ? ($trend['budget'] / $maxValue) * 100 : 0;
                                $actualWidth = $maxValue > 0 ? ($trend['actual'] / $maxValue) * 100 : 0;
                            @endphp
                            <div class="relative h-6 flex-1">
                                <div class="absolute h-6 bg-blue-200 dark:bg-blue-900 rounded" style="width: {{ $budgetWidth }}%"></div>
                                <div class="absolute h-6 bg-red-200 dark:bg-red-900 rounded opacity-75" style="width: {{ $actualWidth }}%"></div>
                            </div>
                        </div>
                        <div class="w-32 text-xs text-right">
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ number_format($trend['variance_percentage'], 1) }}%
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Trend Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Period</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Budget</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actual</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Variance</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Variance %</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($trends as $trend)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $trend['period_label'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            Rp {{ number_format($trend['budget'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            Rp {{ number_format($trend['actual'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $trend['variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format(abs($trend['variance']), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $trend['variance_percentage'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format(abs($trend['variance_percentage']), 2) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs rounded-full {{ $trend['variance_classification'] === 'favorable' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ ucfirst($trend['variance_classification']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('ccm.reports.trend-analysis') }}" 
               class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← Generate New Report
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
