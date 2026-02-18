<?php

use App\Http\Middleware\EnsureModuleAccess;
use Illuminate\Support\Facades\Route;
use Modules\MasterDataManagement\Http\Controllers\MdmDashboardController;
use Modules\MasterDataManagement\Http\Controllers\OrganizationUnitController;
use Modules\MasterDataManagement\Http\Controllers\ChartOfAccountController;
use Modules\MasterDataManagement\Http\Controllers\FundingSourceController;
use Modules\MasterDataManagement\Http\Controllers\ServiceCatalogController;
use Modules\MasterDataManagement\Http\Controllers\TariffController;
use Modules\MasterDataManagement\Http\Controllers\HumanResourceController;
use Modules\MasterDataManagement\Http\Controllers\AssetController;

Route::prefix('m/master-data-management')
    ->middleware(['auth', EnsureModuleAccess::class.':master-data-management'])
    ->group(function () {
        Route::get('/dashboard', [MdmDashboardController::class, 'index'])
            ->name('mdm.dashboard');

        // Organization Units
        Route::resource('organization-units', OrganizationUnitController::class)
            ->names('mdm.organization-units');
        
        Route::get('organization-units-tree', [OrganizationUnitController::class, 'tree'])
            ->name('mdm.organization-units.tree');

        // Chart of Accounts
        Route::resource('chart-of-accounts', ChartOfAccountController::class)
            ->names('mdm.coa');
        
        Route::get('coa/export', [ChartOfAccountController::class, 'export'])
            ->name('mdm.coa.export');
        
        Route::post('coa/import', [ChartOfAccountController::class, 'import'])
            ->name('mdm.coa.import');

        // Funding Sources
        Route::resource('funding-sources', FundingSourceController::class)
            ->names('mdm.funding-sources');
        
        Route::post('funding-sources/check-availability', [FundingSourceController::class, 'checkAvailability'])
            ->name('mdm.funding-sources.check-availability');

        // Service Catalogs
        Route::resource('services', ServiceCatalogController::class)
            ->names('mdm.services');
        
        Route::get('services/search/{code}', [ServiceCatalogController::class, 'searchByCode'])
            ->name('mdm.services.search');

        // Tariffs
        Route::resource('tariffs', TariffController::class)
            ->names('mdm.tariffs');
        
        Route::get('tariffs/service/{serviceId}/history', [TariffController::class, 'history'])
            ->name('mdm.tariffs.history');
        
        Route::post('tariffs/get-applicable', [TariffController::class, 'getApplicableTariff'])
            ->name('mdm.tariffs.get-applicable');

        // Human Resources
        Route::resource('human-resources', HumanResourceController::class)
            ->names('mdm.human-resources');
        
        Route::get('human-resources/{humanResource}/assignments', [HumanResourceController::class, 'assignments'])
            ->name('mdm.human-resources.assignments');
        
        Route::post('human-resources/{humanResource}/assignments', [HumanResourceController::class, 'storeAssignment'])
            ->name('mdm.human-resources.assignments.store');

        // Assets
        Route::get('assets/depreciation-report', [AssetController::class, 'depreciationReport'])
            ->name('mdm.assets.depreciation-report');
        Route::resource('assets', AssetController::class)
            ->names('mdm.assets');
    });

