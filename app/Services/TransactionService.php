<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a new transaction.
     */
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    /**
     * Get transaction history for a user.
     */
    public function getUserTransactions(User $user)
    {
        return Transaction::where('user_id', $user->id)
            ->latest()
            ->paginate(10);
    }

    /**
     * Get transaction history for a campaign.
     */
    public function getCampaignTransactions($campaignId)
    {
        return Transaction::where('campaign_id', $campaignId)
            ->latest()
            ->paginate(10);
    }
}
