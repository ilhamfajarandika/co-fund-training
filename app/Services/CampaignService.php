<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\User;
use App\Notifications\CampaignApprovedNotification;
use App\Notifications\CampaignRejectedNotification;
use App\Jobs\SendCampaignUpdateNotificationJob;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignService
{
    public function getAll($user = null, ?string $status = null, ?string $categoryId = null, ?string $sort = null)
    {
        $query = Campaign::with('user');

        if (!$user || $user->role === 'backer') {
            $query->where('status', 'active');
        } elseif ($user->role === 'creator') {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'active')->orWhere('user_id', $user->id);
            });
        }

        if ($status && $user?->role === 'admin') {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($sort === 'popular') {
            $query->orderByDesc('current_amount');
        } else {
            $query->latest();
        }

        return $query->paginate(10);
    }

    public function getById($id, $user = null)
    {
        $query = Campaign::with('user', 'images');

        if ($user) {
            if ($user->role === 'backer') {
                $query->where('status', 'active');
            } elseif ($user->role === 'creator') {
                $query->where(function ($q) use ($user) {
                    $q->where('status', 'active')->orWhere('user_id', $user->id);
                });
            }
        } else {
            $query->where('status', 'active');
        }

        return $query->findOrFail($id);
    }

    public function create(array $data, $user)
    {
        DB::beginTransaction();

        try {

            $slug = Str::slug($data['title']);
            $originalSlug = $slug;
            $count = 1;
            while (Campaign::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $campaign = Campaign::create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'slug' => $slug,
                'description' => $data['description'],
                'target_amount' => $data['target_amount'],
                'deadline' => $data['deadline'],
                'video_url' => $data['video_url'] ?? null,
                'collected_amount' => 0,
                'status' => 'review'
            ]);

            if (isset($data['images'])) {

                foreach ($data['images'] as $index => $image) {

                    $path = $image->store('campaigns', 'public');

                    $campaign->images()->create([
                        'url' => $path,
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            DB::commit();

            return $campaign->load('images');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update campaign.
     */
    public function update(Campaign $campaign, array $data): Campaign
    {
        if ($campaign->status !== 'draft') {
            throw new \RuntimeException('Campaign yang sudah tidak berstatus draft tidak dapat diperbarui.', 400);
        }

        DB::beginTransaction();

        try {
            if (isset($data['slug']) && $data['slug'] !== $campaign->slug) {
                $slug = Str::slug($data['slug']);
                $originalSlug = $slug;
                $count = 1;
                while (Campaign::where('slug', $slug)->where('id', '!=', $campaign->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $data['slug'] = $slug;
            }

            $campaign->update($data);

            DB::commit();

            return $campaign->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    /**
     * Hapus campaign, termasuk validasi izin & status donasi.
     *
     * @throws \RuntimeException
     */
    public function delete(Campaign $campaign, User $user): void
    {
        if ($user->role !== 'admin' && $campaign->user_id !== $user->id) {
            throw new \RuntimeException('Tidak memiliki izin.', 403);
        }

        if ($campaign->backings()->exists()) {
            throw new \RuntimeException(
                'Campaign yang sudah menerima donasi tidak dapat dihapus.',
                400
            );
        }

        DB::beginTransaction();

        try {
            $campaign->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Buat campaign update.
     *
     * @throws \RuntimeException
     */
    public function createUpdate(Campaign $campaign, User $user, array $data): CampaignUpdate
    {
        if ($campaign->status !== 'active') {
            throw new \RuntimeException('Campaign update hanya bisa dibuat saat campaign active.', 400);
        }

        if ($campaign->user_id !== $user->id && $user->role !== 'admin') {
            throw new \RuntimeException('Tidak memiliki izin untuk membuat update.', 403);
        }

        DB::beginTransaction();

        try {
            $update = $campaign->updates()->create($data);

            DB::commit();

            SendCampaignUpdateNotificationJob::dispatch($update);

            return $update;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve campaign (admin only).
     *
     * @throws \RuntimeException
     */
    public function approve(Campaign $campaign, User $user): Campaign
    {
        if ($user->role !== 'admin') {
            throw new \RuntimeException('Hanya admin yang dapat menyetujui campaign.', 403);
        }

        if ($campaign->status !== 'review') {
            throw new \RuntimeException('Campaign yang sudah tidak berstatus review tidak dapat disetujui.', 400);
        }

        $campaign->update([
            'status' => 'active',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        $campaign->user->notify(new CampaignApprovedNotification($campaign->fresh()));

        return $campaign->fresh();
    }

    /**
     * Reject campaign (admin only).
     *
     * @throws \RuntimeException
     */
    public function reject(Campaign $campaign, User $user, ?string $rejectionNote = null): Campaign
    {
        if ($user->role !== 'admin') {
            throw new \RuntimeException('Hanya admin yang dapat menolak campaign.', 403);
        }

        if ($campaign->status !== 'review') {
            throw new \RuntimeException('Campaign yang sudah tidak berstatus review tidak dapat ditolak.', 400);
        }

        $campaign->update([
            'status' => 'draft',
            'rejection_note' => $rejectionNote,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        $campaign->user->notify(new CampaignRejectedNotification($campaign->fresh(), $rejectionNote));

        return $campaign->fresh();
    }
}