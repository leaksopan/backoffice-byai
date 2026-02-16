<?php

namespace Modules\AdminCenter\Http\Controllers;

use App\Models\Module;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\AdminCenter\Http\Requests\SaveModuleAccessRequest;
use Modules\AdminCenter\Http\Requests\SaveRolePermissionsRequest;
use Modules\AdminCenter\Http\Requests\SaveUserRolesRequest;
use Modules\AdminCenter\Services\AssignmentService;
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

    public function userRolesSave(SaveUserRolesRequest $request, AssignmentService $assignmentService): RedirectResponse
    {
        $validated = $request->validated();
        $user = $assignmentService->syncUserRoles($validated['user_id'], $validated['roles'] ?? []);

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

    public function rolePermissionsSave(SaveRolePermissionsRequest $request, AssignmentService $assignmentService): RedirectResponse
    {
        $validated = $request->validated();
        $role = $assignmentService->syncRolePermissions($validated['role_id'], $validated['permissions'] ?? []);

        return redirect()
            ->route('ac.assign.role-permissions', ['role_id' => $role->id])
            ->with('status', 'Role permissions updated.');
    }

    public function moduleAccessMatrix(Request $request, AssignmentService $assignmentService): View
    {
        $roles = Role::query()->orderBy('name')->get();
        $modules = Module::query()->orderBy('sort_order')->get();
        $selectedRole = $this->resolveSelectedRole($request, $roles);

        return view('admincenter::assign.module_access_matrix', [
            'roles' => $roles,
            'modules' => $modules,
            'selectedRole' => $selectedRole,
            'modulePermissionNames' => $assignmentService->buildModulePermissionNames($modules),
        ]);
    }

    public function moduleAccessSave(SaveModuleAccessRequest $request, AssignmentService $assignmentService): RedirectResponse
    {
        $validated = $request->validated();
        $role = $assignmentService->syncModuleAccess($validated['role_id'], $validated['permissions'] ?? []);

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
}
