<?php

namespace App\Jobs;

use App\Mail\CampaignDisbursedMail;
use App\Models\Campaign;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DisburseCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Campaign $campaign)
    {
    }

    public function handle(): void
    {
        $campaign = $this->campaign->fresh();

        if (!$campaign || $campaign->collected_amount <= 0) {
            return;
        }

        $creator = $campaign->user;

        if (!$creator) {
            return;
        }

        // Idempotency check: prevent double disbursement
        $hasDisbursement = DB::table('transactions')
            ->where('campaign_id', $campaign->id)
            ->where('type', 'disbursement')
            ->exists();

        if ($hasDisbursement) {
            return;
        }

        DB::beginTransaction();

        try {
            $collectedAmount = $campaign->collected_amount;
            $platformFee = $collectedAmount * 0.05;
            $creatorReceive = $collectedAmount - $platformFee;

            Transaction::create([
                'user_id' => $creator->id,
                'campaign_id' => $campaign->id,
                'type' => 'platform_fee',
                'amount' => $platformFee,
                'status' => 'success',
                'reference' => 'FEE-' . strtoupper(uniqid()),
            ]);

            $creator->increment('balance', $creatorReceive);

            Transaction::create([
                'user_id' => $creator->id,
                'campaign_id' => $campaign->id,
                'type' => 'disbursement',
                'amount' => $creatorReceive,
                'status' => 'success',
                'reference' => 'DISB-' . strtoupper(uniqid()),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        Mail::to(new Address($creator->email, $creator->name))
            ->send(new CampaignDisbursedMail($campaign->fresh(), $platformFee, $creatorReceive));
    }
}
