<?php

use Illuminate\Support\Facades\Route;
use Modules\ExampleModules\Http\Controllers\ExampleModulesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('examplemodules', ExampleModulesController::class)->names('examplemodules');
});
