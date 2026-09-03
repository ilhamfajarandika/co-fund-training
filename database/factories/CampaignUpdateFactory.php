<?php

namespace Database\Factories;

use App\Models\CampaignUpdate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignUpdateFactory extends Factory
{
    protected $model = CampaignUpdate::class;

    public function definition(): array
    {
        return [
            'campaign_id' => \App\Models\Campaign::factory(),
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(5),
        ];
    }
}
