<?php

use Illuminate\Support\Facades\Route;
use Modules\StrategicManagement\Http\Controllers\StrategicManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('strategicmanagements', StrategicManagementController::class)->names('strategicmanagement');
});
