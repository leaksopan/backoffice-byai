@extends('layouts.module')

@section('content')
    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Create Project</h1>
            <p class="mt-2 text-sm text-slate-600">Fill the dynamic form based on schema configuration.</p>
        </div>

        @if ($moduleForm)
            <livewire:module-form-renderer :module-form="$moduleForm" />
        @else
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                Module form configuration is missing. Please seed module forms first.
            </div>
        @endif
    </div>
@endsection
