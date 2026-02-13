<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\Route;
use Modules\ProjectManagement\Http\Controllers\PmDashboardController;
use Modules\ProjectManagement\Http\Controllers\PmProjectsController;
use Modules\ProjectManagement\Http\Controllers\PmSettingsController;

Route::prefix('m/project-management')
    ->middleware(['auth', EnsureModuleAccess::class])
    ->name('pm.')
    ->group(function (): void {
        Route::get('/dashboard', [PmDashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:project-management.view')
            ->defaults('moduleKey', 'project-management');

        Route::get('/projects', [PmProjectsController::class, 'index'])
            ->name('projects.index')
            ->middleware('can:project-management.view')
            ->defaults('moduleKey', 'project-management');

        Route::get('/projects/create', [PmProjectsController::class, 'create'])
            ->name('projects.create')
            ->middleware('can:project-management.create')
            ->defaults('moduleKey', 'project-management');

        Route::get('/settings', [PmSettingsController::class, 'index'])
            ->name('settings')
            ->middleware('can:project-management.edit')
            ->defaults('moduleKey', 'project-management');
    });
