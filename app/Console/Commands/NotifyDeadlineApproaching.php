<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Notifications\DeadlineReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NotifyDeadlineApproaching extends Command
{
    protected $signature = 'campaign:notify-deadline';
    protected $description = 'Send notifications to backers for campaigns approaching their deadline (H-3 and H-1)';

    public function handle()
    {
        $this->info('Checking for campaigns approaching deadline...');

        $now = now();
        $targetDates = [
            3 => $now->copy()->addDays(3)->toDateString(),
            1 => $now->copy()->addDay()->toDateString(),
        ];

        foreach ($targetDates as $daysRemaining => $date) {
            $campaigns = Campaign::where('status', 'active')
                ->whereDate('deadline', $date)
                ->get();

            $this->info("Found {$campaigns->count()} campaigns ending in {$daysRemaining} days.");

            foreach ($campaigns as $campaign) {
                $cacheKey = "campaign_{$campaign->id}_deadline_notice_{$daysRemaining}";

                if (Cache::has($cacheKey)) {
                    $this->line("Skipping campaign {$campaign->id}, notification for {$daysRemaining} days already sent.");
                    continue;
                }

                $backings = $campaign->backings()->where('status', 'completed')->with('user')->get();
                $uniqueBackers = $backings->pluck('user')->unique('id');

                foreach ($uniqueBackers as $backer) {
                    if ($backer) {
                        $backer->notify(new DeadlineReminderNotification($campaign, $daysRemaining));
                    }
                }

                Cache::put($cacheKey, true, now()->addHours(24));
                $this->info("Sent notifications for campaign {$campaign->id} ({$daysRemaining} days remaining).");
            }
        }

        $this->info('Finished sending deadline notifications.');
    }
}
