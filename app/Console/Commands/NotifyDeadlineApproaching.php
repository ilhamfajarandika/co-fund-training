<?php

namespace App\Console\Commands;

use App\Mail\CampaignDeadlineApproachingMail;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class NotifyDeadlineApproaching extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:notify-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to backers for campaigns approaching their deadline (H-3 and H-1)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for campaigns approaching deadline...');

        $now = now();
        $targetDates = [
            1 => $now->copy()->addDay()->toDateString(),
            3 => $now->copy()->addDays(3)->toDateString(),
        ];

        foreach ($targetDates as $daysRemaining => $date) {
            $campaigns = Campaign::where('status', 'active')
                ->whereDate('deadline', $date)
                ->get();

            $this->info("Found {$campaigns->count()} campaigns ending in {$daysRemaining} days.");

            foreach ($campaigns as $campaign) {
                // Prevent duplicate notifications using Cache
                $cacheKey = "campaign_{$campaign->id}_deadline_notice_{$daysRemaining}";

                if (Cache::has($cacheKey)) {
                    $this->line("Skipping campaign {$campaign->id}, notification for {$daysRemaining} days already sent.");
                    continue;
                }

                $backings = $campaign->backings()->where('status', 'completed')->with('user')->get();
                $uniqueBackers = $backings->pluck('user')->unique('id');

                foreach ($uniqueBackers as $backer) {
                    if ($backer) {
                        Mail::to(new Address($backer->email, $backer->name))
                            ->send(new CampaignDeadlineApproachingMail($campaign, $daysRemaining));
                    }
                }

                // Cache for 24 hours to prevent re-sending today if command is re-run
                Cache::put($cacheKey, true, now()->addHours(24));
                $this->info("Sent notifications for campaign {$campaign->id} ({$daysRemaining} days remaining).");
            }
        }

        $this->info('Finished sending deadline notifications.');
    }
}
