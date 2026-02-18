@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Edit Visi & Misi</h1>
            <p class="mt-1 text-sm text-slate-600">Perbarui data visi, misi, dan tujuan strategis.</p>
        </div>

        <form method="POST" action="{{ route('sm.visions.update', $vision->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-slate-900">Informasi Visi & Misi</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="title">Judul</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="title" name="title" type="text" value="{{ old('title', $vision->title) }}" required>
                        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="period_start">Tahun Mulai</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="period_start" name="period_start" type="number" min="2020" max="2040" value="{{ old('period_start', $vision->period_start) }}" required>
                        @error('period_start') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="period_end">Tahun Akhir</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="period_end" name="period_end" type="number" min="2020" max="2040" value="{{ old('period_end', $vision->period_end) }}" required>
                        @error('period_end') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="vision_text">Teks Visi</label>
                        <textarea class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="vision_text" name="vision_text" rows="3" required>{{ old('vision_text', $vision->vision_text) }}</textarea>
                        @error('vision_text') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="mission_text">Teks Misi</label>
                        <textarea class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="mission_text" name="mission_text" rows="4" required>{{ old('mission_text', $vision->mission_text) }}</textarea>
                        @error('mission_text') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="inline-flex items-center gap-2">
                            <input class="rounded border-slate-300 text-slate-900 shadow-sm" name="is_active" type="checkbox" value="1" {{ old('is_active', $vision->is_active) ? 'checked' : '' }}>
                            <span class="text-sm text-slate-700">Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Goals List (read-only for now) --}}
            @if ($vision->goals->count())
                <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-slate-900">Tujuan Strategis</h2>
                    <div class="mt-3 space-y-2">
                        @foreach ($vision->goals->sortBy('sort') as $goal)
                            <div class="flex items-start gap-3 rounded-md bg-slate-50 px-4 py-3">
                                <span class="inline-flex items-center justify-center rounded bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-700">{{ $goal->code }}</span>
                                <div class="text-sm font-medium text-slate-900">{{ $goal->name }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                    Perbarui
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900" href="{{ route('sm.visions.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
