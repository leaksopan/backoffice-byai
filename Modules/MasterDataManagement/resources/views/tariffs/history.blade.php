@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('mdm.tariffs.index') }}" class="text-slate-600 hover:text-slate-900">← Kembali</a>
            <h2 class="text-xl font-semibold text-slate-900">Riwayat Tarif: {{ $service->name }}</h2>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Belum ada riwayat tarif untuk layanan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-4 py-3">{{ $tariffs->links() }}</div>
        </div>
    </div>
@endsection
