@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-slate-900">Edit Aset</h2>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('mdm.assets.update', $asset) }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kode</label>
                    <input type="text" name="code" value="{{ old('code', $asset->code) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $asset->name) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kategori</label>
                    <select name="category" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach(['tanah','gedung','peralatan_medis','peralatan_non_medis','kendaraan','inventaris'] as $c)
                            <option value="{{ $c }}" {{ old('category', $asset->category) == $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kondisi</label>
                    <select name="condition" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach(['baik','rusak_ringan','rusak_berat'] as $c)
                            <option value="{{ $c }}" {{ old('condition', $asset->condition) == $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nilai Perolehan (Rp)</label>
                    <input type="number" name="acquisition_value" value="{{ old('acquisition_value', $asset->acquisition_value) }}" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Perolehan</label>
                    <input type="date" name="acquisition_date" value="{{ old('acquisition_date', $asset->acquisition_date?->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Umur Ekonomis (tahun)</label>
                    <input type="number" name="useful_life_years" value="{{ old('useful_life_years', $asset->useful_life_years) }}" min="1" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Metode Depresiasi</label>
                    <select name="depreciation_method" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        <option value="">— Tidak Ada —</option>
                        @foreach(['straight_line','declining_balance','units_of_production'] as $m)
                            <option value="{{ $m }}" {{ old('depreciation_method', $asset->depreciation_method) == $m ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($m)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Lokasi Saat Ini</label>
                <select name="current_location_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    <option value="">— Pilih Lokasi —</option>
                    @foreach($locations as $l)
                        <option value="{{ $l->id }}" {{ old('current_location_id', $asset->current_location_id) == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Nilai Residu (Rp)</label>
                <input type="number" name="residual_value" value="{{ old('residual_value', $asset->residual_value) }}" min="0" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">{{ old('description', $asset->description) }}</textarea>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $asset->is_active) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="ml-2 text-sm text-slate-700">Aktif</span>
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Simpan</button>
                <a href="{{ route('mdm.assets.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
