<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignRefundNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign,
        public float $amount
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'campaign_refund',
            'title' => 'Refund Donasi',
            'body' => "Campaign '{$this->campaign->title}' gagal mencapai target. Donasi Anda sebesar Rp " . number_format($this->amount, 0, ',', '.') . " telah dikembalikan.",
            'data' => [
                'campaign_id' => $this->campaign->id,
                'campaign_title' => $this->campaign->title,
                'amount' => $this->amount,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Refund Campaign - ' . $this->campaign->title)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Campaign '{$this->campaign->title}' tidak berhasil mencapai targetnya.")
            ->line('Donasi Anda sebesar Rp ' . number_format($this->amount, 0, ',', '.') . ' telah dikembalikan.')
            ->line('**Jumlah Refund:** Rp ' . number_format($this->amount, 0, ',', '.'))
            ->action('Lihat Campaign', url('/api/v1/campaigns/' . $this->campaign->id))
            ->line('Thanks,')
            ->line(config('app.name'));
    }
}
