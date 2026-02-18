@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Roadmap Multi-Tahun</h1>
            <p class="mt-1 text-sm text-slate-600">Peta jalan strategis per tahun.</p>
        </div>

        @forelse ($roadmaps as $year => $items)
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $year }}</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach ($items as $item)
                        <div class="flex items-start gap-4 px-6 py-4">
                            <div class="mt-0.5 shrink-0">
                                @if ($item->status === 'done')
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-green-600">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                @elseif ($item->status === 'in_progress')
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    </span>
                                @else
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-slate-900">{{ $item->title }}</span>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                        {{ $item->priority === 'high' ? 'bg-red-100 text-red-700' : ($item->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-600') }}">
                                        {{ ucfirst($item->priority) }}
                                    </span>
                                </div>
                                @if ($item->description)
                                    <p class="mt-1 text-sm text-slate-500">{{ $item->description }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">Tujuan: {{ $item->goal?->code }} — {{ $item->goal?->name }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                Belum ada data roadmap.
            </div>
        @endforelse
    </div>
@endsection
