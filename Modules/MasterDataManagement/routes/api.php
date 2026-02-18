<?php

use Illuminate\Support\Facades\Route;
use Modules\MasterDataManagement\Http\Controllers\Api\OrganizationUnitApiController;
use Modules\MasterDataManagement\Http\Controllers\Api\ChartOfAccountApiController;
use Modules\MasterDataManagement\Http\Controllers\Api\FundingSourceApiController;
use Modules\MasterDataManagement\Http\Controllers\Api\ServiceCatalogApiController;
use Modules\MasterDataManagement\Http\Controllers\Api\TariffApiController;
use Modules\MasterDataManagement\Http\Controllers\Api\HumanResourceApiController;
use Modules\MasterDataManagement\Http\Controllers\Api\AssetApiController;

Route::prefix('mdm')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Organization Units API
        Route::get('organization-units', [OrganizationUnitApiController::class, 'index']);
        Route::get('organization-units/{id}', [OrganizationUnitApiController::class, 'show']);
        Route::get('organization-units/{id}/descendants', [OrganizationUnitApiController::class, 'descendants']);
        Route::get('organization-units/tree', [OrganizationUnitApiController::class, 'tree']);

        // Chart of Accounts API
        Route::get('chart-of-accounts', [ChartOfAccountApiController::class, 'index']);
        Route::get('chart-of-accounts/{id}', [ChartOfAccountApiController::class, 'show']);
        Route::get('chart-of-accounts/by-category/{category}', [ChartOfAccountApiController::class, 'byCategory']);
        Route::get('chart-of-accounts/postable', [ChartOfAccountApiController::class, 'postable']);

        // Funding Sources API
        Route::get('funding-sources', [FundingSourceApiController::class, 'index']);
        Route::get('funding-sources/{id}', [FundingSourceApiController::class, 'show']);
        Route::get('funding-sources/active-on/{date}', [FundingSourceApiController::class, 'activeOn']);

        // Service Catalog API
        Route::get('services', [ServiceCatalogApiController::class, 'index']);
        Route::get('services/{id}', [ServiceCatalogApiController::class, 'show']);
        Route::get('services/by-category/{category}', [ServiceCatalogApiController::class, 'byCategory']);
        Route::get('services/by-unit/{unitId}', [ServiceCatalogApiController::class, 'byUnit']);

        // Tariff API
        Route::get('tariffs/applicable', [TariffApiController::class, 'applicable']);
        Route::get('tariffs/{id}/breakdown', [TariffApiController::class, 'breakdown']);

        // Human Resource API
        Route::get('human-resources', [HumanResourceApiController::class, 'index']);
        Route::get('human-resources/{id}', [HumanResourceApiController::class, 'show']);
        Route::get('human-resources/by-unit/{unitId}', [HumanResourceApiController::class, 'byUnit']);
        Route::get('human-resources/{id}/assignments', [HumanResourceApiController::class, 'assignments']);

        // Asset API
        Route::get('assets', [AssetApiController::class, 'index']);
        Route::get('assets/{id}', [AssetApiController::class, 'show']);
        Route::get('assets/by-location/{unitId}', [AssetApiController::class, 'byLocation']);
        Route::get('assets/{id}/depreciation-schedule', [AssetApiController::class, 'depreciationSchedule']);
    });
