@extends('layouts.module')

@section('content')
    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Project Management</h1>
            <p class="mt-2 text-sm text-slate-600">Welcome to the Project Management module dashboard.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300" href="{{ route('pm.projects.index') }}">
                <div class="text-sm font-semibold text-slate-900">Projects</div>
                <div class="mt-2 text-xs text-slate-500">View project list</div>
            </a>
            <a class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm hover:border-slate-300" href="{{ route('pm.settings') }}">
                <div class="text-sm font-semibold text-slate-900">Settings</div>
                <div class="mt-2 text-xs text-slate-500">Manage module settings</div>
            </a>
        </div>
    </div>
@endsection
