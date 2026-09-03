<?php

namespace App\Notifications;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Campaign $campaign,
        public int $daysRemaining
    ) {}

    public function via($notifiable): array
    {
        return $this->daysRemaining === 1
            ? ['database', 'mail']
            : ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'deadline_reminder',
            'title' => 'Deadline Campaign Perlu Diingat',
            'body' => "Campaign '{$this->campaign->title}' akan berakhir dalam {$this->daysRemaining} hari. Segera donasikan sebelum terlalu pulang!",
            'data' => [
                'campaign_id' => $this->campaign->id,
                'campaign_title' => $this->campaign->title,
                'days_remaining' => $this->daysRemaining,
            ],
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Campaign Deadline Approaching: {$this->campaign->title}")
            ->greeting('Hello,')
            ->line("The campaign you backed, **{$this->campaign->title}**, is ending in **{$this->daysRemaining} days**!")
            ->line('**Target Amount:** Rp ' . number_format($this->campaign->target_amount, 0, ',', '.'))
            ->line('**Current Amount:** Rp ' . number_format($this->campaign->collected_amount, 0, ',', '.'))
            ->line('Don\'t forget to share the campaign with your friends to help it reach its goal.')
            ->action('Lihat Campaign', url('/api/v1/campaigns/' . $this->campaign->id))
            ->line('Thank you,')
            ->line(config('app.name'));
    }
}
