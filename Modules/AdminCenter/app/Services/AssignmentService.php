<?php

namespace Modules\AdminCenter\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class AssignmentService
{
    public function syncUserRoles(int $userId, array $roleIds): User
    {
        $user = User::query()->findOrFail($userId);
        $roles = Role::query()->whereIn('id', $roleIds)->get();

        $user->syncRoles($roles);

        return $user;
    }

    public function syncRolePermissions(int $roleId, array $permissionNames): Role
    {
        $role = Role::query()->findOrFail($roleId);
        $role->syncPermissions($permissionNames);

        return $role;
    }

    public function syncModuleAccess(int $roleId, array $selectedPermissions): Role
    {
        $role = Role::query()->findOrFail($roleId);
        $modules = Module::query()->orderBy('sort_order')->get();
        $modulePermissionNames = $this->buildModulePermissionNames($modules);

        $selected = collect($selectedPermissions)
            ->intersect($modulePermissionNames)
            ->values()
            ->all();

        $existing = $role->permissions->pluck('name')->all();
        $keep = array_diff($existing, $modulePermissionNames->all());

        $role->syncPermissions(array_unique(array_merge($keep, $selected)));

        return $role;
    }

    public function buildModulePermissionNames(Collection $modules): Collection
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
