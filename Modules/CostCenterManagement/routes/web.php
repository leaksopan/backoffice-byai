<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureModuleAccess;
use Modules\CostCenterManagement\Http\Controllers\CostCenterManagementController;
use Modules\CostCenterManagement\Http\Controllers\CostCenterController;
use Modules\CostCenterManagement\Http\Controllers\AllocationProcessController;
use Modules\CostCenterManagement\Http\Controllers\AllocationRuleApprovalController;
use Modules\CostCenterManagement\Http\Controllers\BudgetRevisionController;
use Modules\CostCenterManagement\Http\Controllers\CostCenterDashboardController;
use Modules\CostCenterManagement\Http\Controllers\ReportController;
use Modules\CostCenterManagement\Http\Controllers\AuditTrailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your module. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', EnsureModuleAccess::class])
    ->prefix('m/cost-center-management')
    ->name('ccm.')
    ->group(function () {
        // Main dashboard - redirect to dashboard.index
        Route::get('/', function () {
            return redirect()->route('ccm.dashboard.index');
        })->name('dashboard')
            ->defaults('moduleKey', 'cost-center-management');

        // Cost Center Routes
        Route::get('/cost-centers', [CostCenterController::class, 'index'])
            ->name('cost-centers.index')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/cost-centers/create', [CostCenterController::class, 'create'])
            ->name('cost-centers.create')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/cost-centers', [CostCenterController::class, 'store'])
            ->name('cost-centers.store')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/cost-centers/{costCenter}/edit', [CostCenterController::class, 'edit'])
            ->name('cost-centers.edit')
            ->defaults('moduleKey', 'cost-center-management');

        Route::put('/cost-centers/{costCenter}', [CostCenterController::class, 'update'])
            ->name('cost-centers.update')
            ->defaults('moduleKey', 'cost-center-management');

        Route::delete('/cost-centers/{costCenter}', [CostCenterController::class, 'destroy'])
            ->name('cost-centers.destroy')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/cost-centers/tree', [CostCenterController::class, 'tree'])
            ->name('cost-centers.tree')
            ->defaults('moduleKey', 'cost-center-management');

        // Allocation Process Routes
        Route::get('/allocation-process', [AllocationProcessController::class, 'index'])
            ->name('allocation-process.index')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/allocation-process/create', [AllocationProcessController::class, 'create'])
            ->name('allocation-process.create')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/allocation-process/execute', [AllocationProcessController::class, 'execute'])
            ->name('allocation-process.execute')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/allocation-process/status', [AllocationProcessController::class, 'status'])
            ->name('allocation-process.status')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/allocation-process/{batchId}/review', [AllocationProcessController::class, 'review'])
            ->name('allocation-process.review')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/allocation-process/{batchId}/post', [AllocationProcessController::class, 'post'])
            ->name('allocation-process.post')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/allocation-process/{batchId}/rollback', [AllocationProcessController::class, 'rollback'])
            ->name('allocation-process.rollback')
            ->defaults('moduleKey', 'cost-center-management');

        // Approval Routes - Allocation Rules
        Route::get('/approval/allocation-rules', [AllocationRuleApprovalController::class, 'index'])
            ->name('approval.allocation-rules')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/approval/allocation-rules/{allocationRule}/submit', [AllocationRuleApprovalController::class, 'submit'])
            ->name('approval.allocation-rules.submit')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/approval/allocation-rules/{allocationRule}/approve', [AllocationRuleApprovalController::class, 'approve'])
            ->name('approval.allocation-rules.approve')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/approval/allocation-rules/{allocationRule}/reject', [AllocationRuleApprovalController::class, 'reject'])
            ->name('approval.allocation-rules.reject')
            ->defaults('moduleKey', 'cost-center-management');

        // Approval Routes - Budget Revisions
        Route::get('/budget-revisions', [BudgetRevisionController::class, 'index'])
            ->name('budget-revisions.index')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/budget-revisions/{budget}/create', [BudgetRevisionController::class, 'create'])
            ->name('budget-revisions.create')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/budget-revisions/{budget}', [BudgetRevisionController::class, 'store'])
            ->name('budget-revisions.store')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/budget-revisions/{budget}/approve', [BudgetRevisionController::class, 'approve'])
            ->name('budget-revisions.approve')
            ->defaults('moduleKey', 'cost-center-management');

        Route::post('/budget-revisions/{budget}/reject', [BudgetRevisionController::class, 'reject'])
            ->name('budget-revisions.reject')
            ->defaults('moduleKey', 'cost-center-management');

        // Dashboard Routes
        Route::get('/dashboard', [CostCenterDashboardController::class, 'index'])
            ->name('dashboard.index')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/dashboard/{costCenter}', [CostCenterDashboardController::class, 'show'])
            ->name('dashboard.show')
            ->defaults('moduleKey', 'cost-center-management');

        // Dashboard AJAX Routes
        Route::get('/dashboard/{costCenter}/real-time', [CostCenterDashboardController::class, 'realTimeMonitoring'])
            ->name('dashboard.real-time')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/dashboard/{costCenter}/budget-vs-actual', [CostCenterDashboardController::class, 'budgetVsActual'])
            ->name('dashboard.budget-vs-actual')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/dashboard/{costCenter}/variance-analysis', [CostCenterDashboardController::class, 'varianceAnalysis'])
            ->name('dashboard.variance-analysis')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/dashboard/{costCenter}/cost-distribution', [CostCenterDashboardController::class, 'costDistribution'])
            ->name('dashboard.cost-distribution')
            ->defaults('moduleKey', 'cost-center-management');

        // Report Routes
        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/reports/cost-center-summary', [ReportController::class, 'costCenterSummary'])
            ->name('reports.cost-center-summary')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/reports/cost-allocation-detail', [ReportController::class, 'costAllocationDetail'])
            ->name('reports.cost-allocation-detail')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/reports/budget-vs-actual', [ReportController::class, 'budgetVsActual'])
            ->name('reports.budget-vs-actual')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/reports/variance-analysis', [ReportController::class, 'varianceAnalysis'])
            ->name('reports.variance-analysis')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/reports/trend-analysis', [ReportController::class, 'trendAnalysis'])
            ->name('reports.trend-analysis')
            ->defaults('moduleKey', 'cost-center-management');

        // Audit Trail Routes
        Route::get('/audit-trail', [AuditTrailController::class, 'index'])
            ->name('audit-trail.index')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/audit-trail/{modelType}/{modelId}', [AuditTrailController::class, 'show'])
            ->name('audit-trail.show')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/audit-trail/summary', [AuditTrailController::class, 'summary'])
            ->name('audit-trail.summary')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/audit-trail/export', [AuditTrailController::class, 'export'])
            ->name('audit-trail.export')
            ->defaults('moduleKey', 'cost-center-management');

        Route::get('/audit-trail/user/{userId}', [AuditTrailController::class, 'userActivity'])
            ->name('audit-trail.user-activity')
            ->defaults('moduleKey', 'cost-center-management');
    });
