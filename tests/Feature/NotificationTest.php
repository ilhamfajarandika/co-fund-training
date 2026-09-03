<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignUpdate;
use App\Models\Backing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_get_notifications_list(): void
    {
        $this->user->notify(new \App\Notifications\CampaignUpdateNotification(
            CampaignUpdate::factory()->create([
                'campaign_id' => Campaign::factory()->create(['user_id' => $this->user->id])->id,
            ])
        ));

        $response = $this->getJson('/api/v1/notifications');

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'notifiable_type',
                        'notifiable_id',
                        'type',
                        'data',
                        'read_at',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]
        ]);
    }

    public function test_get_unread_count(): void
    {
        $this->user->notify(new \App\Notifications\CampaignUpdateNotification(
            CampaignUpdate::factory()->create([
                'campaign_id' => Campaign::factory()->create(['user_id' => $this->user->id])->id,
            ])
        ));

        $response = $this->getJson('/api/v1/notifications/unread-count');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => ['unread_count' => 1],
        ]);
    }

    public function test_mark_as_read(): void
    {
        $this->user->notify(new \App\Notifications\CampaignUpdateNotification(
            CampaignUpdate::factory()->create([
                'campaign_id' => Campaign::factory()->create(['user_id' => $this->user->id])->id,
            ])
        ));

        $notificationId = $this->user->notifications()->first()->id;

        $response = $this->patchJson("/api/v1/notifications/{$notificationId}/read");

        $response->assertOk();
        $this->assertNotNull($this->user->notifications()->first()->read_at);
    }

    public function test_mark_as_read_not_found(): void
    {
        $response = $this->patchJson('/api/v1/notifications/non-existent-id/read');

        $response->assertNotFound();
        $response->assertJson([
            'success' => false,
            'message' => 'Notifikasi tidak ditemukan.',
        ]);
    }

    public function test_mark_all_as_read(): void
    {
        $this->user->notify(new \App\Notifications\CampaignUpdateNotification(
            CampaignUpdate::factory()->create([
                'campaign_id' => Campaign::factory()->create(['user_id' => $this->user->id])->id,
            ])
        ));

        $this->user->notify(new \App\Notifications\CampaignApprovedNotification(
            Campaign::factory()->create(['user_id' => $this->user->id])
        ));

        $this->assertEquals(2, $this->user->unreadNotifications()->count());

        $response = $this->patchJson('/api/v1/notifications/read-all');

        $response->assertOk();
        $this->assertEquals(0, $this->user->fresh()->unreadNotifications()->count());
    }

    public function test_notification_routes_require_auth(): void
    {
        auth()->logout();

        $this->getJson('/api/v1/notifications')->assertUnauthorized();
        $this->getJson('/api/v1/notifications/unread-count')->assertUnauthorized();
        $this->patchJson('/api/v1/notifications/1/read')->assertUnauthorized();
        $this->patchJson('/api/v1/notifications/read-all')->assertUnauthorized();
    }
}
