@extends('layouts.module')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Module Access Matrix</h1>
            <p class="mt-2 text-sm text-slate-600">Assign module permissions per role.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('ac.assign.module-access') }}" class="rounded-lg border border-slate-200 bg-white p-6">
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
            @php
                $rolePermissions = $selectedRole->permissions->pluck('name')->all();
            @endphp

            <form method="POST" action="{{ route('ac.assign.module-access.save') }}">
                @csrf
                <input type="hidden" name="role_id" value="{{ $selectedRole->id }}">

                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Module</th>
                                <th class="px-4 py-3">Access</th>
                                <th class="px-4 py-3">View</th>
                                <th class="px-4 py-3">Create</th>
                                <th class="px-4 py-3">Edit</th>
                                <th class="px-4 py-3">Delete</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($modules as $module)
                                @php
                                    $access = 'access '.$module->key;
                                    $view = $module->key.'.view';
                                    $create = $module->key.'.create';
                                    $edit = $module->key.'.edit';
                                    $delete = $module->key.'.delete';
                                @endphp
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900">{{ $module->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $module->key }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $access }}" @if (in_array($access, $rolePermissions, true)) checked @endif>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $view }}" @if (in_array($view, $rolePermissions, true)) checked @endif>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $create }}" @if (in_array($create, $rolePermissions, true)) checked @endif>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $edit }}" @if (in_array($edit, $rolePermissions, true)) checked @endif>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="checkbox" name="permissions[]" value="{{ $delete }}" @if (in_array($delete, $rolePermissions, true)) checked @endif>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">No modules found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800" type="submit">
                        Save Module Access
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
