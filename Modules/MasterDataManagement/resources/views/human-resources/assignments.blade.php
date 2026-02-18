@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('mdm.human-resources.index') }}" class="text-sm text-slate-600 hover:text-slate-900">← Kembali</a>
                <h2 class="mt-2 text-xl font-semibold text-slate-900">Penugasan: {{ $humanResource->name }} ({{ $humanResource->nip }})</h2>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold uppercase text-slate-500">Tambah Penugasan</h3>
            <form action="{{ route('mdm.human-resources.assignments.store', $humanResource) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Unit</label>
                        <select name="unit_id" required class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ old('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Persentase Alokasi (%)</label>
                        <input type="number" name="allocation_percentage" value="{{ old('allocation_percentage') }}" required min="0" max="100" step="0.01" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm">
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
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-slate-300">
                        <span class="ml-2 text-sm text-slate-700">Aktif</span>
                    </label>
                </div>
                <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white hover:bg-slate-700">Tambah Penugasan</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Unit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Alokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Periode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-slate-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($humanResource->assignments as $a)
                        <tr>
                            <td class="px-6 py-4 text-sm text-slate-900">{{ $a->organizationUnit?->name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">{{ $a->allocation_percentage }}%</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
                                {{ $a->start_date->format('d/m/Y') }}
                                @if($a->end_date) – {{ $a->end_date->format('d/m/Y') }} @else – Sekarang @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $a->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $a->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">Belum ada penugasan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
