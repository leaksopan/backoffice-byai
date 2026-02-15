@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Sidebar Configuration</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Sidebar items are fully database-driven via `module_menus`.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Required `module_menus` Fields</h2>
            <ul class="mt-3 grid gap-2 text-sm text-slate-700 dark:text-slate-300">
                <li>`module_key`: active module key (example: `example-modules`).</li>
                <li>`section`: sidebar group label (`MAIN`, `ADMIN`).</li>
                <li>`label`: item text shown in expanded mode and tooltip.</li>
                <li>`route_name` or `url`: destination target.</li>
                <li>`permission_name`: optional permission to hide unauthorized item.</li>
                <li>`sort_order`: item ordering inside each section.</li>
                <li>`is_active`: toggle to hide/unhide menu item.</li>
            </ul>
        </div>

        <div class="rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-50">Rendering Behavior</h2>
            <ul class="mt-3 grid gap-2 text-sm text-slate-700 dark:text-slate-300">
                <li>Only current module menus are loaded by `module_key`.</li>
                <li>Menus are sorted by `section` then `sort_order`.</li>
                <li>Collapsed sidebar shows icon-only with per-item hover tooltip.</li>
                <li>Sidebar never auto-expands on hover in desktop collapsed mode.</li>
            </ul>
        </div>
    </div>
@endsection
