<?php

namespace Modules\AdminCenter\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class AcPermissionsController
{
    public function index(): View
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        return view('admincenter::permissions.index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(): View
    {
        return view('admincenter::permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('ac.permissions.index')
            ->with('status', 'Permission created successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()
            ->route('ac.permissions.index')
            ->with('status', 'Permission deleted successfully.');
    }
}
