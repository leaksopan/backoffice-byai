@extends('layouts.module')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Projects</h1>
            <p class="mt-2 text-sm text-slate-600">Manage your project list.</p>
        </div>
        <a class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" href="{{ route('pm.projects.create') }}">
            Create Project
        </a>
    </div>

    <div class="mt-6 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">
        No projects yet. Use the create button to add your first project.
    </div>
@endsection
