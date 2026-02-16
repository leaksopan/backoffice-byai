@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Module File Structure</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Standard structure used by Modulify modules.
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80">
            <pre class="overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs text-slate-100"><code>Modules/
  ExampleModules/
    app/Http/Controllers/
    app/Providers/
    routes/web.php
    resources/views/
    database/seeders/
    module.json</code></pre>

            <div class="mt-4 grid gap-3 text-sm text-slate-700 dark:text-slate-300">
                <div><strong>Controller:</strong> request handling and returning Blade views.</div>
                <div><strong>routes/web.php:</strong> module routes with auth + module access middleware.</div>
                <div><strong>resources/views:</strong> UI pages using `layouts.module` for shared shell.</div>
                <div><strong>database/seeders:</strong> optional seed data for module-specific needs.</div>
                <div><strong>module.json:</strong> module metadata and service providers.</div>
            </div>
        </div>
    </div>
@endsection
