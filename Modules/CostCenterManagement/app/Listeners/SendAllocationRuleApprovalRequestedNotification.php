<?php

namespace Modules\CostCenterManagement\Listeners;

use Modules\CostCenterManagement\Events\AllocationRuleApprovalRequested;
use Modules\CostCenterManagement\Notifications\AllocationRuleApprovalRequestedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SendAllocationRuleApprovalRequestedNotification
{
    /**
     * Handle the event.
     */
    public function handle(AllocationRuleApprovalRequested $event): void
    {
        // Kirim notifikasi ke users dengan permission cost-center-management.approve
        $approvers = User::permission('cost-center-management.approve')->get();
        
        Notification::send(
            $approvers,
            new AllocationRuleApprovalRequestedNotification(
                $event->allocationRule,
                $event->requestedBy
            )
        );
    }
}
