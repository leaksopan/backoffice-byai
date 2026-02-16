@extends('layouts.module')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Edit Permission</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Update permission name.</p>
        </div>

        <form class="glass-card space-y-6 p-6" method="POST" action="{{ route('ac.permissions.update', $permission) }}">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Permission Name</label>
                <input class="input-glass mt-2 w-full" name="name" type="text" value="{{ old('name', $permission->name) }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <button class="btn-primary" type="submit">
                    Update
                </button>
                <a class="text-sm text-slate-600 dark:text-slate-300 transition hover:text-slate-900 dark:hover:text-slate-100" href="{{ route('ac.permissions.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
