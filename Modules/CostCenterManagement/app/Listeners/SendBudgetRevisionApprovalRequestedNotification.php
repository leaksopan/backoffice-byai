<?php

namespace Modules\CostCenterManagement\Listeners;

use Modules\CostCenterManagement\Events\BudgetRevisionApprovalRequested;
use Modules\CostCenterManagement\Notifications\BudgetRevisionApprovalRequestedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SendBudgetRevisionApprovalRequestedNotification
{
    /**
     * Handle the event.
     */
    public function handle(BudgetRevisionApprovalRequested $event): void
    {
        // Kirim notifikasi ke users dengan permission cost-center-management.approve
        $approvers = User::permission('cost-center-management.approve')->get();
        
        Notification::send(
            $approvers,
            new BudgetRevisionApprovalRequestedNotification(
                $event->budget,
                $event->requestedBy,
                $event->justification
            )
        );
    }
}
