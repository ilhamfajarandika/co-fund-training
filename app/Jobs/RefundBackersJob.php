<?php

namespace App\Jobs;

use App\Mail\CampaignRefundedMail;
use App\Models\Backing;
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

class RefundBackersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Campaign $campaign)
    {
    }

    public function handle(): void
    {
        $campaign = $this->campaign->fresh();

        if (!$campaign) {
            return;
        }

        $backings = $campaign->backings()
            ->where('status', 'completed')
            ->get();

        if ($backings->isEmpty()) {
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($backings as $backing) {
                $backer = $backing->user;

                if (!$backer) {
                    continue;
                }

                // Idempotency check: prevent double refund
                $hasRefund = DB::table('transactions')
                    ->where('backing_id', $backing->id)
                    ->where('type', 'refund')
                    ->exists();

                if ($hasRefund) {
                    continue;
                }

                $backer->increment('balance', $backing->amount);

                Transaction::create([
                    'user_id' => $backer->id,
                    'backing_id' => $backing->id,
                    'campaign_id' => $campaign->id,
                    'type' => 'refund',
                    'amount' => $backing->amount,
                    'status' => 'success',
                    'reference' => 'REF-' . strtoupper(uniqid()),
                ]);

                $backing->update([
                    'status' => 'refunded',
                ]);

                Mail::to(new Address($backer->email, $backer->name))
                    ->send(new CampaignRefundedMail($campaign->fresh(), $backing->amount));
            }

            $campaign->update([
                'collected_amount' => 0,
                'current_amount' => 0,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
