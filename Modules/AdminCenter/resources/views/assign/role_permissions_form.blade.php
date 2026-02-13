@extends('layouts.module')

@section('content')
    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Assign Permissions to Role</h1>
            <p class="mt-2 text-sm text-slate-600">Select a role and manage its permissions.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('ac.assign.role-permissions') }}" class="rounded-lg border border-slate-200 bg-white p-6">
            <label class="block text-sm font-medium text-slate-700">Select Role</label>
            <select name="role_id" class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" onchange="this.form.submit()">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @if ($selectedRole && $selectedRole->id === $role->id) selected @endif>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </form>

        @if ($selectedRole)
            <form class="space-y-4 rounded-lg border border-slate-200 bg-white p-6" method="POST" action="{{ route('ac.assign.role-permissions.save') }}">
                @csrf
                <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->name }}"
                                class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                @if ($selectedRole->permissions->pluck('name')->contains($permission->name)) checked @endif
                            >
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>

                <div>
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                        Save Permissions
                    </button>
                </div>
            </form>
        @else
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                No roles available.
            </div>
        @endif
    </div>
@endsection
