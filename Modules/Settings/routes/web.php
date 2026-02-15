<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\SettingsDashboardController;

Route::prefix('m/settings')
    ->middleware(['auth', EnsureModuleAccess::class])
    ->name('settings.')
    ->group(function (): void {
        Route::get('/dashboard', [SettingsDashboardController::class, 'dashboard'])
            ->name('dashboard')
            ->middleware('can:settings.view')
            ->defaults('moduleKey', 'settings');

        Route::get('/branding', [SettingsDashboardController::class, 'branding'])
            ->name('branding')
            ->middleware('can:settings.edit')
            ->defaults('moduleKey', 'settings');

        Route::put('/branding', [SettingsDashboardController::class, 'updateBranding'])
            ->name('branding.update')
            ->middleware('can:settings.edit')
            ->defaults('moduleKey', 'settings');
    });
