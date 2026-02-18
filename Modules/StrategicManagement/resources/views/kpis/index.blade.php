@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">KPI (Indikator Kinerja Utama)</h1>
                <p class="mt-1 text-sm text-slate-600">Daftar KPI beserta target dan realisasi.</p>
            </div>
            @can('strategic-management.create')
                <a class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" href="{{ route('sm.kpis.create') }}">
                    + Tambah KPI
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nama KPI</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tujuan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Tahun</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Target</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Realisasi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">Capaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kpis as $kpi)
                        @php
                            $latestActual = $kpi->actuals->sortByDesc('created_at')->first();
                            $actualVal    = $latestActual?->actual_value ?? 0;
                            $pct          = $kpi->target_value > 0 ? round($actualVal / $kpi->target_value * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $kpi->code }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $kpi->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500">{{ $kpi->goal?->code }} — {{ Str::limit($kpi->goal?->name, 30) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-slate-600">{{ $kpi->year }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700">{{ number_format($kpi->target_value, 2) }} {{ $kpi->unit }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700">{{ number_format($actualVal, 2) }} {{ $kpi->unit }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <div class="h-2 w-16 overflow-hidden rounded-full bg-slate-200">
                                        <div class="h-full rounded-full {{ $pct >= 100 ? 'bg-green-500' : ($pct >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ min($pct, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-slate-600">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-8 text-center text-sm text-slate-500" colspan="7">Belum ada data KPI.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
