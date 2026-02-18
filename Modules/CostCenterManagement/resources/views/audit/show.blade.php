<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit History') }} - {{ class_basename($modelType) }} #{{ $modelId }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <a href="{{ route('ccm.audit-trail.index') }}" class="text-blue-500 hover:text-blue-700">
                            ← Back to Audit Trail
                        </a>
                    </div>

                    <div class="space-y-6">
                        @foreach($history as $log)
                            <div class="border-l-4 
                                @if($log->event == 'created') border-green-500
                                @elseif($log->event == 'updated') border-blue-500
                                @elseif($log->event == 'deleted') border-red-500
                                @else border-gray-500
                                @endif
                                pl-4 py-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($log->event == 'created') bg-green-100 text-green-800
                                                @elseif($log->event == 'updated') bg-blue-100 text-blue-800
                                                @elseif($log->event == 'deleted') bg-red-100 text-red-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst($log->event) }}
                                            </span>
                                            <span class="text-sm text-gray-600">
                                                by {{ $log->user ? $log->user->name : 'System' }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $log->created_at->format('Y-m-d H:i:s') }} 
                                            ({{ $log->created_at->diffForHumans() }})
                                        </div>
                                        @if($log->ip_address)
                                            <div class="text-xs text-gray-500">
                                                IP: {{ $log->ip_address }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                @if($log->justification)
                                    <div class="mt-2 text-sm text-gray-700">
                                        <strong>Justification:</strong> {{ $log->justification }}
                                    </div>
                                @endif

                                @php
                                    $changes = $log->getChangedFields();
                                @endphp

                                @if(count($changes) > 0)
                                    <div class="mt-3">
                                        <div class="text-sm font-medium text-gray-700 mb-2">Changes:</div>
                                        <div class="bg-gray-50 rounded p-3 space-y-2">
                                            @foreach($changes as $field => $values)
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-700">{{ $field }}:</span>
                                                    <div class="ml-4 mt-1">
                                                        <div class="text-red-600">
                                                            - {{ is_array($values['old']) ? json_encode($values['old']) : ($values['old'] ?? 'NULL') }}
                                                        </div>
                                                        <div class="text-green-600">
                                                            + {{ is_array($values['new']) ? json_encode($values['new']) : ($values['new'] ?? 'NULL') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($log->event == 'created' && $log->new_values)
                                    <div class="mt-3">
                                        <div class="text-sm font-medium text-gray-700 mb-2">Initial Values:</div>
                                        <div class="bg-gray-50 rounded p-3">
                                            <pre class="text-xs">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                @elseif($log->event == 'deleted' && $log->old_values)
                                    <div class="mt-3">
                                        <div class="text-sm font-medium text-gray-700 mb-2">Deleted Values:</div>
                                        <div class="bg-gray-50 rounded p-3">
                                            <pre class="text-xs">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        @if($history->isEmpty())
                            <div class="text-center text-gray-500 py-8">
                                No audit history found for this record
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
