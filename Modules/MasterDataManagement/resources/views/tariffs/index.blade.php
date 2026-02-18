@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Tarif Layanan</h2>
            <a href="{{ route('mdm.tariffs.create') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Tambah Tarif
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <form method="GET" class="flex flex-wrap gap-2">
            <select name="service_id" class="rounded-md border-slate-300 shadow-sm">
                <option value="">Semua Layanan</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}" {{ request('service_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="service_class" class="rounded-md border-slate-300 shadow-sm">
                <option value="">Semua Kelas</option>
                @foreach(['vip','kelas_1','kelas_2','kelas_3','umum'] as $c)
                    <option value="{{ $c }}" {{ request('service_class') == $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Filter</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Layanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Tarif</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Periode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($tariffs as $t)
                        <tr>
                            <td class="px-6 py-4 text-sm text-slate-900">{{ $t->service?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ str_replace('_', ' ', ucfirst($t->service_class)) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">Rp {{ number_format($t->tariff_amount, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $t->start_date->format('d/m/Y') }}
                                @if($t->end_date) – {{ $t->end_date->format('d/m/Y') }} @else – Sekarang @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $t->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $t->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('mdm.tariffs.edit', $t) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                @if($t->service_id)
                                    <a href="{{ route('mdm.tariffs.history', $t->service_id) }}" class="ml-2 text-slate-600 hover:text-slate-900">Riwayat</a>
                                @endif
                                <form action="{{ route('mdm.tariffs.destroy', $t) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin hapus tarif ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Belum ada data tarif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-4 py-3">{{ $tariffs->withQueryString()->links() }}</div>
        </div>
    </div>
@endsection
