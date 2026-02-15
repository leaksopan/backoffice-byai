<?php

namespace Modules\AdminCenter\Http\Controllers;

use App\Models\Module;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AcAssignmentsController
{
    public function userRolesForm(Request $request): View
    {
        $users = User::query()->orderBy('name')->get();
        $roles = Role::query()->orderBy('name')->get();
        $selectedUser = $this->resolveSelectedUser($request, $users);

        return view('admincenter::assign.user_roles_form', [
            'users' => $users,
            'roles' => $roles,
            'selectedUser' => $selectedUser,
        ]);
    }

    public function userRolesSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $roles = Role::query()->whereIn('id', $validated['roles'] ?? [])->get();
        $user->syncRoles($roles);

        return redirect()
            ->route('ac.assign.user-roles', ['user_id' => $user->id])
            ->with('status', 'User roles updated.');
    }

    public function rolePermissionsForm(Request $request): View
    {
        $roles = Role::query()->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();
        $selectedRole = $this->resolveSelectedRole($request, $roles);

        return view('admincenter::assign.role_permissions_form', [
            'roles' => $roles,
            'permissions' => $permissions,
            'selectedRole' => $selectedRole,
        ]);
    }

    public function rolePermissionsSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::query()->findOrFail($validated['role_id']);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('ac.assign.role-permissions', ['role_id' => $role->id])
            ->with('status', 'Role permissions updated.');
    }

    public function moduleAccessMatrix(Request $request): View
    {
        $roles = Role::query()->orderBy('name')->get();
        $modules = Module::query()->orderBy('sort_order')->get();
        $selectedRole = $this->resolveSelectedRole($request, $roles);

        return view('admincenter::assign.module_access_matrix', [
            'roles' => $roles,
            'modules' => $modules,
            'selectedRole' => $selectedRole,
            'modulePermissionNames' => $this->buildModulePermissionNames($modules),
        ]);
    }

    public function moduleAccessSave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::query()->findOrFail($validated['role_id']);
        $modules = Module::query()->orderBy('sort_order')->get();
        $modulePermissionNames = $this->buildModulePermissionNames($modules);

        $selected = collect($validated['permissions'] ?? [])
            ->intersect($modulePermissionNames)
            ->values()
            ->all();

        $existing = $role->permissions->pluck('name')->all();
        $keep = array_diff($existing, $modulePermissionNames->all());
        $role->syncPermissions(array_unique(array_merge($keep, $selected)));

        return redirect()
            ->route('ac.assign.module-access', ['role_id' => $role->id])
            ->with('status', 'Module access updated.');
    }

    private function resolveSelectedUser(Request $request, $users): ?User
    {
        $selectedId = $request->integer('user_id');

        if ($selectedId) {
            return $users->firstWhere('id', $selectedId);
        }

        return $users->first();
    }

    private function resolveSelectedRole(Request $request, $roles): ?Role
    {
        $selectedId = $request->integer('role_id');

        if ($selectedId) {
            return $roles->firstWhere('id', $selectedId);
        }

        return $roles->first();
    }

    private function buildModulePermissionNames($modules)
    {
        return $modules->flatMap(function (Module $module) {
            return [
                'access '.$module->key,
                $module->key.'.view',
                $module->key.'.create',
                $module->key.'.edit',
                $module->key.'.delete',
            ];
        })->values();
    }
}
