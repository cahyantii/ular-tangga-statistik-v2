<?php

namespace Tests\Feature\Player;

use App\Enums\GameMode;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('leaderboard'))->assertRedirect(route('login'));
    }

    public function test_default_tab_shows_global_ranking(): void
    {
        $user = User::factory()->create();
        $sesi = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'user_id' => $user->id, 'skor' => 42]);

        $response = $this->actingAs($user)->get(route('leaderboard'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee('42');
    }

    public function test_robot_tab_only_shows_vs_robot_results(): void
    {
        $user = User::factory()->create();
        $sesiRobot = GameSession::factory()->finished()->create(['mode' => GameMode::VsRobot]);
        GamePlayer::factory()->create(['game_session_id' => $sesiRobot->id, 'user_id' => $user->id, 'skor' => 15]);

        $other = User::factory()->create();
        $sesiMulti = GameSession::factory()->finished()->create(['mode' => GameMode::Multiplayer]);
        GamePlayer::factory()->create(['game_session_id' => $sesiMulti->id, 'user_id' => $other->id, 'skor' => 99]);

        $response = $this->actingAs($user)->get(route('leaderboard', ['tab' => 'robot']));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertDontSee($other->name);
    }
}
