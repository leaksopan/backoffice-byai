@extends('layouts.modules')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Modules Dashboard</h1>
        <p class="mt-2 text-sm text-slate-600">Choose a module you have access to.</p>
    </div>

    @if ($modules->isEmpty())
        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">
            No modules available for your account.
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($modules as $module)
                <a class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md" href="{{ url('/m/'.$module->key) }}">
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-semibold text-slate-900">{{ $module->name }}</div>
                        <div class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold uppercase text-slate-500">
                            {{ $module->key }}
                        </div>
                    </div>
                    @if ($module->description)
                        <p class="mt-3 text-sm text-slate-600">{{ $module->description }}</p>
                    @else
                        <p class="mt-3 text-sm text-slate-500">Open module</p>
                    @endif
                    <div class="mt-5 text-sm font-medium text-slate-700 group-hover:text-slate-900">
                        Enter module →
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
