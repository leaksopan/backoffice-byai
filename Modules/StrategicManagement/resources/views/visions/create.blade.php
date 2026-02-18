@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Tambah Visi & Misi</h1>
            <p class="mt-1 text-sm text-slate-600">Buat visi, misi, dan tujuan strategis baru.</p>
        </div>

        <form method="POST" action="{{ route('sm.visions.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-base font-semibold text-slate-900">Informasi Visi & Misi</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="title">Judul</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="title" name="title" type="text" value="{{ old('title') }}" required>
                        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="period_start">Tahun Mulai</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="period_start" name="period_start" type="number" min="2020" max="2040" value="{{ old('period_start', date('Y')) }}" required>
                        @error('period_start') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="period_end">Tahun Akhir</label>
                        <input class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="period_end" name="period_end" type="number" min="2020" max="2040" value="{{ old('period_end', date('Y') + 4) }}" required>
                        @error('period_end') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="vision_text">Teks Visi</label>
                        <textarea class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="vision_text" name="vision_text" rows="3" required>{{ old('vision_text') }}</textarea>
                        @error('vision_text') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="mission_text">Teks Misi</label>
                        <textarea class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500 sm:text-sm" id="mission_text" name="mission_text" rows="4" placeholder="Pisahkan setiap misi dengan baris baru" required>{{ old('mission_text') }}</textarea>
                        @error('mission_text') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Goals Repeater --}}
            <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm" x-data="goalsRepeater()">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900">Tujuan Strategis</h2>
                    <button class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" type="button" @click="addGoal()">
                        + Tambah Tujuan
                    </button>
                </div>
                <template x-for="(goal, index) in goals" :key="index">
                    <div class="mt-4 flex items-start gap-3 rounded-md border border-slate-100 bg-slate-50 p-4">
                        <div class="w-24 shrink-0">
                            <label class="block text-xs font-medium text-slate-600">Kode</label>
                            <input class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm" type="text" :name="`goals[${index}][code]`" x-model="goal.code" placeholder="T1" required>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-600">Nama Tujuan</label>
                            <input class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow-sm" type="text" :name="`goals[${index}][name]`" x-model="goal.name" placeholder="Meningkatkan mutu pelayanan..." required>
                        </div>
                        <button class="mt-5 shrink-0 text-red-500 hover:text-red-700" type="button" @click="removeGoal(index)">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-3">
                <button class="inline-flex items-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                    Simpan
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900" href="{{ route('sm.visions.index') }}">Batal</a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function goalsRepeater() {
            return {
                goals: [],
                addGoal() {
                    this.goals.push({ code: '', name: '' });
                },
                removeGoal(index) {
                    this.goals.splice(index, 1);
                }
            }
        }
    </script>
    @endpush
@endsection
