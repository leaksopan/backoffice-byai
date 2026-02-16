<?php

namespace Modules\AdminCenter\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\AdminCenter\Http\Requests\StorePermissionRequest;
use Modules\AdminCenter\Http\Requests\UpdatePermissionRequest;
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

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('ac.permissions.index')
            ->with('status', 'Permission created successfully.');
    }

    public function edit(Permission $permission): View
    {
        return view('admincenter::permissions.edit', [
            'permission' => $permission,
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validated();

        $permission->update([
            'name' => $validated['name'],
        ]);

        return redirect()
            ->route('ac.permissions.index')
            ->with('status', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()
            ->route('ac.permissions.index')
            ->with('status', 'Permission deleted successfully.');
    }
}
