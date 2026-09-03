<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'slug' => Str::slug(fake()->sentence(3)),
            'description' => fake()->paragraph(3),
            'target_amount' => fake()->randomFloat(2, 1000, 100000),
            'collected_amount' => 0,
            'deadline' => now()->addDays(30),
            'status' => 'draft',
            'video_url' => null,
        ];
    }
}
