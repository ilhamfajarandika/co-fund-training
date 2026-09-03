<?php

namespace App\Services;

use App\Models\Backing;
use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;
use App\Notifications\BackingConfirmedNotification;
use App\Notifications\CampaignApprovedNotification;
use App\Notifications\CampaignRefundNotification;
use App\Notifications\CampaignRejectedNotification;
use App\Notifications\CampaignSuccessNotification;
use App\Notifications\CampaignUpdateNotification;
use App\Notifications\DeadlineReminderNotification;

class NotificationService
{
    public function sendCampaignApproved(Campaign $campaign): void
    {
        $campaign->user->notify(new CampaignApprovedNotification($campaign));
    }

    public function sendCampaignRejected(Campaign $campaign, ?string $rejectionNote = null): void
    {
        $campaign->user->notify(new CampaignRejectedNotification($campaign, $rejectionNote));
    }

    public function sendBackingConfirmed(Backing $backing): void
    {
        $backing->load('user', 'campaign.user');

        $backing->user->notify(new BackingConfirmedNotification($backing, true));

        $backing->campaign->user->notify(new BackingConfirmedNotification($backing, false));
    }

    public function sendCampaignUpdate(CampaignUpdate $update): void
    {
        $updates = $update->campaign->backings()
            ->where('status', '!=', 'refunded')
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id');

        foreach ($updates as $backer) {
            if ($backer) {
                $backer->notify(new CampaignUpdateNotification($update));
            }
        }
    }

    public function sendDeadlineReminder(Campaign $campaign, int $daysRemaining): void
    {
        $backers = $campaign->backings()
            ->where('status', 'completed')
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id');

        foreach ($backers as $backer) {
            if ($backer) {
                $backer->notify(new DeadlineReminderNotification($campaign, $daysRemaining));
            }
        }
    }

    public function sendCampaignSuccess(Campaign $campaign, float $platformFee, float $creatorReceive): void
    {
        $campaign->user->notify(new CampaignSuccessNotification($campaign, $platformFee, $creatorReceive));
    }

    public function sendCampaignRefund(Campaign $campaign, float $amount): void
    {
        $backers = $campaign->backings()
            ->where('status', 'completed')
            ->with('user')
            ->get()
            ->pluck('user')
            ->unique('id');

        foreach ($backers as $backer) {
            if ($backer) {
                $backer->notify(new CampaignRefundNotification($campaign, $amount));
            }
        }
    }

    public function getNotifications(User $user, int $perPage = 15)
    {
        return $user->notifications()->latest()->paginate($perPage);
    }

    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, string $id): bool
    {
        $notification = $user->notifications()->where('id', $id)->first();

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
