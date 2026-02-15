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
            <a class="glass-card p-5 transition hover:-translate-y-0.5 hover:border-slate-300/80 dark:hover:border-slate-600/80" href="{{ route('settings.branding') }}">
                <div class="text-base font-semibold text-slate-900 dark:text-slate-50">Branding</div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Manage app name, tagline, logos, and favicon.
                </p>
            </a>
        </div>
    </div>
@endsection
