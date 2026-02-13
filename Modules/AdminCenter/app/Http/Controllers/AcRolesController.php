<?php

namespace Modules\AdminCenter\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AcRolesController
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->withCount('permissions')
            ->orderBy('name')
            ->get();

        return view('admincenter::roles.index', [
            'roles' => $roles,
        ]);
    }

    public function create(): View
    {
        return view('admincenter::roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('ac.roles.index')
            ->with('status', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        return view('admincenter::roles.edit', [
            'role' => $role,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->withErrors(['role' => 'Super admin role cannot be modified.']);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('ac.roles.index')
            ->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->withErrors(['role' => 'Super admin role cannot be deleted.']);
        }

        $role->delete();

        return redirect()
            ->route('ac.roles.index')
            ->with('status', 'Role deleted successfully.');
    }
}
