@extends('layouts.module')

@section('title', 'Cost Allocation Detail Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    @if(!request()->has('period_start'))
    <!-- Filter Form -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Report Parameters</h2>
        
        <form method="GET" action="{{ route('ccm.reports.cost-allocation-detail') }}" class="space-y-4">
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
                    <label for="batch_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Batch ID (Optional)</label>
                    <input type="text" name="batch_id" id="batch_id"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="posted">Posted</option>
                        <option value="reversed">Reversed</option>
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
                        Period: {{ $period_start->format('d M Y') }} - {{ $period_end->format('d M Y') }}
                    </p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ccm.reports.cost-allocation-detail', array_merge(request()->all(), ['format' => 'excel'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md">
                        Excel
                    </a>
                    <a href="{{ route('ccm.reports.cost-allocation-detail', array_merge(request()->all(), ['format' => 'csv'])) }}" 
                       class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md">
                        CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Source Amount</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($total_source_amount, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Allocated Amount</div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        Rp {{ number_format($total_allocated_amount, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Difference (Zero-Sum Check)</div>
                    <div class="text-xl font-bold {{ abs($total_difference) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                        Rp {{ number_format(abs($total_difference), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Content -->
        <div class="px-6 py-4">
            @foreach($report_data as $batch)
            <div class="mb-6">
                <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-t-lg">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Batch: {{ $batch['batch_id'] }}</h3>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Source: Rp {{ number_format($batch['batch_source_amount'], 0, ',', '.') }} | 
                            Allocated: Rp {{ number_format($batch['batch_allocated_amount'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Source</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Target</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Base</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Source Amount</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Allocated</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($batch['allocations'] as $allocation)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                    {{ $allocation->sourceCostCenter->code }} - {{ $allocation->sourceCostCenter->name }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                    {{ $allocation->targetCostCenter->code }} - {{ $allocation->targetCostCenter->name }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $allocation->allocationRule->allocation_base ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">
                                    Rp {{ number_format($allocation->source_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-sm text-right text-gray-900 dark:text-white">
                                    Rp {{ number_format($allocation->allocated_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $allocation->status === 'posted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 
                                           ($allocation->status === 'reversed' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 
                                           'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300') }}">
                                        {{ ucfirst($allocation->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('ccm.reports.cost-allocation-detail') }}" 
               class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                ← Generate New Report
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
