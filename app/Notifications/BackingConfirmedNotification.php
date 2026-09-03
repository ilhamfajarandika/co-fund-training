<?php

namespace App\Notifications;

use App\Models\Backing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BackingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Backing $backing,
        public bool $forBacker = true
    ) {}

    public function via($notifiable): array
    {
        return $this->forBacker
            ? ['database', 'mail']
            : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        if ($this->forBacker) {
            return [
                'type' => 'backing_confirmed',
                'title' => 'Donasi Berhasil Diproses',
                'body' => 'Donasi Anda sebesar Rp ' . number_format($this->backing->amount, 0, ',', '.') . ' untuk campaign "' . $this->backing->campaign->title . '" berhasil diproses.',
                'data' => [
                    'backing_id' => $this->backing->id,
                    'campaign_id' => $this->backing->campaign_id,
                    'campaign_title' => $this->backing->campaign->title,
                    'amount' => $this->backing->amount,
                ],
            ];
        }

        return [
            'type' => 'new_backing',
            'title' => 'Donasi Baru Diterima',
            'body' => 'Ada donasi baru sebesar Rp ' . number_format($this->backing->amount, 0, ',', '.') . ' dari ' . $this->backing->user->name . ' untuk campaign "' . $this->backing->campaign->title . '".',
            'data' => [
                'backing_id' => $this->backing->id,
                'campaign_id' => $this->backing->campaign_id,
                'campaign_title' => $this->backing->campaign->title,
                'backer_name' => $this->backing->user->name,
                'amount' => $this->backing->amount,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format($this->backing->amount, 0, ',', '.');
        $campaignTitle = $this->backing->campaign->title;

        return (new MailMessage)
            ->subject('Konfirmasi Donasi - ' . $campaignTitle)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Donasi Anda sebesar Rp ' . $amount . ' untuk campaign "' . $campaignTitle . '" berhasil diproses.')
            ->line('**Jumlah Donasi:** Rp ' . $amount)
            ->line('**Campaign:** ' . $campaignTitle)
            ->action('Lihat Campaign', url('/api/v1/campaigns/' . $this->backing->campaign_id))
            ->line('Thanks,')
            ->line(config('app.name'));
    }
}
