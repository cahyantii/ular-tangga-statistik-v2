<?php

namespace Tests\Feature\Admin\Management;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_only_their_own_notifications(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        $admin->notify(new AppNotification(['title' => 'Untuk Admin Ini', 'message' => 'Halo', 'category' => 'system']));
        $otherAdmin->notify(new AppNotification(['title' => 'Untuk Admin Lain', 'message' => 'Halo', 'category' => 'system']));

        $response = $this->actingAs($admin)->get('/admin/management/notifications');

        $response->assertOk()->assertSee('Untuk Admin Ini')->assertDontSee('Untuk Admin Lain');
    }

    public function test_status_filter_shows_only_unread_or_read(): void
    {
        $admin = User::factory()->admin()->create();

        $admin->notify(new AppNotification(['title' => 'Belum Dibaca Ini', 'message' => 'x', 'category' => 'system']));
        $admin->notify(new AppNotification(['title' => 'Sudah Dibaca Ini', 'message' => 'x', 'category' => 'system']));
        $admin->notifications()->where('data->title', 'Sudah Dibaca Ini')->first()->markAsRead();

        $this->actingAs($admin)->get('/admin/management/notifications?status=unread')
            ->assertSee('Belum Dibaca Ini')->assertDontSee('Sudah Dibaca Ini');

        $this->actingAs($admin)->get('/admin/management/notifications?status=read')
            ->assertSee('Sudah Dibaca Ini')->assertDontSee('Belum Dibaca Ini');
    }

    public function test_category_filter_and_search_narrow_results(): void
    {
        $admin = User::factory()->admin()->create();

        $admin->notify(new AppNotification(['title' => 'Achievement Diperoleh', 'message' => 'x', 'category' => 'achievement']));
        $admin->notify(new AppNotification(['title' => 'Room Kedaluwarsa', 'message' => 'x', 'category' => 'multiplayer']));

        $this->actingAs($admin)->get('/admin/management/notifications?category=achievement')
            ->assertSee('Achievement Diperoleh')->assertDontSee('Room Kedaluwarsa');

        $this->actingAs($admin)->get('/admin/management/notifications?search=Kedaluwarsa')
            ->assertSee('Room Kedaluwarsa')->assertDontSee('Achievement Diperoleh');
    }

    public function test_admin_can_mark_read_mark_all_read_and_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $admin->notify(new AppNotification(['title' => 'Satu', 'message' => 'x', 'category' => 'system']));
        $admin->notify(new AppNotification(['title' => 'Dua', 'message' => 'x', 'category' => 'system']));

        $first = $admin->notifications()->where('data->title', 'Satu')->first();

        $this->actingAs($admin)->patch("/notifications/{$first->id}/read")->assertRedirect();
        $this->assertNotNull($first->fresh()->read_at);

        $this->actingAs($admin)->patch('/notifications/read-all')->assertRedirect();
        $this->assertSame(0, $admin->unreadNotifications()->count());

        $this->actingAs($admin)->delete("/notifications/{$first->id}")->assertRedirect();
        $this->assertSame(1, $admin->notifications()->count());
    }

    public function test_admin_can_export_notifications(): void
    {
        $admin = User::factory()->admin()->create();
        $admin->notify(new AppNotification(['title' => 'Export Test', 'message' => 'x', 'category' => 'system']));

        $this->actingAs($admin)->get('/admin/management/notifications/export')->assertOk();
    }

    public function test_stats_counts_are_correct(): void
    {
        $admin = User::factory()->admin()->create();
        $admin->notify(new AppNotification(['title' => 'A', 'message' => 'x', 'category' => 'system']));
        $admin->notify(new AppNotification(['title' => 'B', 'message' => 'x', 'category' => 'system']));

        $response = $this->actingAs($admin)->get('/admin/management/notifications');

        $response->assertViewHas('stats', function ($stats) {
            return $stats['hari_ini'] === 2 && $stats['belum_dibaca'] === 2;
        });
    }

    public function test_player_cannot_access_admin_notification_center(): void
    {
        $player = User::factory()->create();

        $this->actingAs($player)->get('/admin/management/notifications')->assertForbidden();
    }
}
