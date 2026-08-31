<?php

namespace App\Jobs;

use App\Models\CampaignUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\CampaignUpdateMail;

class SendCampaignUpdateNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public CampaignUpdate $update)
    {
    }

    public function handle(): void
    {
        $campaign = $this->update->campaign->load('backings.user');

        if (!$campaign || !$campaign->backings) {
            return;
        }

        $backers = $campaign->backings
            ->where('status', '!=', 'refunded')
            ->pluck('user')
            ->unique('id');

        foreach ($backers as $backer) {
            if ($backer && $backer->email) {
                \Mail::to(new Address($backer->email, $backer->name))
                    ->send(new CampaignUpdateMail($this->update));
            }
        }
    }
}
