<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Campaign $campaign) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'campaign_approved',
            'title' => 'Campaign Disetujui',
            'body' => "Campaign '{$this->campaign->title}' Anda telah disetujui dan sekarang sedang aktif.",
            'data' => [
                'campaign_id' => $this->campaign->id,
                'campaign_title' => $this->campaign->title,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Campaign Kamu Sudah Disetujui - ' . $this->campaign->title)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Bagus! Campaign kamu sudah disetujui dan kini aktif.')
            ->line('**Judul:** ' . $this->campaign->title)
            ->line('**Target Dana:** Rp ' . number_format($this->campaign->target_amount, 0, ',', '.'))
            ->line('**Deadline:** ' . $this->campaign->deadline->format('d M Y'))
            ->action('Lihat Campaign', url('/api/v1/campaigns/' . $this->campaign->id))
            ->line('Thanks,')
            ->line(config('app.name'));
    }
}
