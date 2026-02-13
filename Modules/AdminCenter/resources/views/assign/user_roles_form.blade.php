@extends('layouts.module')

@section('content')
    <div class="max-w-4xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Assign Roles to User</h1>
            <p class="mt-2 text-sm text-slate-600">Select a user and manage their roles.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('ac.assign.user-roles') }}" class="rounded-lg border border-slate-200 bg-white p-6">
            <label class="block text-sm font-medium text-slate-700">Select User</label>
            <select name="user_id" class="mt-2 w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500" onchange="this.form.submit()">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @if ($selectedUser && $selectedUser->id === $user->id) selected @endif>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </form>

        @if ($selectedUser)
            <form class="space-y-4 rounded-lg border border-slate-200 bg-white p-6" method="POST" action="{{ route('ac.assign.user-roles.save') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                @if ($selectedUser->roles->pluck('id')->contains($role->id)) checked @endif
                            >
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>

                <div>
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                        Save Roles
                    </button>
                </div>
            </form>
        @else
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-600">
                No users available.
            </div>
        @endif
    </div>
@endsection
