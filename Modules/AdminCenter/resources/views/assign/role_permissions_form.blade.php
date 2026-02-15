@extends('layouts.module')

@section('content')
    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-50">Assign Permissions to Role</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Select a role and manage its permissions.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('ac.assign.role-permissions') }}" class="glass-card p-6">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Select Role</label>
            <select name="role_id" class="input-glass mt-2 w-full" onchange="this.form.submit()">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @if ($selectedRole && $selectedRole->id === $role->id) selected @endif>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </form>

        @if ($selectedRole)
            <form class="space-y-4 glass-card p-6" method="POST" action="{{ route('ac.assign.role-permissions.save') }}">
                @csrf
                <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->name }}"
                                class="rounded border-slate-300 bg-white/80 text-sky-600 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-900/70 dark:text-sky-400"
                                @if ($selectedRole->permissions->pluck('name')->contains($permission->name)) checked @endif
                            >
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>

                <div>
                    <button class="btn-primary" type="submit">
                        Save Permissions
                    </button>
                </div>
            </form>
        @else
            <div class="glass-card rounded-lg border border-dashed border-slate-300/80 p-6 text-sm text-slate-600 dark:border-slate-700/80 dark:text-slate-300">
                No roles available.
            </div>
        @endif
    </div>
@endsection
