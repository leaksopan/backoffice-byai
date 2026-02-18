<?php

use Illuminate\Support\Facades\Route;
use Modules\CostCenterManagement\Http\Controllers\Api\CostCenterApiController;
use Modules\CostCenterManagement\Http\Controllers\Api\AllocationProcessApiController;
use Modules\CostCenterManagement\Http\Controllers\Api\CostTransactionApiController;
use Modules\CostCenterManagement\Http\Controllers\Api\BudgetApiController;
use Modules\CostCenterManagement\Http\Controllers\Api\ServiceLineApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API endpoints untuk integrasi dengan sistem eksternal
| Semua routes menggunakan auth:sanctum middleware dan permission checks
|
*/

Route::middleware(['auth:sanctum'])->prefix('v1/cost-center-management')->name('api.ccm.')->group(function () {
    
    // Cost Center API - requires cost-center-management.view permission
    Route::middleware(['can:cost-center-management.view'])->prefix('cost-centers')->name('cost-centers.')->group(function () {
        Route::get('/', [CostCenterApiController::class, 'index'])->name('index');
        Route::get('/tree', [CostCenterApiController::class, 'tree'])->name('tree');
        Route::get('/{costCenter}', [CostCenterApiController::class, 'show'])->name('show');
        Route::get('/{costCenter}/descendants', [CostCenterApiController::class, 'descendants'])->name('descendants');
        Route::get('/{costCenter}/ancestors', [CostCenterApiController::class, 'ancestors'])->name('ancestors');
        Route::get('/by-org-unit/{organizationUnitId}', [CostCenterApiController::class, 'byOrganizationUnit'])->name('by-org-unit');
    });

    // Allocation Process API - requires cost-center-management.allocate permission
    Route::middleware(['can:cost-center-management.allocate'])->prefix('allocation')->name('allocation.')->group(function () {
        Route::post('/execute', [AllocationProcessApiController::class, 'execute'])->name('execute');
        Route::post('/rollback', [AllocationProcessApiController::class, 'rollback'])->name('rollback');
    });

    // Allocation viewing - requires cost-center-management.view permission
    Route::middleware(['can:cost-center-management.view'])->prefix('allocation')->name('allocation.')->group(function () {
        Route::get('/status', [AllocationProcessApiController::class, 'status'])->name('status');
        Route::get('/journals', [AllocationProcessApiController::class, 'journals'])->name('journals');
    });

    // Cost Transaction API
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::middleware(['can:cost-center-management.view'])->group(function () {
            Route::get('/', [CostTransactionApiController::class, 'index'])->name('index');
            Route::get('/summary', [CostTransactionApiController::class, 'summary'])->name('summary');
        });
        
        Route::middleware(['can:cost-center-management.create'])->group(function () {
            Route::post('/', [CostTransactionApiController::class, 'store'])->name('store');
        });
    });

    // Budget API
    Route::prefix('budgets')->name('budgets.')->group(function () {
        Route::middleware(['can:cost-center-management.view'])->group(function () {
            Route::get('/', [BudgetApiController::class, 'index'])->name('index');
            Route::get('/available', [BudgetApiController::class, 'available'])->name('available');
            Route::get('/variance', [BudgetApiController::class, 'variance'])->name('variance');
            Route::get('/utilization', [BudgetApiController::class, 'utilization'])->name('utilization');
        });
        
        Route::middleware(['can:cost-center-management.create'])->group(function () {
            Route::post('/', [BudgetApiController::class, 'store'])->name('store');
        });
    });

    // Service Line API - requires cost-center-management.view permission
    Route::middleware(['can:cost-center-management.view'])->prefix('service-lines')->name('service-lines.')->group(function () {
        Route::get('/', [ServiceLineApiController::class, 'index'])->name('index');
        Route::get('/{serviceLine}', [ServiceLineApiController::class, 'show'])->name('show');
        Route::get('/{serviceLine}/cost-analysis', [ServiceLineApiController::class, 'costAnalysis'])->name('cost-analysis');
        Route::get('/{serviceLine}/profitability', [ServiceLineApiController::class, 'profitability'])->name('profitability');
        Route::post('/compare', [ServiceLineApiController::class, 'compare'])->name('compare');
    });
});
