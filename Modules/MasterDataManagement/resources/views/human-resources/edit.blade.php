@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-slate-900">Edit SDM</h2>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('mdm.human-resources.update', $humanResource) }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">NIP</label>
                    <input type="text" name="nip" value="{{ old('nip', $humanResource->nip) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $humanResource->name) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kategori</label>
                    <select name="category" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach(['medis_dokter','medis_perawat','medis_bidan','penunjang_medis','administrasi','umum'] as $c)
                            <option value="{{ $c }}" {{ old('category', $humanResource->category) == $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Posisi</label>
                    <input type="text" name="position" value="{{ old('position', $humanResource->position) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Status Kepegawaian</label>
                    <select name="employment_status" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach(['pns','pppk','kontrak','honorer'] as $s)
                            <option value="{{ $s }}" {{ old('employment_status', $humanResource->employment_status) == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Golongan</label>
                    <input type="text" name="grade" value="{{ old('grade', $humanResource->grade) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Gaji Pokok</label>
                    <input type="number" name="basic_salary" value="{{ old('basic_salary', $humanResource->basic_salary) }}" min="0" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jam Efektif/Minggu</label>
                    <input type="number" name="effective_hours_per_week" value="{{ old('effective_hours_per_week', $humanResource->effective_hours_per_week) }}" min="0" max="168" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Mulai Kerja</label>
                    <input type="date" name="hire_date" value="{{ old('hire_date', $humanResource->hire_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Berakhir</label>
                    <input type="date" name="termination_date" value="{{ old('termination_date', $humanResource->termination_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $humanResource->is_active) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="ml-2 text-sm text-slate-700">Aktif</span>
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Simpan</button>
                <a href="{{ route('mdm.human-resources.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
