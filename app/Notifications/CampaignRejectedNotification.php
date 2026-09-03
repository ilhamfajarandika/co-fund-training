<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CampaignRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign,
        public ?string $rejectionNote = null
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'campaign_rejected',
            'title' => 'Campaign Ditolak',
            'body' => "Campaign '{$this->campaign->title}' Anda ditolak. " . ($this->rejectionNote ? 'Catatan: ' . $this->rejectionNote : ''),
            'data' => [
                'campaign_id' => $this->campaign->id,
                'campaign_title' => $this->campaign->title,
                'rejection_note' => $this->rejectionNote,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Campaign Kamu Perlu Diperbaiki - ' . $this->campaign->title)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Campaign kamu ditolak dan perlu diperbaiki.')
            ->line('**Judul:** ' . $this->campaign->title);

        if ($this->rejectionNote) {
            $message->line('**Catatan Penolakan:** ' . $this->rejectionNote);
        }

        $message->action('Lihat Campaign', url('/api/v1/campaigns/' . $this->campaign->id))
            ->line('Thanks,')
            ->line(config('app.name'));

        return $message;
    }
}
