<?php

namespace App\Console\Commands;

use App\Jobs\DisburseCampaignJob;
use App\Jobs\RefundBackersJob;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessCampaignEndingsCommand extends Command
{
    protected $signature = 'campaigns:process-endings';
    protected $description = 'Process campaigns that have ended: disburse success campaigns and refund failed campaigns';

    public function handle(): void
    {
        $this->info('Processing campaign endings...');

        $now = now();

        // 1. Find campaigns that have ended and are successful
        $successCampaigns = Campaign::where('status', 'success')
            ->where('deadline', '<', $now)
            ->get();

        $this->info("Found {$successCampaigns->count()} successful campaigns to disburse.");

        foreach ($successCampaigns as $campaign) {
            $hasDisbursement = DB::table('transactions')
                ->where('campaign_id', $campaign->id)
                ->where('type', 'disbursement')
                ->exists();

            if (!$hasDisbursement) {
                DisburseCampaignJob::dispatch($campaign);
                $this->info("Disbursing campaign: {$campaign->title} (ID: {$campaign->id})");
            }
        }

        // 2. Find campaigns that have ended but are not successful
        $failedCampaigns = Campaign::whereNotIn('status', ['success', 'failed'])
            ->where('deadline', '<', $now)
            ->get();

        $this->info("Found {$failedCampaigns->count()} failed campaigns to refund.");

        foreach ($failedCampaigns as $campaign) {
            $hasRefund = DB::table('transactions')
                ->where('campaign_id', $campaign->id)
                ->where('type', 'refund')
                ->exists();

            if (!$hasRefund) {
                $campaign->update(['status' => 'failed']);

                RefundBackersJob::dispatch($campaign);
                $this->info("Refunding campaign: {$campaign->title} (ID: {$campaign->id})");
            }
        }

        $this->info('Campaign endings processed successfully.');
    }
}
