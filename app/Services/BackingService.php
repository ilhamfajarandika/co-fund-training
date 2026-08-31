<?php

namespace App\Services;

use App\Jobs\SendDonationNotificationJob;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BackingService
{
    /**
     * Proses donasi untuk sebuah campaign.
     *
     * @throws RuntimeException Jika campaign tidak valid untuk menerima donasi (404/400).
     * @throws Exception Jika terjadi kesalahan saat proses transaksi (500).
     */
    public function store(string $campaignId, User $user, array $data): Backing
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            throw new RuntimeException('Campaign tidak ditemukan.', 404);
        }

        if ($campaign->status !== 'active') {
            throw new RuntimeException('Campaign belum dapat menerima donasi.', 400);
        }

        if ($campaign->deadline->isPast()) {
            throw new RuntimeException('Campaign telah berakhir.', 400);
        }

        DB::beginTransaction();

        try {
            $backing = Backing::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'status' => 'pending',
            ]);

            $campaign->increment('current_amount', $data['amount']);

            if ($campaign->current_amount >= $campaign->target_amount) {
                $campaign->update([
                    'status' => 'success',
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        SendDonationNotificationJob::dispatch($backing);

        return $backing;
    }
}
