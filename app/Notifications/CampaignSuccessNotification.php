<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign,
        public float $platformFee,
        public float $creatorReceive
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'campaign_success',
            'title' => 'Campaign Berhasil',
            'body' => "Campaign '{$this->campaign->title}' berhasil mencapai target donasinya! Dana akan segera dicairkan.",
            'data' => [
                'campaign_id' => $this->campaign->id,
                'campaign_title' => $this->campaign->title,
                'collected_amount' => $this->campaign->collected_amount,
                'target_amount' => $this->campaign->target_amount,
                'platform_fee' => $this->platformFee,
                'creator_receive' => $this->creatorReceive,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Dana Campaign Kamu Sudah Dicairkan - ' . $this->campaign->title)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line("Campaign '{$this->campaign->title}' berhasil mencapai target donasinya!")
            ->line('**Total Terkumpul:** Rp ' . number_format($this->campaign->collected_amount, 0, ',', '.'))
            ->line('**Biaya Platform:** Rp ' . number_format($this->platformFee, 0, ',', '.'))
            ->line('**Yang Diterima:** Rp ' . number_format($this->creatorReceive, 0, ',', '.'))
            ->action('Lihat Campaign', url('/api/v1/campaigns/' . $this->campaign->id))
            ->line('Thanks,')
            ->line(config('app.name'));
    }
}
