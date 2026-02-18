<?php

namespace Modules\CostCenterManagement\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        \Modules\MasterDataManagement\Events\MasterDataUpdated::class => [
            \Modules\CostCenterManagement\Listeners\UpdateCostCenterOnOrgUnitChange::class,
            \Modules\CostCenterManagement\Listeners\ReallocateCostOnHRAssignmentChange::class,
            \Modules\CostCenterManagement\Listeners\DeactivateCostCenterOnOrgUnitDeactivation::class,
        ],
        \Modules\CostCenterManagement\Events\BudgetThresholdExceeded::class => [
            \Modules\CostCenterManagement\Listeners\SendBudgetWarningNotification::class,
        ],
        \Modules\CostCenterManagement\Events\AllocationCompleted::class => [
            \Modules\CostCenterManagement\Listeners\SendAllocationCompletedNotification::class,
        ],
        \Modules\CostCenterManagement\Events\AllocationRuleApprovalRequested::class => [
            \Modules\CostCenterManagement\Listeners\SendAllocationRuleApprovalRequestedNotification::class,
        ],
        \Modules\CostCenterManagement\Events\BudgetRevisionApprovalRequested::class => [
            \Modules\CostCenterManagement\Listeners\SendBudgetRevisionApprovalRequestedNotification::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
