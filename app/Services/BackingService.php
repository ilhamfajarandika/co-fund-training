<?php

namespace App\Services;

use App\Jobs\SendDonationNotificationJob;
use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\Transaction;
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

        if ($user->role !== 'admin' && !$user->hasVerifiedEmail()) {
            throw new RuntimeException('Email belum diverifikasi.', 400);
        }

        if ($campaign->user_id === $user->id) {
            throw new RuntimeException('Creator tidak dapat mendonasi campaign sendiri.', 400);
        }

        $tier = null;
        if (!empty($data['tier_id'])) {
            $tier = CampaignTier::where('id', $data['tier_id'])
                ->where('campaign_id', $campaign->id)
                ->first();

            if (!$tier) {
                throw new RuntimeException('Tier tidak ditemukan.', 404);
            }

            if ($tier->quota > 0 && $tier->remaining_quota <= 0) {
                throw new RuntimeException('Tier ini sudah penuh.', 400);
            }

            if ($data['amount'] < $tier->min_amount) {
                throw new RuntimeException("Minimal donasi untuk tier {$tier->name} adalah Rp" . number_format($tier->min_amount, 0, ',', '.'), 400);
            }
        }

        DB::beginTransaction();

        try {
            $backing = Backing::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'tier_id' => $data['tier_id'] ?? null,
                'amount' => $data['amount'],
                'status' => 'completed',
            ]);

            $campaign->increment('collected_amount', $data['amount']);

            if ($tier && $tier->quota > 0) {
                $tier->decrement('remaining_quota');
            }

            $campaign->refresh();

            if ($campaign->collected_amount >= $campaign->target_amount) {
                $campaign->update([
                    'status' => 'success',
                ]);
            }

            Transaction::create([
                'user_id' => $user->id,
                'backing_id' => $backing->id,
                'campaign_id' => $campaign->id,
                'type' => 'payment',
                'amount' => $data['amount'],
                'status' => 'success',
                'reference' => 'PAY-' . strtoupper(uniqid()),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        SendDonationNotificationJob::dispatch($backing);

        return $backing->load('tier', 'campaign', 'user');
    }

    /**
     * Get user's backing history.
     */
    public function getMyBackings(User $user)
    {
        return Backing::with('campaign', 'tier')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);
    }
}
