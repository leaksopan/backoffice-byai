@extends('layouts.module')

@section('title', 'Cost Center Summary Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    @if(!request()->has('period_start'))
    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Report Parameters</h2>
        
        <form method="GET" action="{{ route('ccm.reports.cost-center-summary') }}" class="space-y-4">
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
                    <label for="classification" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Classification</label>
                    <input type="text" name="classification" id="classification"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                           placeholder="e.g., Rawat Jalan, Laboratorium">
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
                    <p class="text-xs text-gray-500 dark:text-gray-500">
                        Generated: {{ $generated_at->format('d M Y H:i:s') }} by {{ $generated_by }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ccm.reports.cost-center-summary', array_merge(request()->all(), ['format' => 'excel'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Excel
                    </a>
                    <a href="{{ route('ccm.reports.cost-center-summary', array_merge(request()->all(), ['format' => 'pdf'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        PDF
                    </a>
                    <a href="{{ route('ccm.reports.cost-center-summary', array_merge(request()->all(), ['format' => 'csv'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Budget</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($total_budget, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Actual</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($total_actual, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Variance</div>
                    <div class="text-2xl font-bold {{ $total_variance >= 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rp {{ number_format(abs($total_variance), 0, ',', '.') }}
                        <span class="text-sm">{{ $total_variance >= 0 ? '(Over)' : '(Under)' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Budget</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actual</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Variance</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Variance %</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($report_data as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $row['cost_center']->code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $row['cost_center']->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                {{ ucfirst(str_replace('_', ' ', $row['cost_center']->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            Rp {{ number_format($row['budget'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                            Rp {{ number_format($row['actual'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $row['variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format(abs($row['variance']), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $row['variance_percentage'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ number_format(abs($row['variance_percentage']), 2) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="px-2 py-1 text-xs rounded-full {{ $row['variance_classification'] === 'favorable' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                {{ ucfirst($row['variance_classification']) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No data available for the selected period
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <a href="{{ route('ccm.reports.cost-center-summary') }}" 
                   class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Generate New Report
                </a>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Total: {{ count($report_data) }} cost centers
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
