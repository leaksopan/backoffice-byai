@extends('layouts.module')

@section('content')
    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Admin Center</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Manage users, roles, permissions, and module access.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a class="glass-card p-4 transition hover:-translate-y-0.5 hover:border-slate-300/80 dark:hover:border-slate-600/80" href="{{ route('ac.users.index') }}">
                <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">Users</div>
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Manage application users</div>
            </a>
            <a class="glass-card p-4 transition hover:-translate-y-0.5 hover:border-slate-300/80 dark:hover:border-slate-600/80" href="{{ route('ac.roles.index') }}">
                <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">Roles</div>
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Manage role definitions</div>
            </a>
            <a class="glass-card p-4 transition hover:-translate-y-0.5 hover:border-slate-300/80 dark:hover:border-slate-600/80" href="{{ route('ac.permissions.index') }}">
                <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">Permissions</div>
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Manage permissions catalog</div>
            </a>
            @can('modules.manage')
                <a class="glass-card p-4 transition hover:-translate-y-0.5 hover:border-slate-300/80 dark:hover:border-slate-600/80" href="{{ route('ac.modules.index') }}">
                    <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">Modules Management</div>
                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Sort and hide/unhide modules</div>
                </a>
            @endcan
            <a class="glass-card p-4 transition hover:-translate-y-0.5 hover:border-slate-300/80 dark:hover:border-slate-600/80" href="{{ route('ac.assign.module-access') }}">
                <div class="text-sm font-semibold text-slate-900 dark:text-slate-50">Module Access Matrix</div>
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">Assign module permissions per role</div>
            </a>
        </div>
    </div>
@endsection
