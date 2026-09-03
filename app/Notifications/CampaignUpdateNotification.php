<?php

namespace App\Notifications;

use App\Models\CampaignUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CampaignUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CampaignUpdate $update) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'campaign_update',
            'title' => 'Update Campaign Baru',
            'body' => "Campaign '{$this->update->campaign->title}' memiliki update baru: '{$this->update->title}'.",
            'data' => [
                'campaign_id' => $this->update->campaign_id,
                'campaign_title' => $this->update->campaign->title,
                'update_id' => $this->update->id,
                'update_title' => $this->update->title,
            ],
        ];
    }
}
