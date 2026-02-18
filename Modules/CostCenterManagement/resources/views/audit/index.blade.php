<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Trail') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('ccm.audit-trail.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Model Type</label>
                                <select name="model_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">All</option>
                                    <option value="Modules\CostCenterManagement\Models\CostCenter" {{ request('model_type') == 'Modules\CostCenterManagement\Models\CostCenter' ? 'selected' : '' }}>Cost Center</option>
                                    <option value="Modules\CostCenterManagement\Models\AllocationRule" {{ request('model_type') == 'Modules\CostCenterManagement\Models\AllocationRule' ? 'selected' : '' }}>Allocation Rule</option>
                                    <option value="Modules\CostCenterManagement\Models\CostCenterBudget" {{ request('model_type') == 'Modules\CostCenterManagement\Models\CostCenterBudget' ? 'selected' : '' }}>Budget</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Event</label>
                                <select name="event" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">All</option>
                                    <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                                    <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                                    <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                                    <option value="approved" {{ request('event') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="executed" {{ request('event') == 'executed' ? 'selected' : '' }}>Executed</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date Range</label>
                                <div class="flex space-x-2">
                                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Filter
                            </button>
                            <a href="{{ route('ccm.audit-trail.export', ['start_date' => request('start_date', now()->subMonth()->toDateString()), 'end_date' => request('end_date', now()->toDateString()), 'format' => 'csv']) }}" 
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Export CSV
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changes</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($logs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($log->event == 'created') bg-green-100 text-green-800
                                                @elseif($log->event == 'updated') bg-blue-100 text-blue-800
                                                @elseif($log->event == 'deleted') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($log->event) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->user ? $log->user->name : 'System' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @php
                                                $changes = $log->getChangedFields();
                                            @endphp
                                            @if(count($changes) > 0)
                                                <ul class="list-disc list-inside">
                                                    @foreach(array_slice($changes, 0, 3) as $field => $values)
                                                        <li>{{ $field }}: {{ $values['old'] ?? 'NULL' }} → {{ $values['new'] ?? 'NULL' }}</li>
                                                    @endforeach
                                                    @if(count($changes) > 3)
                                                        <li class="text-gray-500">... and {{ count($changes) - 3 }} more</li>
                                                    @endif
                                                </ul>
                                            @else
                                                <span class="text-gray-500">No changes</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('ccm.audit-trail.show', [$log->auditable_type, $log->auditable_id]) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">View History</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No audit logs found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
