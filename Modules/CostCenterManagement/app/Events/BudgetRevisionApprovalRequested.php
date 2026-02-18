<?php

namespace Modules\CostCenterManagement\Events;

use Modules\CostCenterManagement\Models\CostCenterBudget;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetRevisionApprovalRequested
{
    use Dispatchable, SerializesModels;

    public CostCenterBudget $budget;
    public int $requestedBy;
    public string $justification;

    public function __construct(CostCenterBudget $budget, int $requestedBy, string $justification)
    {
        $this->budget = $budget;
        $this->requestedBy = $requestedBy;
        $this->justification = $justification;
    }
}
