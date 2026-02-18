<?php

namespace Modules\CostCenterManagement\Events;

use Modules\CostCenterManagement\Models\AllocationRule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AllocationRuleApprovalRequested
{
    use Dispatchable, SerializesModels;

    public AllocationRule $allocationRule;
    public int $requestedBy;

    public function __construct(AllocationRule $allocationRule, int $requestedBy)
    {
        $this->allocationRule = $allocationRule;
        $this->requestedBy = $requestedBy;
    }
}
