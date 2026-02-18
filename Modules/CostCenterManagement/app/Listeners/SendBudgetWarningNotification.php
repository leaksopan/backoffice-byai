<?php

namespace Modules\CostCenterManagement\Listeners;

use Modules\CostCenterManagement\Events\BudgetThresholdExceeded;
use Modules\CostCenterManagement\Notifications\BudgetWarningNotification;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class SendBudgetWarningNotification
{
    /**
     * Handle the event.
     */
    public function handle(BudgetThresholdExceeded $event): void
    {
        $budget = $event->budget;
        $costCenter = $budget->costCenter;

        // Kirim notifikasi ke cost center manager
        if ($costCenter->manager_user_id) {
            $manager = User::find($costCenter->manager_user_id);
            if ($manager) {
                $manager->notify(new BudgetWarningNotification($budget));
            }
        }

        // Kirim notifikasi ke users dengan permission cost-center-management.approve
        $approvers = User::permission('cost-center-management.approve')->get();
        Notification::send($approvers, new BudgetWarningNotification($budget));

        // Kirim notifikasi ke users dengan permission cost-center-management.view yang terkait dengan cost center
        // (optional: bisa ditambahkan logic untuk filter users berdasarkan cost center assignment)
    }
}
