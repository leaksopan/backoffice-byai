@extends('layouts.module')

@section('content')
    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Admin Center</h1>
            <p class="mt-2 text-sm text-slate-600">Manage users, roles, permissions, and module access.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300" href="{{ route('ac.users.index') }}">
                <div class="text-sm font-semibold text-slate-900">Users</div>
                <div class="mt-2 text-xs text-slate-500">Manage application users</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300" href="{{ route('ac.roles.index') }}">
                <div class="text-sm font-semibold text-slate-900">Roles</div>
                <div class="mt-2 text-xs text-slate-500">Manage role definitions</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300" href="{{ route('ac.permissions.index') }}">
                <div class="text-sm font-semibold text-slate-900">Permissions</div>
                <div class="mt-2 text-xs text-slate-500">Manage permissions catalog</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300" href="{{ route('ac.assign.module-access') }}">
                <div class="text-sm font-semibold text-slate-900">Module Access Matrix</div>
                <div class="mt-2 text-xs text-slate-500">Assign module permissions per role</div>
            </a>
        </div>
    </div>
@endsection
