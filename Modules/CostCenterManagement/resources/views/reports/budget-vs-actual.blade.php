@extends('layouts.module')

@section('title', 'Budget vs Actual Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    @if(!request()->has('fiscal_year'))
    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Report Parameters</h2>
        
        <form method="GET" action="{{ route('ccm.reports.budget-vs-actual') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fiscal_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fiscal Year</label>
                    <input type="number" name="fiscal_year" id="fiscal_year" required value="{{ date('Y') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="period_month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period Month (Optional)</label>
                    <select name="period_month" id="period_month"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Full Year</option>
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}">{{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}</option>
                        @endfor
                    </select>
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
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
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
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Period: {{ $period_label }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ccm.reports.budget-vs-actual', array_merge(request()->all(), ['format' => 'excel'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                        Excel
                    </a>
                    <a href="{{ route('ccm.reports.budget-vs-actual', array_merge(request()->all(), ['format' => 'csv'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Grand Total Summary -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Grand Total Budget</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($grand_total_budget, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Grand Total Actual</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($grand_total_actual, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Grand Total Variance</div>
                    <div class="text-xl font-bold {{ $grand_total_variance >= 0 ? 'text-red-600' : 'text-green-600' }}">
                        Rp {{ number_format(abs($grand_total_variance), 0, ',', '.') }}
                        <span class="text-sm">{{ $grand_total_variance >= 0 ? '(Over)' : '(Under)' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div class="px-6 py-4 space-y-6">
            @foreach($report_data as $costCenterData)
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="bg-gray-100 dark:bg-gray-700 px-4 py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">
                                {{ $costCenterData['cost_center']->code }} - {{ $costCenterData['cost_center']->name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ ucfirst(str_replace('_', ' ', $costCenterData['cost_center']->type)) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total Variance</div>
                            <div class="text-lg font-bold {{ $costCenterData['total_variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rp {{ number_format(abs($costCenterData['total_variance']), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Category</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Budget</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Actual</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Variance</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Variance %</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($costCenterData['categories'] as $category => $data)
                            <tr>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ ucfirst($category) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">
                                    Rp {{ number_format($data['budget'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">
                                    Rp {{ number_format($data['actual'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right {{ $data['variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format(abs($data['variance']), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right {{ $data['variance_percentage'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ number_format(abs($data['variance_percentage']), 2) }}%
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $data['variance_classification'] === 'favorable' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }}">
                                        {{ ucfirst($data['variance_classification']) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray-50 dark:bg-gray-900 font-semibold">
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">Total</td>
                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">
                                    Rp {{ number_format($costCenterData['total_budget'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">
                                    Rp {{ number_format($costCenterData['total_actual'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right {{ $costCenterData['total_variance'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                    Rp {{ number_format(abs($costCenterData['total_variance']), 0, ',', '.') }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('ccm.reports.budget-vs-actual') }}" 
               class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← Generate New Report
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
