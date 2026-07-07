<?php

namespace Tests\Feature\Admin\Management;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
        Notification::fake();
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
        Notification::assertSentTo($newAdmin, VerifyEmail::class);
    }

    public function test_promoting_a_player_to_admin_triggers_email_verification(): void
    {
        Notification::fake();
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
        Notification::assertSentTo($player, VerifyEmail::class);
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
