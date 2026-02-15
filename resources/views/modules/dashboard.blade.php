@extends('layouts.modules')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Modules Dashboard</h1>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Choose a module you have access to.</p>
    </div>

    @if ($modules->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white/70 p-8 text-center text-slate-600 backdrop-blur dark:border-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
            No modules available for your account.
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($modules as $module)
                <a class="group rounded-2xl border border-slate-200/80 glass-panel p-6 transition hover:-translate-y-0.5 hover:border-slate-300 dark:border-slate-700/80" href="{{ url('/m/'.$module->key) }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl glass-chip text-slate-700 dark:text-slate-200">
                                @if ($module->icon && str_starts_with($module->icon, 'heroicon-'))
                                    <span class="h-5 w-5">
                                        <x-menu-icon :name="$module->icon" />
                                    </span>
                                @else
                                    <span class="text-sm font-bold">{{ strtoupper(substr($module->name, 0, 1)) }}</span>
                                @endif
                            </span>
                            <div class="text-lg font-semibold text-slate-900 dark:text-slate-50">{{ $module->name }}</div>
                        </div>
                        <div class="rounded-full glass-chip px-3 py-1 text-xs font-semibold uppercase text-slate-600 dark:text-slate-200">
                            {{ strtoupper($module->key) }}
                        </div>
                    </div>
                    @if ($module->description)
                        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ $module->description }}</p>
                    @else
                        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Open module</p>
                    @endif
                    <div class="mt-5 text-sm font-medium text-slate-700 group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-slate-100">
                        Enter module →
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
