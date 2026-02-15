@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Modules Management</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Sort modules and control hide/unhide visibility.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="table-glass">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Module</th>
                        <th class="px-4 py-3">Entry Route</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($modules as $module)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900 dark:text-slate-50">{{ $module->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ strtoupper($module->key) }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $module->entry_route }}</td>
                            <td class="px-4 py-3">{{ $module->sort_order }}</td>
                            <td class="px-4 py-3">
                                @if ($module->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">ACTIVE</span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200">HIDDEN</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a class="rounded-md border border-slate-300/80 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-100/80 dark:border-slate-600 dark:hover:bg-slate-800/70" href="{{ route('ac.modules.edit', $module) }}">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('ac.modules.toggle', $module) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $module->is_active ? 0 : 1 }}">
                                        <button class="rounded-md border border-slate-300/80 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-200 transition hover:bg-slate-100/80 dark:border-slate-600 dark:hover:bg-slate-800/70" type="submit">
                                            {{ $module->is_active ? 'Hide' : 'Unhide' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No modules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
