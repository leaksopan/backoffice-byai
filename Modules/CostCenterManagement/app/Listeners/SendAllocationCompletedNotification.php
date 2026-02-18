<?php

namespace Modules\CostCenterManagement\Listeners;

use Modules\CostCenterManagement\Events\AllocationCompleted;
use Modules\CostCenterManagement\Notifications\AllocationCompletedNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SendAllocationCompletedNotification
{
    /**
     * Handle the event.
     */
    public function handle(AllocationCompleted $event): void
    {
        // Kirim notifikasi ke users dengan permission cost-center-management.allocate
        $allocators = User::permission('cost-center-management.allocate')->get();
        
        Notification::send(
            $allocators,
            new AllocationCompletedNotification(
                $event->batchId,
                $event->totalJournals,
                $event->totalAmount,
                $event->summary
            )
        );

        // Kirim notifikasi ke users dengan permission cost-center-management.approve
        $approvers = User::permission('cost-center-management.approve')->get();
        
        Notification::send(
            $approvers,
            new AllocationCompletedNotification(
                $event->batchId,
                $event->totalJournals,
                $event->totalAmount,
                $event->summary
            )
        );
    }
}
