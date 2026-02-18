@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-slate-900">Edit Katalog Layanan</h2>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('mdm.services.update', $service) }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kode</label>
                    <input type="text" name="code" value="{{ old('code', $service->code) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" name="name" value="{{ old('name', $service->name) }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kategori</label>
                    <select name="category" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach(['rawat_jalan','rawat_inap','igd','penunjang_medis','tindakan','operasi','persalinan','administrasi'] as $c)
                            <option value="{{ $c }}" {{ old('category', $service->category) == $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Unit</label>
                    <select name="unit_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ old('unit_id', $service->unit_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kode INA-CBG</label>
                    <input type="text" name="inacbg_code" value="{{ old('inacbg_code', $service->inacbg_code) }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Durasi Standar (menit)</label>
                    <input type="number" name="standard_duration" value="{{ old('standard_duration', $service->standard_duration) }}" min="0" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Deskripsi</label>
                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">{{ old('description', $service->description) }}</textarea>
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="ml-2 text-sm text-slate-700">Aktif</span>
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Simpan</button>
                <a href="{{ route('mdm.services.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
