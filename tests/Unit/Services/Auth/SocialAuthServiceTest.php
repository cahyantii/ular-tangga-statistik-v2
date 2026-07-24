<?php

namespace Tests\Unit\Services\Auth;

use App\Enums\UserRole;
use App\Exceptions\OAuthProviderNotConfiguredException;
use App\Models\User;
use App\Services\Auth\SocialAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class SocialAuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private SocialAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SocialAuthService();
    }

    public function test_creates_a_new_player_when_email_is_unknown(): void
    {
        $socialiteUser = SocialiteUser::fake([
            'id' => 'google-123',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'avatar' => 'https://lh3.googleusercontent.com/jane.jpg',
        ]);

        $user = $this->service->findOrCreateUser('google', $socialiteUser);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'google_id' => 'google-123']);
        $this->assertSame(UserRole::Player, $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('https://lh3.googleusercontent.com/jane.jpg', $user->avatar);
    }

    public function test_repeat_login_with_same_provider_id_does_not_duplicate(): void
    {
        $socialiteUser = SocialiteUser::fake([
            'id' => 'google-123',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $first = $this->service->findOrCreateUser('google', $socialiteUser);
        $second = $this->service->findOrCreateUser('google', $socialiteUser);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, User::where('email', 'jane@example.com')->count());
    }

    public function test_google_login_links_to_an_existing_manually_registered_account_instead_of_duplicating(): void
    {
        $existing = User::factory()->create(['email' => 'sudah-daftar@example.com', 'google_id' => null]);

        $socialiteUser = SocialiteUser::fake([
            'id' => 'google-999',
            'name' => $existing->name,
            'email' => 'sudah-daftar@example.com',
        ]);

        $result = $this->service->findOrCreateUser('google', $socialiteUser);

        $this->assertSame($existing->id, $result->id);
        $this->assertSame(1, User::where('email', 'sudah-daftar@example.com')->count());
        $this->assertSame('google-999', $result->fresh()->google_id);
    }

    public function test_github_login_links_to_the_same_account_a_google_login_already_created(): void
    {
        $googleUser = SocialiteUser::fake([
            'id' => 'google-1',
            'name' => 'Multi Provider',
            'email' => 'multi@example.com',
        ]);
        $created = $this->service->findOrCreateUser('google', $googleUser);

        $githubUser = SocialiteUser::fake([
            'id' => 'github-1',
            'name' => 'Multi Provider',
            'email' => 'multi@example.com',
        ]);
        $linked = $this->service->findOrCreateUser('github', $githubUser);

        $this->assertSame($created->id, $linked->id);
        $this->assertSame(1, User::where('email', 'multi@example.com')->count());
        $linked->refresh();
        $this->assertSame('google-1', $linked->google_id);
        $this->assertSame('github-1', $linked->github_id);
    }

    public function test_assert_provider_configured_throws_when_credentials_are_blank(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->expectException(OAuthProviderNotConfiguredException::class);

        $this->service->assertProviderConfigured('google');
    }

    public function test_assert_provider_configured_passes_when_credentials_are_present(): void
    {
        config(['services.google.client_id' => 'x', 'services.google.client_secret' => 'y']);

        $this->service->assertProviderConfigured('google');

        $this->assertTrue(true);
    }
}
