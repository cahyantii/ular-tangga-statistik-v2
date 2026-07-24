<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_registration_always_defaults_to_player_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Pemain Baru',
            'email' => 'pemain@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => '1',
        ]);

        $user = User::where('email', 'pemain@example.com')->firstOrFail();

        $this->assertSame(UserRole::Player, $user->role);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_player_login_redirects_to_player_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('Password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_verified_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['password' => bcrypt('Password123')]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_player_cannot_access_admin_area(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_unverified_admin_is_redirected_to_verification_notice(): void
    {
        $admin = User::factory()->admin()->unverified()->create();

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('verification.notice'));
    }

    public function test_verified_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Selamat datang');
    }
}
