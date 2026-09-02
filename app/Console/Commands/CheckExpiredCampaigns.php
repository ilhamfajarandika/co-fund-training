<?php

namespace App\Console\Commands;

use App\Jobs\DisburseCampaignJob;
use App\Jobs\RefundBackersJob;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckExpiredCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check expired active campaigns and dispatch disbursement or refund jobs accordingly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking expired campaigns...');

        $now = now();

        $expiredCampaigns = Campaign::where('status', 'active')
            ->where('deadline', '<', $now)
            ->get();

        $this->info("Found {$expiredCampaigns->count()} expired campaigns.");

        foreach ($expiredCampaigns as $campaign) {
            DB::transaction(function () use ($campaign) {
                // Refresh data to prevent race conditions
                $campaign->refresh();

                if ($campaign->status !== 'active') {
                    return;
                }

                if ($campaign->collected_amount >= $campaign->target_amount) {
                    $campaign->update(['status' => 'success']);
                    DisburseCampaignJob::dispatch($campaign);
                    $this->info("Campaign {$campaign->id} marked as success. Disbursing funds.");
                } else {
                    $campaign->update(['status' => 'failed']);
                    RefundBackersJob::dispatch($campaign);
                    $this->info("Campaign {$campaign->id} marked as failed. Refunding backers.");
                }
            });
        }

        $this->info('Finished checking expired campaigns.');
    }
}
