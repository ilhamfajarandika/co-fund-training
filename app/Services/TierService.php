<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TierService
{
    /**
     * Create tier for a campaign.
     *
     * @throws RuntimeException
     */
    public function create(Campaign $campaign, User $user, array $data): CampaignTier
    {
        if ($campaign->user_id !== $user->id && $user->role !== 'admin') {
            throw new RuntimeException('Tidak memiliki izin untuk membuat tier.', 403);
        }

        if ($campaign->status !== 'draft' && $campaign->status !== 'review') {
            throw new RuntimeException('Tier hanya bisa dibuat saat campaign draft atau review.', 400);
        }

        $quota = (int) ($data['quota'] ?? 0);
        $remainingQuota = $quota > 0 ? $quota : 0;

        $tier = $campaign->tiers()->create([
            'name' => $data['name'],
            'min_amount' => $data['min_amount'],
            'quota' => $quota,
            'remaining_quota' => $remainingQuota,
            'reward_description' => $data['reward_description'],
        ]);

        return $tier;
    }

    /**
     * Update tier.
     *
     * @throws RuntimeException
     */
    public function update(CampaignTier $tier, User $user, array $data): CampaignTier
    {
        $campaign = $tier->campaign;

        if ($campaign->user_id !== $user->id && $user->role !== 'admin') {
            throw new RuntimeException('Tidak memiliki izin untuk memperbarui tier.', 403);
        }

        if ($campaign->status !== 'draft' && $campaign->status !== 'review') {
            throw new RuntimeException('Tier hanya bisa diperbarui saat campaign draft atau review.', 400);
        }

        if (isset($data['quota'])) {
            $quota = (int) $data['quota'];
            if ($quota > 0 && $tier->remaining_quota > $quota) {
                throw new RuntimeException('Remaining quota tidak boleh melebihi quota baru.', 400);
            }
            $data['remaining_quota'] = $quota > 0 ? $quota : 0;
        }

        $tier->update($data);

        return $tier->fresh();
    }

    /**
     * Delete tier.
     *
     * @throws RuntimeException
     */
    public function delete(CampaignTier $tier, User $user): void
    {
        $campaign = $tier->campaign;

        if ($campaign->user_id !== $user->id && $user->role !== 'admin') {
            throw new RuntimeException('Tidak memiliki izin untuk menghapus tier.', 403);
        }

        if ($campaign->status !== 'draft' && $campaign->status !== 'review') {
            throw new RuntimeException('Tier hanya bisa dihapus saat campaign draft atau review.', 400);
        }

        $tier->delete();
    }

    /**
     * Get all tiers for a campaign.
     */
    public function getByCampaign(Campaign $campaign)
    {
        return $campaign->tiers()->get();
    }
}
