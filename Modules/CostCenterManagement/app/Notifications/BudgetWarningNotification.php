<?php

namespace Modules\CostCenterManagement\Notifications;

use Modules\CostCenterManagement\Models\CostCenterBudget;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class BudgetWarningNotification extends Notification
{
    use Queueable;

    public CostCenterBudget $budget;

    public function __construct(CostCenterBudget $budget)
    {
        $this->budget = $budget;
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
        $costCenter = $this->budget->costCenter;
        $utilizationPercentage = $this->budget->utilization_percentage;

        return (new MailMessage)
            ->subject('Peringatan: Budget Utilization Melebihi Threshold')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Budget utilization untuk Cost Center **' . $costCenter->name . '** telah melebihi threshold.')
            ->line('**Detail Budget:**')
            ->line('- Cost Center: ' . $costCenter->code . ' - ' . $costCenter->name)
            ->line('- Kategori: ' . ucfirst($this->budget->category))
            ->line('- Periode: ' . $this->budget->period_month . '/' . $this->budget->fiscal_year)
            ->line('- Budget Amount: Rp ' . number_format($this->budget->budget_amount, 2, ',', '.'))
            ->line('- Actual Amount: Rp ' . number_format($this->budget->actual_amount, 2, ',', '.'))
            ->line('- Utilization: ' . number_format($utilizationPercentage, 2) . '%')
            ->line('- Variance: Rp ' . number_format($this->budget->variance_amount, 2, ',', '.'))
            ->action('Lihat Detail Budget', url('/m/cost-center-management/budgets/' . $this->budget->id))
            ->line('Mohon segera lakukan review dan tindakan yang diperlukan.');
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
            'actual_amount' => $this->budget->actual_amount,
            'utilization_percentage' => $this->budget->utilization_percentage,
            'variance_amount' => $this->budget->variance_amount,
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
