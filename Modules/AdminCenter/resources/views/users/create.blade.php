@extends('layouts.module')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Create User</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Add a new user to the system.</p>
        </div>

        <form class="glass-card space-y-6 p-6" method="POST" action="{{ route('ac.users.store') }}">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Name</label>
                <input class="input-glass mt-2 w-full" name="name" type="text" value="{{ old('name') }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Email</label>
                <input class="input-glass mt-2 w-full" name="email" type="email" value="{{ old('email') }}" required>
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Password</label>
                <input class="input-glass mt-2 w-full" name="password" type="password" required>
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            @can('assignments.manage')
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Roles</label>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-slate-300 bg-white/80 text-sky-600 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-900/70 dark:text-sky-400">
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                    @error('roles.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endcan

            <div class="flex items-center gap-3">
                <button class="btn-primary" type="submit">
                    Save
                </button>
                <a class="text-sm text-slate-600 dark:text-slate-300 transition hover:text-slate-900 dark:hover:text-slate-100" href="{{ route('ac.users.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
