@extends('layouts.module')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Roles</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Manage role definitions.</p>
        </div>
        @can('roles.create')
            <a class="btn-primary" href="{{ route('ac.roles.create') }}">
                Create Role
            </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mt-6 table-glass">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Users</th>
                    <th class="px-4 py-3">Permissions</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($roles as $role)
                    <tr>
                        <td class="px-4 py-3">{{ $role->name }}</td>
                        <td class="px-4 py-3">{{ $role->users_count }}</td>
                        <td class="px-4 py-3">{{ $role->permissions_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                @can('roles.edit')
                                    <a class="rounded-md border border-slate-300/80 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-100/80 dark:border-slate-600 dark:hover:bg-slate-800/70" href="{{ route('ac.roles.edit', $role) }}">
                                        Edit
                                    </a>
                                @endcan
                                @can('roles.delete')
                                    <form method="POST" action="{{ route('ac.roles.destroy', $role) }}">
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
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No roles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
