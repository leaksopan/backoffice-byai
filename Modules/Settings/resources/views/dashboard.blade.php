@extends('layouts.module')

@section('content')
    <div class="space-y-5">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Settings Module</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Centralized application branding and configuration.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a class="rounded-2xl border border-slate-200/80 glass-panel p-5 transition hover:-translate-y-0.5 dark:border-slate-700/80" href="{{ route('settings.branding') }}">
                <div class="text-base font-semibold text-slate-900 dark:text-slate-50">Branding</div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Manage app name, tagline, logos, and favicon.
                </p>
            </a>
        </div>
    </div>
@endsection
