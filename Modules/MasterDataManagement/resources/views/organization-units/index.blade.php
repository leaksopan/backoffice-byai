@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold text-slate-900">Organization Units</h2>
            <a href="{{ route('mdm.organization-units.create') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                Tambah Unit Organisasi
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Parent</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase text-slate-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @foreach($units as $unit)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-slate-900">{{ $unit->code }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $unit->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ $unit->type }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $unit->parent?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $unit->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $unit->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('mdm.organization-units.edit', $unit) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('mdm.organization-units.destroy', $unit) }}" method="POST" class="inline ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $units->links() }}
            </div>
        </div>
    </div>
@endsection
