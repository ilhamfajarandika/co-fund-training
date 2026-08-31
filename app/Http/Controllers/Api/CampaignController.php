<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campaign\ApproveCampaignRequest;
use App\Http\Requests\Campaign\StoreCampaignRequest;
use App\Http\Requests\Campaign\StoreCampaignTierRequest;
use App\Http\Requests\Campaign\StoreCampaignUpdateRequest;
use App\Http\Requests\Campaign\UpdateCampaignRequest;
use App\Http\Requests\Campaign\UpdateCampaignTierRequest;
use App\Models\Campaign;
use App\Models\CampaignTier;
use App\Services\CampaignService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CampaignController extends Controller
{
    public function __construct(private CampaignService $campaignService) {}

    /**
     * Display a listing of the resource.
     * 
     * 
     */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->campaignService->getAll(
                $request->user(),
                $request->query('status'),
                $request->query('category_id'),
                $request->query('sort')
            )
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCampaignRequest $request)
    {
        $campaign = $this->campaignService->create(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Campaign berhasil dibuat.',
            'data' => $campaign
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->campaignService->getById($id, $request->user())
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        return response()->json([
            'success' => true,
            'message' => 'Campaign berhasil diperbarui.',
            'data' => $this->campaignService->update(
                $campaign,
                $request->validated()
            )
        ]);
    }

    /**
     * Store a new campaign update.
     */
    public function storeUpdate(StoreCampaignUpdateRequest $request, Campaign $campaign)
    {
        $update = $this->campaignService->createUpdate(
            $campaign,
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Campaign update berhasil dibuat.',
            'data' => $update
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign tidak ditemukan.'
            ], 404);
        }

        try {
            $this->campaignService->delete($campaign, $request->user());
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return response()->json([
            'success' => true,
            'message' => 'Campaign berhasil dihapus.'
        ]);
    }

    /**
     * Approve campaign (admin only).
     */
    public function approve(ApproveCampaignRequest $request, Campaign $campaign)
    {
        try {
            $approved = $this->campaignService->approve($campaign, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Campaign berhasil disetujui.',
                'data' => $approved
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject campaign (admin only).
     */
    public function reject(ApproveCampaignRequest $request, Campaign $campaign)
    {
        try {
            $rejected = $this->campaignService->reject(
                $campaign,
                $request->user(),
                $request->validated()['rejection_note'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Campaign berhasil ditolak.',
                'data' => $rejected
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store campaign tier.
     */
    public function storeTier(StoreCampaignTierRequest $request, Campaign $campaign)
    {
        try {
            $tier = $this->campaignService->createTier(
                $campaign,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Tier berhasil dibuat.',
                'data' => $tier
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update campaign tier.
     */
    public function updateTier(UpdateCampaignTierRequest $request, Campaign $campaign, CampaignTier $tier)
    {
        try {
            $updated = $this->campaignService->updateTier(
                $tier,
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Tier berhasil diperbarui.',
                'data' => $updated
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete campaign tier.
     */
    public function destroyTier(Request $request, Campaign $campaign, CampaignTier $tier)
    {
        try {
            $this->campaignService->deleteTier($tier, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Tier berhasil dihapus.'
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode());
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
