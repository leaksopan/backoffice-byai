@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-slate-900">Tambah Tarif Layanan</h2>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('mdm.tariffs.store') }}" method="POST" class="space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Layanan</label>
                <select name="service_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ old('service_id') == $s->id ? 'selected' : '' }}>{{ $s->code }} - {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kelas Layanan</label>
                    <select name="service_class" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                        @foreach(['vip','kelas_1','kelas_2','kelas_3','umum'] as $c)
                            <option value="{{ $c }}" {{ old('service_class') == $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Jumlah Tarif (Rp)</label>
                    <input type="number" name="tariff_amount" value="{{ old('tariff_amount') }}" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Tipe Pembayar</label>
                <input type="text" name="payer_type" value="{{ old('payer_type') }}" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300">
                    <span class="ml-2 text-sm text-slate-700">Aktif</span>
                </label>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Simpan</button>
                <a href="{{ route('mdm.tariffs.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Batal</a>
            </div>
        </form>
    </div>
@endsection
