<?php

namespace Modules\CostCenterManagement\Notifications;

use Modules\CostCenterManagement\Models\CostCenterBudget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class BudgetRevisionApprovalRequestedNotification extends Notification
{
    use Queueable;

    public CostCenterBudget $budget;
    public int $requestedBy;
    public string $justification;

    public function __construct(CostCenterBudget $budget, int $requestedBy, string $justification)
    {
        $this->budget = $budget;
        $this->requestedBy = $requestedBy;
        $this->justification = $justification;
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
        $costCenter = $this->budget->costCenter;

        return (new MailMessage)
            ->subject('Approval Request: Budget Revision')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Anda menerima permintaan approval untuk revisi budget.')
            ->line('**Detail Budget:**')
            ->line('- Cost Center: ' . $costCenter->code . ' - ' . $costCenter->name)
            ->line('- Kategori: ' . ucfirst($this->budget->category))
            ->line('- Periode: ' . $this->budget->period_month . '/' . $this->budget->fiscal_year)
            ->line('- Budget Amount: Rp ' . number_format($this->budget->budget_amount, 2, ',', '.'))
            ->line('- Revision Number: ' . $this->budget->revision_number)
            ->line('- Requested By: ' . ($requester ? $requester->name : 'Unknown'))
            ->line('**Justification:**')
            ->line($this->justification)
            ->action('Review & Approve', url('/m/cost-center-management/budgets/' . $this->budget->id . '/approve'))
            ->line('Mohon segera review dan berikan approval.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'budget_id' => $this->budget->id,
            'cost_center_id' => $this->budget->cost_center_id,
            'cost_center_name' => $this->budget->costCenter->name,
            'category' => $this->budget->category,
            'fiscal_year' => $this->budget->fiscal_year,
            'period_month' => $this->budget->period_month,
            'budget_amount' => $this->budget->budget_amount,
            'revision_number' => $this->budget->revision_number,
            'requested_by' => $this->requestedBy,
            'justification' => $this->justification,
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
