@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Strategic Management</h1>
            <p class="mt-1 text-sm text-slate-600">Pengelolaan Visi, Misi, KPI, Roadmap & Evaluasi Kinerja BLUD.</p>
        </div>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Visi Aktif</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $activeVisions }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Total KPI</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $totalKpis }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Capaian Rata-rata</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $avgScore }}%</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Roadmap Items</div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $roadmapItems }}</div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300 hover:shadow" href="{{ route('sm.visions.index') }}">
                <div class="text-sm font-semibold text-slate-900">Visi & Misi</div>
                <div class="mt-2 text-xs text-slate-500">Kelola visi, misi, dan tujuan strategis</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300 hover:shadow" href="{{ route('sm.kpis.index') }}">
                <div class="text-sm font-semibold text-slate-900">KPI</div>
                <div class="mt-2 text-xs text-slate-500">Indikator kinerja utama & pencapaian</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300 hover:shadow" href="{{ route('sm.roadmap.index') }}">
                <div class="text-sm font-semibold text-slate-900">Roadmap</div>
                <div class="mt-2 text-xs text-slate-500">Peta jalan multi-tahun</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300 hover:shadow" href="{{ route('sm.evaluations.index') }}">
                <div class="text-sm font-semibold text-slate-900">Evaluasi Kinerja</div>
                <div class="mt-2 text-xs text-slate-500">Evaluasi pencapaian tahunan</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300 hover:shadow" href="{{ route('sm.settings') }}">
                <div class="text-sm font-semibold text-slate-900">Settings</div>
                <div class="mt-2 text-xs text-slate-500">Pengaturan modul</div>
            </a>
        </div>
    </div>
@endsection
