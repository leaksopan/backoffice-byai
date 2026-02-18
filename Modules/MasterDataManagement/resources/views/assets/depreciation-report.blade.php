@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Laporan Depresiasi Aset</h2>
            <a href="{{ route('mdm.assets.index') }}" class="text-slate-600 hover:text-slate-900">← Kembali ke Daftar Aset</a>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm font-medium text-slate-700">Per Tanggal:</label>
            <input type="date" name="as_of_date" value="{{ $asOfDate->format('Y-m-d') }}" class="rounded-md border-slate-300 shadow-sm">
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Tampilkan</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Nama</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Nilai Perolehan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Depresiasi Bulanan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Akumulasi</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Nilai Buku</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($depreciationData as $row)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $row['asset']->code }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $row['asset']->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-600">Rp {{ number_format($row['asset']->acquisition_value, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-600">Rp {{ number_format($row['monthly_depreciation'], 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-slate-600">Rp {{ number_format($row['accumulated_depreciation'], 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-slate-900">Rp {{ number_format($row['book_value'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Tidak ada aset yang dapat didepresiasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
