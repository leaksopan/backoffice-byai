<?php

namespace Modules\CostCenterManagement\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AllocationCompletedNotification extends Notification
{
    use Queueable;

    public string $batchId;
    public int $totalJournals;
    public float $totalAmount;
    public array $summary;

    public function __construct(string $batchId, int $totalJournals, float $totalAmount, array $summary = [])
    {
        $this->batchId = $batchId;
        $this->totalJournals = $totalJournals;
        $this->totalAmount = $totalAmount;
        $this->summary = $summary;
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
        $message = (new MailMessage)
            ->subject('Proses Alokasi Biaya Selesai')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Proses alokasi biaya telah selesai dijalankan.')
            ->line('**Detail Alokasi:**')
            ->line('- Batch ID: ' . $this->batchId)
            ->line('- Total Journals: ' . number_format($this->totalJournals))
            ->line('- Total Amount: Rp ' . number_format($this->totalAmount, 2, ',', '.'));

        if (!empty($this->summary)) {
            $message->line('**Summary:**');
            foreach ($this->summary as $key => $value) {
                $message->line('- ' . ucfirst(str_replace('_', ' ', $key)) . ': ' . $value);
            }
        }

        return $message
            ->action('Lihat Detail Alokasi', url('/m/cost-center-management/allocations/batches/' . $this->batchId))
            ->line('Silakan review hasil alokasi sebelum posting ke GL.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'batch_id' => $this->batchId,
            'total_journals' => $this->totalJournals,
            'total_amount' => $this->totalAmount,
            'summary' => $this->summary,
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
