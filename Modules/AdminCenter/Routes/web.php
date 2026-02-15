<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\Route;
use Modules\AdminCenter\Http\Controllers\AcAssignmentsController;
use Modules\AdminCenter\Http\Controllers\AcDashboardController;
use Modules\AdminCenter\Http\Controllers\AcModulesController;
use Modules\AdminCenter\Http\Controllers\AcPermissionsController;
use Modules\AdminCenter\Http\Controllers\AcRolesController;
use Modules\AdminCenter\Http\Controllers\AcUsersController;

Route::prefix('m/admin-center')
    ->middleware(['auth', EnsureModuleAccess::class])
    ->name('ac.')
    ->group(function (): void {
        Route::get('/dashboard', [AcDashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:admin-center.view')
            ->defaults('moduleKey', 'admin-center');

        Route::get('/users', [AcUsersController::class, 'index'])
            ->name('users.index')
            ->middleware('can:users.view')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/users/create', [AcUsersController::class, 'create'])
            ->name('users.create')
            ->middleware('can:users.create')
            ->defaults('moduleKey', 'admin-center');
        Route::post('/users', [AcUsersController::class, 'store'])
            ->name('users.store')
            ->middleware('can:users.create')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/users/{user}/edit', [AcUsersController::class, 'edit'])
            ->name('users.edit')
            ->middleware('can:users.edit')
            ->defaults('moduleKey', 'admin-center');
        Route::put('/users/{user}', [AcUsersController::class, 'update'])
            ->name('users.update')
            ->middleware('can:users.edit')
            ->defaults('moduleKey', 'admin-center');
        Route::delete('/users/{user}', [AcUsersController::class, 'destroy'])
            ->name('users.destroy')
            ->middleware('can:users.delete')
            ->defaults('moduleKey', 'admin-center');

        Route::get('/roles', [AcRolesController::class, 'index'])
            ->name('roles.index')
            ->middleware('can:roles.view')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/roles/create', [AcRolesController::class, 'create'])
            ->name('roles.create')
            ->middleware('can:roles.create')
            ->defaults('moduleKey', 'admin-center');
        Route::post('/roles', [AcRolesController::class, 'store'])
            ->name('roles.store')
            ->middleware('can:roles.create')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/roles/{role}/edit', [AcRolesController::class, 'edit'])
            ->name('roles.edit')
            ->middleware('can:roles.edit')
            ->defaults('moduleKey', 'admin-center');
        Route::put('/roles/{role}', [AcRolesController::class, 'update'])
            ->name('roles.update')
            ->middleware('can:roles.edit')
            ->defaults('moduleKey', 'admin-center');
        Route::delete('/roles/{role}', [AcRolesController::class, 'destroy'])
            ->name('roles.destroy')
            ->middleware('can:roles.delete')
            ->defaults('moduleKey', 'admin-center');

        Route::get('/permissions', [AcPermissionsController::class, 'index'])
            ->name('permissions.index')
            ->middleware('can:permissions.view')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/permissions/create', [AcPermissionsController::class, 'create'])
            ->name('permissions.create')
            ->middleware('can:permissions.create')
            ->defaults('moduleKey', 'admin-center');
        Route::post('/permissions', [AcPermissionsController::class, 'store'])
            ->name('permissions.store')
            ->middleware('can:permissions.create')
            ->defaults('moduleKey', 'admin-center');
        Route::delete('/permissions/{permission}', [AcPermissionsController::class, 'destroy'])
            ->name('permissions.destroy')
            ->middleware('can:permissions.delete')
            ->defaults('moduleKey', 'admin-center');

        Route::get('/modules', [AcModulesController::class, 'index'])
            ->name('modules.index')
            ->middleware('can:modules.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/modules/{module}/edit', [AcModulesController::class, 'edit'])
            ->name('modules.edit')
            ->middleware('can:modules.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::put('/modules/{module}', [AcModulesController::class, 'update'])
            ->name('modules.update')
            ->middleware('can:modules.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::patch('/modules/{module}/toggle', [AcModulesController::class, 'toggle'])
            ->name('modules.toggle')
            ->middleware('can:modules.manage')
            ->defaults('moduleKey', 'admin-center');

        Route::get('/assign/user-roles', [AcAssignmentsController::class, 'userRolesForm'])
            ->name('assign.user-roles')
            ->middleware('can:assignments.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::post('/assign/user-roles', [AcAssignmentsController::class, 'userRolesSave'])
            ->name('assign.user-roles.save')
            ->middleware('can:assignments.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/assign/role-permissions', [AcAssignmentsController::class, 'rolePermissionsForm'])
            ->name('assign.role-permissions')
            ->middleware('can:assignments.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::post('/assign/role-permissions', [AcAssignmentsController::class, 'rolePermissionsSave'])
            ->name('assign.role-permissions.save')
            ->middleware('can:assignments.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::get('/assign/module-access', [AcAssignmentsController::class, 'moduleAccessMatrix'])
            ->name('assign.module-access')
            ->middleware('can:assignments.manage')
            ->defaults('moduleKey', 'admin-center');
        Route::post('/assign/module-access', [AcAssignmentsController::class, 'moduleAccessSave'])
            ->name('assign.module-access.save')
            ->middleware('can:assignments.manage')
            ->defaults('moduleKey', 'admin-center');
    });
