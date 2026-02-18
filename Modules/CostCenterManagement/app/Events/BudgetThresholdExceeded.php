<?php

namespace Modules\CostCenterManagement\Events;

use Modules\CostCenterManagement\Models\CostCenterBudget;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetThresholdExceeded
{
    use Dispatchable, SerializesModels;

    public CostCenterBudget $budget;

    public function __construct(CostCenterBudget $budget)
    {
        $this->budget = $budget;
    }
}
