@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Example Modules Guide</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                In-app reference for creating new modules in Modulify.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">7 Steps To Add A New Module</h2>
            <ol class="mt-4 grid gap-3 text-sm text-slate-700 dark:text-slate-300">
                <li>1. Generate module folder with `php artisan module:make {ModuleName}`.</li>
                <li>2. Add routes under `Modules/{ModuleName}/routes/web.php` with `/m/{module-key}` prefix.</li>
                <li>3. Add controller methods and Blade views inside module folders.</li>
                <li>4. Insert module record into `modules` table with `entry_route` and `sort_order`.</li>
                <li>5. Insert module menus into `module_menus` using `module_key` and permissions.</li>
                <li>6. Ensure permissions exist: `access {k}`, `{k}.view`, `{k}.create`, `{k}.edit`, `{k}.delete`.</li>
                <li>7. Seed role assignments and verify entry via `/dashboard-modules`.</li>
            </ol>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a class="rounded-2xl border border-slate-200/80 glass-panel p-5 transition hover:-translate-y-0.5 dark:border-slate-700/80" href="{{ route('example.files') }}">
                <div class="text-base font-semibold text-slate-900 dark:text-slate-50">File Structure</div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Understand the role of each module file and folder.</p>
            </a>
            <a class="rounded-2xl border border-slate-200/80 glass-panel p-5 transition hover:-translate-y-0.5 dark:border-slate-700/80" href="{{ route('example.sidebar') }}">
                <div class="text-base font-semibold text-slate-900 dark:text-slate-50">Sidebar Setup</div>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Configure sidebar menus from `module_menus` records.</p>
            </a>
        </div>
    </div>
@endsection
