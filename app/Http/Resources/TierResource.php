<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'name' => $this->name,
            'min_amount' => $this->min_amount,
            'quota' => $this->quota,
            'remaining_quota' => $this->remaining_quota,
            'reward_description' => $this->reward_description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
