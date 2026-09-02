<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Backing;
use Illuminate\Database\Seeder;

class CampaignLifecycleTestSeeder extends Seeder
{
    public function run(): void
    {
        // Campaign success expired
        $campaign1 = Campaign::create([
            'user_id' => 3,
            'category_id' => 1,
            'title' => 'Campaign Expired Success Testing',
            'slug' => 'campaign-expired-success-testing',
            'description' => 'Campaign expired yang sudah mencapai target. Minimal 20 karakter ya.',
            'target_amount' => 100000,
            'collected_amount' => 120000,
            'deadline' => now()->subDay(),
            'status' => 'active'
        ]);
        echo 'Success campaign ID: ' . $campaign1->id . PHP_EOL;

        // Campaign failed expired
        $campaign2 = Campaign::create([
            'user_id' => 3,
            'category_id' => 1,
            'title' => 'Campaign Expired Failed Testing',
            'slug' => 'campaign-expired-failed-testing',
            'description' => 'Campaign expired yang gagal mencapai target. Minimal 20 karakter ya.',
            'target_amount' => 200000,
            'collected_amount' => 50000,
            'deadline' => now()->subDay(),
            'status' => 'active'
        ]);
        echo 'Failed campaign ID: ' . $campaign2->id . PHP_EOL;

        // Backing for failed campaign
        $backing = Backing::create([
            'campaign_id' => $campaign2->id,
            'user_id' => 2,
            'amount' => 50000,
            'status' => 'completed'
        ]);
        echo 'Backing ID: ' . $backing->id . PHP_EOL;

        // Campaign H-3
        $campaign3 = Campaign::create([
            'user_id' => 3,
            'category_id' => 1,
            'title' => 'Campaign Deadline H-3 Testing',
            'slug' => 'campaign-deadline-h3-testing',
            'description' => 'Campaign dengan deadline 3 hari lagi untuk testing notification. Minimal 20 karakter ya.',
            'target_amount' => 100000,
            'collected_amount' => 50000,
            'deadline' => now()->addDays(3),
            'status' => 'active'
        ]);
        echo 'H-3 campaign ID: ' . $campaign3->id . PHP_EOL;

        $backing2 = Backing::create([
            'campaign_id' => $campaign3->id,
            'user_id' => 2,
            'amount' => 25000,
            'status' => 'completed'
        ]);

        // Campaign H-1
        $campaign4 = Campaign::create([
            'user_id' => 3,
            'category_id' => 1,
            'title' => 'Campaign Deadline H-1 Testing',
            'slug' => 'campaign-deadline-h1-testing',
            'description' => 'Campaign dengan deadline 1 hari lagi untuk testing notification. Minimal 20 karakter ya.',
            'target_amount' => 100000,
            'collected_amount' => 30000,
            'deadline' => now()->addDay(),
            'status' => 'active'
        ]);
        echo 'H-1 campaign ID: ' . $campaign4->id . PHP_EOL;

        $backing3 = Backing::create([
            'campaign_id' => $campaign4->id,
            'user_id' => 2,
            'amount' => 30000,
            'status' => 'completed'
        ]);

        echo PHP_EOL . 'Test data created successfully!' . PHP_EOL;
        echo 'Save these IDs to your Postman environment:' . PHP_EOL;
        echo 'campaign_expired_success_id = ' . $campaign1->id . PHP_EOL;
        echo 'campaign_expired_failed_id = ' . $campaign2->id . PHP_EOL;
        echo 'backing_id = ' . $backing->id . PHP_EOL;
        echo 'campaign_h3_id = ' . $campaign3->id . PHP_EOL;
        echo 'campaign_h1_id = ' . $campaign4->id . PHP_EOL;
    }
}
