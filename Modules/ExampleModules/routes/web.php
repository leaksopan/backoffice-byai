<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\Route;
use Modules\ExampleModules\Http\Controllers\ExampleModulesController;

Route::prefix('m/example-modules')
    ->middleware(['auth', EnsureModuleAccess::class])
    ->name('example.')
    ->group(function (): void {
        Route::get('/dashboard', [ExampleModulesController::class, 'dashboard'])
            ->name('dashboard')
            ->middleware('can:example-modules.view')
            ->defaults('moduleKey', 'example-modules');

        Route::get('/files', [ExampleModulesController::class, 'files'])
            ->name('files')
            ->middleware('can:example-modules.view')
            ->defaults('moduleKey', 'example-modules');

        Route::get('/sidebar', [ExampleModulesController::class, 'sidebar'])
            ->name('sidebar')
            ->middleware('can:example-modules.view')
            ->defaults('moduleKey', 'example-modules');
    });
