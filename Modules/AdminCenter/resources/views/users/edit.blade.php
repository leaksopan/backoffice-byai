@extends('layouts.module')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Edit User</h1>
            <p class="mt-2 text-sm text-slate-600">Update user profile and roles.</p>
        </div>

        <form class="space-y-6 rounded-lg border border-slate-200 bg-white p-6" method="POST" action="{{ route('ac.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700">Name</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700">Password (optional)</label>
                <input class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" name="password" type="password">
                @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            @can('assignments.manage')
                <div>
                    <label class="block text-sm font-medium text-slate-700">Roles</label>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    name="roles[]"
                                    value="{{ $role->id }}"
                                    class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                    @if (in_array($role->name, $user->roles->pluck('name')->all(), true)) checked @endif
                                >
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                    @error('roles.*') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endcan

            <div class="flex items-center gap-3">
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                    Update
                </button>
                <a class="text-sm text-slate-600 hover:text-slate-900" href="{{ route('ac.users.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
