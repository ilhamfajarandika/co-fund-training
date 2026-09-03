<?php

namespace App\Jobs;

use App\Models\Backing;
use App\Notifications\BackingConfirmedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDonationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Backing $backing)
    {
    }

    public function handle(): void
    {
        $backing = $this->backing->load('user', 'campaign.user');

        $backing->user->notify(new BackingConfirmedNotification($backing, true));

        $backing->campaign->user->notify(new BackingConfirmedNotification($backing, false));
    }
}
