<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider as SocialiteProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialiteAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_shows_friendly_error_when_not_configured(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $response = $this->get('/auth/google/redirect');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertStringContainsString(
            'belum lengkap',
            session('oauth_error'),
        );
    }

    public function test_github_redirect_shows_friendly_error_when_not_configured(): void
    {
        config(['services.github.client_id' => null, 'services.github.client_secret' => null]);

        $response = $this->get('/auth/github/redirect');

        $response->assertRedirect(route('login'));
        $this->assertStringContainsString('belum lengkap', session('oauth_error'));
    }

    public function test_google_redirect_goes_to_google_when_configured(): void
    {
        config([
            'services.google.client_id' => 'fake-client-id',
            'services.google.client_secret' => 'fake-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        $response = $this->get('/auth/google/redirect');

        $response->assertStatus(302);
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
        $this->assertStringContainsString('client_id=fake-client-id', $response->headers->get('Location'));
    }

    public function test_google_callback_creates_a_new_player_and_logs_in(): void
    {
        config(['services.google.client_id' => 'x', 'services.google.client_secret' => 'y']);

        $socialiteUser = SocialiteUser::fake([
            'id' => 'google-42',
            'name' => 'Baru Daftar',
            'email' => 'baru-google@example.com',
            'avatar' => 'https://lh3.googleusercontent.com/avatar.png',
        ]);

        $provider = \Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticated();
        $user = User::where('email', 'baru-google@example.com')->firstOrFail();
        $this->assertSame('google-42', $user->google_id);
        $this->assertNotNull($user->email_verified_at);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_google_callback_logs_into_existing_account_matched_by_email_without_duplicating(): void
    {
        config(['services.google.client_id' => 'x', 'services.google.client_secret' => 'y']);

        $existing = User::factory()->create(['email' => 'sudah-ada@example.com']);

        $socialiteUser = SocialiteUser::fake([
            'id' => 'google-77',
            'name' => $existing->name,
            'email' => 'sudah-ada@example.com',
        ]);

        $provider = \Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->andReturn($socialiteUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame(1, User::where('email', 'sudah-ada@example.com')->count());
    }

    public function test_google_callback_shows_friendly_error_when_provider_throws(): void
    {
        config(['services.google.client_id' => 'x', 'services.google.client_secret' => 'y']);

        $provider = \Mockery::mock(SocialiteProvider::class);
        $provider->shouldReceive('user')->andThrow(new \Exception('invalid state'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNotNull(session('oauth_error'));
    }
}
