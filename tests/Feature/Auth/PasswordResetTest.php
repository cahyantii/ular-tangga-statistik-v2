<?php

namespace Tests\Feature\Auth;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Mail::assertQueued(ResetPasswordMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) {
            $response = $this->get('/reset-password/'.$this->extractToken($mail));

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Mail::assertQueued(ResetPasswordMail::class, function (ResetPasswordMail $mail) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $this->extractToken($mail),
                'email' => $user->email,
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    private function extractToken(ResetPasswordMail $mail): string
    {
        return Str::before(Str::after($mail->resetUrl, '/reset-password/'), '?');
    }
}
