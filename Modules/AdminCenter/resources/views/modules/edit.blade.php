@extends('layouts.module')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Edit Module</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Update module metadata, order, and visibility.</p>
        </div>

        <form class="glass-card space-y-6 p-6" method="POST" action="{{ route('ac.modules.update', $module) }}">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Module Key</label>
                <input class="input-glass mt-2 w-full opacity-80" type="text" value="{{ strtoupper($module->key) }}" disabled>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                <input class="input-glass mt-2 w-full" name="name" type="text" value="{{ old('name', $module->name) }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Description</label>
                <textarea class="input-glass mt-2 w-full" name="description" rows="3">{{ old('description', $module->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Icon</label>
                    <input class="input-glass mt-2 w-full" name="icon" type="text" value="{{ old('icon', $module->icon) }}">
                    @error('icon') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Sort Order</label>
                    <input class="input-glass mt-2 w-full" name="sort_order" type="number" min="0" value="{{ old('sort_order', $module->sort_order) }}" required>
                    @error('sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Entry Route</label>
                <input class="input-glass mt-2 w-full" name="entry_route" type="text" value="{{ old('entry_route', $module->entry_route) }}" required>
                @error('entry_route') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 bg-white/80 text-sky-600 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-900/70 dark:text-sky-400" @checked(old('is_active', $module->is_active))>
                Active module
            </label>

            <div class="flex items-center gap-3">
                <button class="btn-primary" type="submit">
                    Save Changes
                </button>
                <a class="text-sm text-slate-600 dark:text-slate-300 transition hover:text-slate-900 dark:hover:text-slate-100" href="{{ route('ac.modules.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
