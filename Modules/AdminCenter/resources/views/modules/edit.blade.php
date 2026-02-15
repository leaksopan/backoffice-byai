@extends('layouts.module')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Edit Module</h1>
            <p class="mt-2 text-sm text-slate-600">Update module metadata, order, and visibility.</p>
        </div>

        <form class="space-y-6 rounded-lg border border-slate-200 bg-white p-6" method="POST" action="{{ route('ac.modules.update', $module) }}">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">Module Key</label>
                <input class="mt-2 w-full rounded-lg border-slate-200 bg-slate-50 text-slate-600" type="text" value="{{ strtoupper($module->key) }}" disabled>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="name" type="text" value="{{ old('name', $module->name) }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Description</label>
                <textarea class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="description" rows="3">{{ old('description', $module->description) }}</textarea>
                @error('description') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Icon</label>
                    <input class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="icon" type="text" value="{{ old('icon', $module->icon) }}">
                    @error('icon') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Sort Order</label>
                    <input class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="sort_order" type="number" min="0" value="{{ old('sort_order', $module->sort_order) }}" required>
                    @error('sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Entry Route</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="entry_route" type="text" value="{{ old('entry_route', $module->entry_route) }}" required>
                @error('entry_route') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-500" @checked(old('is_active', $module->is_active))>
                Active module
            </label>

            <div class="flex items-center gap-3">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                    Save Changes
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900" href="{{ route('ac.modules.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
