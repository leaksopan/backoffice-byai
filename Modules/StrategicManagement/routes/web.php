<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\Route;
use Modules\StrategicManagement\Http\Controllers\SmDashboardController;
use Modules\StrategicManagement\Http\Controllers\SmEvaluationsController;
use Modules\StrategicManagement\Http\Controllers\SmKpisController;
use Modules\StrategicManagement\Http\Controllers\SmRoadmapController;
use Modules\StrategicManagement\Http\Controllers\SmSettingsController;
use Modules\StrategicManagement\Http\Controllers\SmVisionsController;

Route::prefix('m/strategic-management')
    ->middleware(['auth', EnsureModuleAccess::class])
    ->name('sm.')
    ->group(function (): void {
        Route::get('/dashboard', [SmDashboardController::class, 'index'])
            ->name('dashboard')
            ->middleware('can:strategic-management.view')
            ->defaults('moduleKey', 'strategic-management');

        // Visions (Visi & Misi)
        Route::get('/visions', [SmVisionsController::class, 'index'])
            ->name('visions.index')
            ->middleware('can:strategic-management.view')
            ->defaults('moduleKey', 'strategic-management');

        Route::get('/visions/create', [SmVisionsController::class, 'create'])
            ->name('visions.create')
            ->middleware('can:strategic-management.create')
            ->defaults('moduleKey', 'strategic-management');

        Route::post('/visions', [SmVisionsController::class, 'store'])
            ->name('visions.store')
            ->middleware('can:strategic-management.create')
            ->defaults('moduleKey', 'strategic-management');

        Route::get('/visions/{id}/edit', [SmVisionsController::class, 'edit'])
            ->name('visions.edit')
            ->middleware('can:strategic-management.edit')
            ->defaults('moduleKey', 'strategic-management');

        Route::put('/visions/{id}', [SmVisionsController::class, 'update'])
            ->name('visions.update')
            ->middleware('can:strategic-management.edit')
            ->defaults('moduleKey', 'strategic-management');

        Route::delete('/visions/{id}', [SmVisionsController::class, 'destroy'])
            ->name('visions.destroy')
            ->middleware('can:strategic-management.delete')
            ->defaults('moduleKey', 'strategic-management');

        // KPIs
        Route::get('/kpis', [SmKpisController::class, 'index'])
            ->name('kpis.index')
            ->middleware('can:strategic-management.view')
            ->defaults('moduleKey', 'strategic-management');

        Route::get('/kpis/create', [SmKpisController::class, 'create'])
            ->name('kpis.create')
            ->middleware('can:strategic-management.create')
            ->defaults('moduleKey', 'strategic-management');

        Route::post('/kpis', [SmKpisController::class, 'store'])
            ->name('kpis.store')
            ->middleware('can:strategic-management.create')
            ->defaults('moduleKey', 'strategic-management');

        // Roadmap
        Route::get('/roadmap', [SmRoadmapController::class, 'index'])
            ->name('roadmap.index')
            ->middleware('can:strategic-management.view')
            ->defaults('moduleKey', 'strategic-management');

        // Evaluations
        Route::get('/evaluations', [SmEvaluationsController::class, 'index'])
            ->name('evaluations.index')
            ->middleware('can:strategic-management.view')
            ->defaults('moduleKey', 'strategic-management');

        // Settings
        Route::get('/settings', [SmSettingsController::class, 'index'])
            ->name('settings')
            ->middleware('can:strategic-management.edit')
            ->defaults('moduleKey', 'strategic-management');
    });
