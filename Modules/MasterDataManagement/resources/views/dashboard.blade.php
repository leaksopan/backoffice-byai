@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-2xl font-bold mb-2">Dashboard Master Data Management</h3>
                <p class="text-gray-600">Modul pengelolaan data referensi untuk seluruh sistem ERP BLUD.</p>
            </div>
        </div>

        <!-- Summary Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Organization Units Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Organization Units</h4>
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $statistics['organization_units']['total'] }}</div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold">{{ $statistics['organization_units']['active'] }} Active</span>
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-red-600">{{ $statistics['organization_units']['inactive'] }} Inactive</span>
                    </div>
                    @if(!empty($statistics['organization_units']['by_type']))
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                @foreach($statistics['organization_units']['by_type'] as $type => $count)
                                    <div class="flex justify-between">
                                        <span class="capitalize">{{ str_replace('_', ' ', $type) }}</span>
                                        <span class="font-semibold">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chart of Accounts Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Chart of Accounts</h4>
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $statistics['chart_of_accounts']['total'] }}</div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold">{{ $statistics['chart_of_accounts']['postable'] }} Postable</span>
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-blue-600">{{ $statistics['chart_of_accounts']['headers'] }} Headers</span>
                    </div>
                    @if(!empty($statistics['chart_of_accounts']['by_category']))
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                @foreach($statistics['chart_of_accounts']['by_category'] as $category => $count)
                                    <div class="flex justify-between">
                                        <span class="capitalize">{{ str_replace('_', ' ', $category) }}</span>
                                        <span class="font-semibold">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Funding Sources Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Funding Sources</h4>
                        <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $statistics['funding_sources']['total'] }}</div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold">{{ $statistics['funding_sources']['active_today'] }} Active Today</span>
                    </div>
                    @if(!empty($statistics['funding_sources']['by_type']))
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                @foreach(array_slice($statistics['funding_sources']['by_type'], 0, 4) as $type => $count)
                                    <div class="flex justify-between">
                                        <span class="uppercase">{{ $type }}</span>
                                        <span class="font-semibold">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Services Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Service Catalog</h4>
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $statistics['services']['total'] }}</div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold">{{ $statistics['services']['active'] }} Active</span>
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-red-600">{{ $statistics['services']['inactive'] }} Inactive</span>
                    </div>
                </div>
            </div>

            <!-- Tariffs Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Tariffs</h4>
                        <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $statistics['tariffs']['total'] }}</div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold">{{ $statistics['tariffs']['valid_today'] }} Valid Today</span>
                    </div>
                    @if(!empty($statistics['tariffs']['by_class']))
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-xs text-gray-500 space-y-1">
                                @foreach($statistics['tariffs']['by_class'] as $class => $count)
                                    <div class="flex justify-between">
                                        <span class="capitalize">{{ str_replace('_', ' ', $class) }}</span>
                                        <span class="font-semibold">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Human Resources Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Human Resources</h4>
                        <svg class="w-8 h-8 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $statistics['human_resources']['total'] }}</div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold">{{ $statistics['human_resources']['active'] }} Active</span>
                        <span class="text-gray-400 mx-2">|</span>
                        <span class="text-red-600">{{ $statistics['human_resources']['inactive'] }} Inactive</span>
                    </div>
                </div>
            </div>

            <!-- Assets Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Assets</h4>
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2">{{ $statistics['assets']['total'] }}</div>
                    <div class="flex items-center text-sm">
                        <span class="text-green-600 font-semibold">{{ $statistics['assets']['depreciable'] }} Depreciable</span>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="text-xs text-gray-500">
                            <div class="flex justify-between">
                                <span>Total Value</span>
                                <span class="font-semibold">Rp {{ number_format($statistics['assets']['total_value'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Quality Summary Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase">Data Quality</h4>
                        <svg class="w-8 h-8 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="space-y-2">
                        @php
                            $avgCompleteness = collect($dataQuality)->avg('completeness');
                        @endphp
                        <div class="text-3xl font-bold text-gray-900">{{ number_format($avgCompleteness, 1) }}%</div>
                        <div class="text-sm text-gray-600">Average Completeness</div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-teal-500 h-2 rounded-full" style="width: {{ $avgCompleteness }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Changes -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent Changes</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entity Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentChanges as $change)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $change['entity_type'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $change['entity_code'] }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $change['entity_name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $change['action'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $change['timestamp']->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No recent changes
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Data Quality Metrics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($dataQuality as $entityType => $metrics)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 capitalize">
                            {{ str_replace('_', ' ', $entityType) }} Quality
                        </h4>
                        
                        <!-- Completeness Bar -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-gray-600">Completeness</span>
                                <span class="text-sm font-semibold text-gray-900">{{ number_format($metrics['completeness'], 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $metrics['completeness'] >= 80 ? 'bg-green-500' : ($metrics['completeness'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                     style="width: {{ $metrics['completeness'] }}%"></div>
                            </div>
                        </div>

                        <!-- Quality Issues -->
                        <div class="space-y-2">
                            @foreach($metrics as $key => $value)
                                @if($key !== 'completeness' && is_numeric($value))
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                        <span class="font-semibold {{ $value > 0 ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $value }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
