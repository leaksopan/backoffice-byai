@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Tambah KPI</h1>
            <p class="mt-1 text-sm text-slate-600">Buat indikator kinerja utama baru.</p>
        </div>

        <form method="POST" action="{{ route('sm.kpis.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="goal_id">Tujuan Strategis</label>
                        <select class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="goal_id" name="goal_id" required>
                            <option value="">— Pilih Tujuan —</option>
                            @foreach ($goals as $goal)
                                <option value="{{ $goal->id }}" {{ old('goal_id') == $goal->id ? 'selected' : '' }}>
                                    {{ $goal->code }} — {{ $goal->name }} ({{ $goal->vision?->title }})
                                </option>
                            @endforeach
                        </select>
                        @error('goal_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="code">Kode KPI</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="code" name="code" type="text" value="{{ old('code') }}" placeholder="IKU-01" required>
                        @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="name">Nama KPI</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="name" name="name" type="text" value="{{ old('name') }}" required>
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="unit">Satuan</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="unit" name="unit" type="text" value="{{ old('unit') }}" placeholder="%, angka, rupiah" required>
                        @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="year">Tahun</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="year" name="year" type="number" min="2020" max="2040" value="{{ old('year', date('Y')) }}" required>
                        @error('year') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="target_value">Nilai Target</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="target_value" name="target_value" type="number" step="0.01" value="{{ old('target_value') }}" required>
                        @error('target_value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="baseline_value">Nilai Baseline</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="baseline_value" name="baseline_value" type="number" step="0.01" value="{{ old('baseline_value') }}">
                        @error('baseline_value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="formula">Formula Perhitungan</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="formula" name="formula" type="text" value="{{ old('formula') }}" placeholder="Opsional">
                        @error('formula') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                    Simpan KPI
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900" href="{{ route('sm.kpis.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
