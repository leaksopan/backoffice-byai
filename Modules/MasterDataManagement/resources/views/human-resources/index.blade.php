@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900">SDM</h2>
            <a href="{{ route('mdm.human-resources.create') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Tambah SDM
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIP/nama" class="rounded-md border-slate-300 shadow-sm">
            <select name="category" class="rounded-md border-slate-300">
                <option value="">Semua Kategori</option>
                @foreach(['medis_dokter','medis_perawat','medis_bidan','penunjang_medis','administrasi','umum'] as $c)
                    <option value="{{ $c }}" {{ request('category') == $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                @endforeach
            </select>
            <select name="unit_id" class="rounded-md border-slate-300">
                <option value="">Semua Unit</option>
                @foreach($units as $u)
                    <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Filter</button>
        </form>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">NIP</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Posisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($humanResources as $hr)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $hr->nip }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $hr->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ $hr->position }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $hr->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $hr->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('mdm.human-resources.assignments', $hr) }}" class="text-indigo-600 hover:text-indigo-900">Penugasan</a>
                                <a href="{{ route('mdm.human-resources.edit', $hr) }}" class="ml-2 text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('mdm.human-resources.destroy', $hr) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin hapus SDM ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-slate-500">Belum ada data SDM.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-4 py-3">{{ $humanResources->withQueryString()->links() }}</div>
        </div>
    </div>
@endsection
