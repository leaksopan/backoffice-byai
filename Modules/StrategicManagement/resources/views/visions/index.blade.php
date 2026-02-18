@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Visi & Misi</h1>
                <p class="mt-1 text-sm text-slate-600">Daftar visi, misi, dan tujuan strategis institusi.</p>
            </div>
            @can('strategic-management.create')
                <a class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" href="{{ route('sm.visions.create') }}">
                    + Tambah Visi
                </a>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @forelse ($visions as $vision)
            <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-lg font-semibold text-slate-900">{{ $vision->title }}</h2>
                            @if ($vision->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">Nonaktif</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-500">Periode {{ $vision->period_start }} — {{ $vision->period_end }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @can('strategic-management.edit')
                            <a class="rounded-md border border-slate-200 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" href="{{ route('sm.visions.edit', $vision->id) }}">
                                Edit
                            </a>
                        @endcan
                        @can('strategic-management.delete')
                            <form method="POST" action="{{ route('sm.visions.destroy', $vision->id) }}" onsubmit="return confirm('Yakin hapus visi ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-md border border-red-200 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50" type="submit">Hapus</button>
                            </form>
                        @endcan
                    </div>
                </div>
                <div class="grid gap-6 px-6 py-4 md:grid-cols-2">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Visi</h3>
                        <p class="mt-2 text-sm text-slate-700">{{ $vision->vision_text }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Misi</h3>
                        <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $vision->mission_text }}</p>
                    </div>
                </div>
                @if ($vision->goals->count())
                    <div class="border-t border-slate-100 px-6 py-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tujuan Strategis</h3>
                        <div class="mt-3 space-y-2">
                            @foreach ($vision->goals->sortBy('sort') as $goal)
                                <div class="flex items-start gap-3 rounded-md bg-slate-50 px-4 py-3">
                                    <span class="inline-flex items-center justify-center rounded bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $goal->code }}</span>
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $goal->name }}</div>
                                        @if ($goal->description)
                                            <div class="mt-0.5 text-xs text-slate-500">{{ $goal->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                Belum ada data visi & misi. Klik "Tambah Visi" untuk memulai.
            </div>
        @endforelse
    </div>
@endsection
