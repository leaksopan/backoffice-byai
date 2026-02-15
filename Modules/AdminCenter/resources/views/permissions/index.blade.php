@extends('layouts.module')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Permissions</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Manage permission catalog.</p>
        </div>
        @can('permissions.create')
            <a class="btn-primary" href="{{ route('ac.permissions.create') }}">
                Create Permission
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-6 table-glass">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3">Permission</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($permissions as $permission)
                    <tr>
                        <td class="px-4 py-3">{{ $permission->name }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @can('permissions.edit')
                                    <a class="rounded-md border border-slate-300/80 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-100/80 dark:border-slate-600 dark:hover:bg-slate-800/70" href="{{ route('ac.permissions.edit', $permission) }}">
                                        Edit
                                    </a>
                                @endcan
                                @can('permissions.delete')
                                    <form class="inline" method="POST" action="{{ route('ac.permissions.destroy', $permission) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50" type="submit">
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No permissions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
