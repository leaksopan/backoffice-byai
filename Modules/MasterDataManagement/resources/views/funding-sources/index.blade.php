@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Sumber Dana</h2>
            <a href="{{ route('mdm.funding-sources.create') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Tambah Sumber Dana
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Periode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($fundingSources as $fs)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $fs->code }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $fs->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ str_replace('_', ' ', ucfirst($fs->type)) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $fs->start_date->format('d/m/Y') }}
                                @if($fs->end_date)
                                    – {{ $fs->end_date->format('d/m/Y') }}
                                @else
                                    – Sekarang
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $fs->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $fs->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('mdm.funding-sources.edit', $fs) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('mdm.funding-sources.destroy', $fs) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin hapus sumber dana ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">Belum ada data sumber dana.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $fundingSources->links() }}
            </div>
        </div>
    </div>
@endsection
