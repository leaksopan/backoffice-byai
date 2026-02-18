@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-slate-900">Edit Sumber Dana</h2>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mdm.funding-sources.update', $fundingSource) }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kode</label>
                    <input type="text" name="code" value="{{ old('code', $fundingSource->code) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $fundingSource->name) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Tipe</label>
                <select name="type" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    @foreach(['apbn','apbd_provinsi','apbd_kab_kota','pnbp','hibah','pinjaman','lainnya'] as $t)
                        <option value="{{ $t }}" {{ old('type', $fundingSource->type) == $t ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($t)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $fundingSource->start_date?->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $fundingSource->end_date?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">{{ old('description', $fundingSource->description) }}</textarea>
            </div>

            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $fundingSource->is_active) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="ml-2 text-sm text-slate-700">Aktif</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Simpan</button>
                <a href="{{ route('mdm.funding-sources.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
