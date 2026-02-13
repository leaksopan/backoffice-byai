<?php

use Illuminate\Support\Facades\Route;
use Modules\AdminCenter\Http\Controllers\AdminCenterController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('admincenters', AdminCenterController::class)->names('admincenter');
});
