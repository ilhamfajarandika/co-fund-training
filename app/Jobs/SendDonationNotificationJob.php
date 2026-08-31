<?php

namespace App\Jobs;

use App\Models\Backing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\DonationReceivedMail;

class SendDonationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Backing $backing)
    {
    }

    public function handle(): void
    {
        $campaign = $this->backing->campaign->load('user');

        if ($campaign && $campaign->user) {
            \Mail::to(new Address($campaign->user->email, $campaign->user->name))
                ->send(new DonationReceivedMail($this->backing));
        }
    }
}
