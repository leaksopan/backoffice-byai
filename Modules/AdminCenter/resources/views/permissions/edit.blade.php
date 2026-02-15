@extends('layouts.module')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Edit Permission</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Update permission name.</p>
        </div>

        <form class="space-y-6 rounded-2xl border border-slate-200/80 glass-panel p-6 dark:border-slate-700/80" method="POST" action="{{ route('ac.permissions.update', $permission) }}">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Permission Name</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 bg-white/70 shadow-sm focus:border-slate-500 focus:ring-slate-500 dark:border-slate-700 dark:bg-slate-900/70" name="name" type="text" value="{{ old('name', $permission->name) }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-sky-600 dark:hover:bg-sky-500" type="submit">
                    Update
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100" href="{{ route('ac.permissions.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
