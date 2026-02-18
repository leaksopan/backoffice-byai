@extends('layouts.module')

@section('title', 'Review Alokasi - ' . $summary['batch_id'])

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Review Hasil Alokasi</h1>
        <p class="text-gray-600 mt-2">Batch ID: {{ $summary['batch_id'] }}</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Summary Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Ringkasan</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-600">Periode</p>
                <p class="text-lg font-semibold">
                    {{ $summary['period_start']->format('d/m/Y') }} - {{ $summary['period_end']->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status</p>
                <p class="text-lg font-semibold">
                    @if($summary['status'] === 'draft')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Draft
                        </span>
                    @elseif($summary['status'] === 'posted')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Posted
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            Reversed
                        </span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Jumlah Journal</p>
                <p class="text-lg font-semibold">{{ number_format($summary['journal_count']) }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <p class="text-sm text-gray-600">Total Source Amount</p>
                <p class="text-lg font-semibold">Rp {{ number_format($summary['total_source'], 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Allocated Amount</p>
                <p class="text-lg font-semibold">Rp {{ number_format($summary['total_allocated'], 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Difference (Zero-Sum Check)</p>
                <p class="text-lg font-semibold {{ abs($summary['difference']) > 0.01 ? 'text-red-600' : 'text-green-600' }}">
                    Rp {{ number_format($summary['difference'], 2) }}
                    @if(abs($summary['difference']) <= 0.01)
                        ✓
                    @else
                        ✗
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Grouped by Source Cost Center -->
    @foreach($groupedBySources as $sourceCostCenterId => $group)
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-lg font-bold mb-4">
                Source: {{ $group['source_cost_center']->code }} - {{ $group['source_cost_center']->name }}
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-sm text-gray-600">Total Source Amount</p>
                    <p class="text-lg font-semibold">Rp {{ number_format($group['total_source'], 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Allocated</p>
                    <p class="text-lg font-semibold">Rp {{ number_format($group['total_allocated'], 2) }}</p>
                </div>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Target Cost Center</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Allocation Rule</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Allocated Amount</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($group['journals'] as $journal)
                        <tr>
                            <td class="px-4 py-2 text-sm">
                                {{ $journal->targetCostCenter->code }} - {{ $journal->targetCostCenter->name }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                {{ $journal->allocationRule->code }}
                            </td>
                            <td class="px-4 py-2 text-sm text-right">
                                Rp {{ number_format($journal->allocated_amount, 2) }}
                            </td>
                            <td class="px-4 py-2 text-sm">
                                @php
                                    $detail = json_decode($journal->calculation_detail, true);
                                @endphp
                                {{ ucfirst($detail['method'] ?? 'N/A') }}
                                @if(isset($detail['percentage']))
                                    ({{ $detail['percentage'] }}%)
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- Actions -->
    <div class="flex justify-between items-center mt-6">
        <a href="{{ route('ccm.allocation-process.index') }}" 
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Kembali ke Daftar
        </a>
        
        @if($summary['status'] === 'draft')
            <div class="space-x-2">
                <form action="{{ route('ccm.allocation-process.rollback', $summary['batch_id']) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('Yakin ingin rollback batch ini?')">
                    @csrf
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Rollback
                    </button>
                </form>
                
                <form action="{{ route('ccm.allocation-process.post', $summary['batch_id']) }}" 
                      method="POST" 
                      class="inline"
                      onsubmit="return confirm('Yakin ingin posting batch ini ke GL?')">
                    @csrf
                    <button type="submit" 
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                            {{ abs($summary['difference']) > 0.01 ? 'disabled' : '' }}>
                        Post ke GL
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
