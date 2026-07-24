<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_recent_endpoint_returns_unread_count_and_own_notifications_only(): void
    {
        $player = User::factory()->create();
        $otherPlayer = User::factory()->create();

        $player->notify(new AppNotification(['title' => 'Punya Saya', 'message' => 'x', 'category' => 'game']));
        $otherPlayer->notify(new AppNotification(['title' => 'Punya Orang Lain', 'message' => 'x', 'category' => 'game']));

        $response = $this->actingAs($player)->getJson('/notifications/recent');

        $response->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'notifications')
            ->assertJsonPath('notifications.0.title', 'Punya Saya');
    }

    public function test_mark_read_via_json_returns_json_and_updates_unread_count(): void
    {
        $player = User::factory()->create();
        $player->notify(new AppNotification(['title' => 'Test', 'message' => 'x', 'category' => 'game']));
        $notification = $player->notifications()->first();

        $this->actingAs($player)
            ->patchJson("/notifications/{$notification->id}/read")
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertSame(0, $player->unreadNotifications()->count());
    }

    public function test_cannot_mark_read_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $owner->notify(new AppNotification(['title' => 'Rahasia', 'message' => 'x', 'category' => 'game']));
        $notification = $owner->notifications()->first();

        $this->actingAs($intruder)->patchJson("/notifications/{$notification->id}/read")->assertNotFound();
    }

    public function test_guest_cannot_access_notification_endpoints(): void
    {
        $this->getJson('/notifications/recent')->assertUnauthorized();
    }
}
