<?php

namespace Tests\Feature\Admin\Management;

use App\Enums\UserRole;
use App\Mail\VerifyEmailMail;
use App\Models\User;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_player_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/management/users', [
            'name' => 'Pemain Baru',
            'email' => 'pemain-baru@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'player',
        ]);

        $response->assertRedirect(route('admin.management.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'pemain-baru@example.com', 'role' => 'player']);
    }

    public function test_creating_an_admin_user_triggers_email_verification(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/management/users', [
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'role' => 'admin',
        ]);

        $newAdmin = User::where('email', 'admin-baru@example.com')->firstOrFail();
        $this->assertNull($newAdmin->email_verified_at);
        Mail::assertQueued(VerifyEmailMail::class, fn ($mail) => $mail->hasTo($newAdmin->email));
    }

    public function test_promoting_a_player_to_admin_triggers_email_verification(): void
    {
        Mail::fake();
        $admin = User::factory()->admin()->create();
        $player = User::factory()->create();

        $this->actingAs($admin)->put("/admin/management/users/{$player->id}", [
            'name' => $player->name,
            'email' => $player->email,
            'role' => 'admin',
        ]);

        $player->refresh();
        $this->assertSame(UserRole::Admin, $player->role);
        $this->assertNull($player->email_verified_at);
        Mail::assertQueued(VerifyEmailMail::class, fn ($mail) => $mail->hasTo($player->email));
    }

    public function test_editing_a_users_email_resets_verification_status(): void
    {
        $admin = User::factory()->admin()->create();
        $player = User::factory()->create(['email' => 'lama@example.com']);
        $this->assertNotNull($player->email_verified_at);

        $this->actingAs($admin)->put("/admin/management/users/{$player->id}", [
            'name' => $player->name,
            'email' => 'baru@example.com',
            'role' => 'player',
        ])->assertRedirect(route('admin.management.users.index'));

        $player->refresh();
        $this->assertSame('baru@example.com', $player->email);
        $this->assertNull($player->email_verified_at);
    }

    public function test_editing_an_admins_email_also_resets_verification_status(): void
    {
        $actor = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create(['email' => 'admin-lama@example.com']);
        $this->assertNotNull($otherAdmin->email_verified_at);

        $this->actingAs($actor)->put("/admin/management/users/{$otherAdmin->id}", [
            'name' => $otherAdmin->name,
            'email' => 'admin-baru@example.com',
            'role' => 'admin',
        ])->assertRedirect(route('admin.management.users.index'));

        $otherAdmin->refresh();
        $this->assertSame('admin-baru@example.com', $otherAdmin->email);
        $this->assertNull($otherAdmin->email_verified_at);
    }

    public function test_editing_a_user_without_changing_email_keeps_verification_status(): void
    {
        $admin = User::factory()->admin()->create();
        $player = User::factory()->create();
        $this->assertNotNull($player->email_verified_at);

        $this->actingAs($admin)->put("/admin/management/users/{$player->id}", [
            'name' => 'Nama Diperbarui',
            'email' => $player->email,
            'role' => 'player',
        ])->assertRedirect(route('admin.management.users.index'));

        $player->refresh();
        $this->assertSame('Nama Diperbarui', $player->name);
        $this->assertNotNull($player->email_verified_at);
    }

    public function test_updating_a_users_role_invalidates_admin_dashboard_player_stats_cache(): void
    {
        $admin = User::factory()->admin()->create();
        $player = User::factory()->create();

        Cache::put(AdminDashboardService::STATS_PLAYERS_CACHE_KEY, ['stale' => true], now()->addMinutes(5));

        $this->actingAs($admin)->put("/admin/management/users/{$player->id}", [
            'name' => $player->name,
            'email' => $player->email,
            'role' => 'admin',
        ])->assertRedirect(route('admin.management.users.index'));

        $this->assertFalse(Cache::has(AdminDashboardService::STATS_PLAYERS_CACHE_KEY));
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/admin/management/users/{$admin->id}");

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }

    public function test_admin_cannot_delete_the_last_remaining_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $anotherAdminActor = User::factory()->admin()->create();

        // Hapus semua admin lain agar $admin menjadi satu-satunya yang tersisa.
        $anotherAdminActor->delete();

        $response = $this->actingAs($admin)->delete("/admin/management/users/{$admin->id}");

        $response->assertSessionHasErrors('user');
    }

    public function test_admin_can_soft_delete_and_restore_a_player(): void
    {
        $admin = User::factory()->admin()->create();
        $player = User::factory()->create();

        $this->actingAs($admin)->delete("/admin/management/users/{$player->id}")
            ->assertRedirect(route('admin.management.users.index'));
        $this->assertSoftDeleted('users', ['id' => $player->id]);

        $this->actingAs($admin)->patch("/admin/management/users/{$player->id}/restore")
            ->assertRedirect(route('admin.management.users.index'));
        $this->assertDatabaseHas('users', ['id' => $player->id, 'deleted_at' => null]);
    }

    public function test_player_cannot_access_user_management(): void
    {
        $player = User::factory()->create();

        $this->actingAs($player)->get('/admin/management/users')->assertForbidden();
    }
}
