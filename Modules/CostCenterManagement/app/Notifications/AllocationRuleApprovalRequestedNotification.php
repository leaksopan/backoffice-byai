<?php

namespace Modules\CostCenterManagement\Notifications;

use Modules\CostCenterManagement\Models\AllocationRule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AllocationRuleApprovalRequestedNotification extends Notification
{
    use Queueable;

    public AllocationRule $allocationRule;
    public int $requestedBy;

    public function __construct(AllocationRule $allocationRule, int $requestedBy)
    {
        $this->allocationRule = $allocationRule;
        $this->requestedBy = $requestedBy;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $requester = \App\Models\User::find($this->requestedBy);
        $sourceCostCenter = $this->allocationRule->sourceCostCenter;

        return (new MailMessage)
            ->subject('Approval Request: Allocation Rule')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Anda menerima permintaan approval untuk Allocation Rule baru.')
            ->line('**Detail Allocation Rule:**')
            ->line('- Code: ' . $this->allocationRule->code)
            ->line('- Name: ' . $this->allocationRule->name)
            ->line('- Source Cost Center: ' . $sourceCostCenter->code . ' - ' . $sourceCostCenter->name)
            ->line('- Allocation Base: ' . ucfirst(str_replace('_', ' ', $this->allocationRule->allocation_base)))
            ->line('- Effective Date: ' . $this->allocationRule->effective_date->format('d/m/Y'))
            ->line('- Requested By: ' . ($requester ? $requester->name : 'Unknown'))
            ->line('**Justification:**')
            ->line($this->allocationRule->justification ?? 'Tidak ada justifikasi.')
            ->action('Review & Approve', url('/m/cost-center-management/allocation-rules/' . $this->allocationRule->id . '/approve'))
            ->line('Mohon segera review dan berikan approval.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'allocation_rule_id' => $this->allocationRule->id,
            'code' => $this->allocationRule->code,
            'name' => $this->allocationRule->name,
            'source_cost_center_id' => $this->allocationRule->source_cost_center_id,
            'allocation_base' => $this->allocationRule->allocation_base,
            'effective_date' => $this->allocationRule->effective_date->toDateString(),
            'requested_by' => $this->requestedBy,
            'justification' => $this->allocationRule->justification,
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage($this->toArray($notifiable));
    }
}
