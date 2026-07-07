<?php

namespace Tests\Feature\Console;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\PlayerStatus;
use App\Enums\WinReason;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\GameSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckGameHeartbeatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        GameSetting::factory()->create(['key' => 'heartbeat_timeout_seconds', 'value' => '15']);
        GameSetting::factory()->create(['key' => 'reconnect_timeout_seconds', 'value' => '60']);
        GameSetting::factory()->create(['key' => 'win_point', 'value' => '100']);
    }

    public function test_pauses_the_session_when_a_players_heartbeat_goes_stale(): void
    {
        $sesi = GameSession::factory()->create(['mode' => GameMode::Multiplayer, 'status' => GameStatus::Playing]);
        $stale = GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'turn_order' => 1,
            'last_heartbeat_at' => now()->subSeconds(30),
        ]);
        GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'turn_order' => 2,
            'last_heartbeat_at' => now(),
        ]);

        $this->artisan('game:check-heartbeats')->assertExitCode(0);

        $this->assertSame(GameStatus::Paused, $sesi->fresh()->status);
        $this->assertSame(PlayerStatus::Disconnected, $stale->fresh()->status);
        $this->assertDatabaseHas('game_logs', [
            'game_session_id' => $sesi->id,
            'event_type' => 'paused',
        ]);
    }

    public function test_does_not_pause_a_session_where_both_players_are_active(): void
    {
        $sesi = GameSession::factory()->create(['mode' => GameMode::Multiplayer, 'status' => GameStatus::Playing]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'last_heartbeat_at' => now()]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2, 'last_heartbeat_at' => now()]);

        $this->artisan('game:check-heartbeats')->assertExitCode(0);

        $this->assertSame(GameStatus::Playing, $sesi->fresh()->status);
    }

    public function test_resumes_a_paused_session_once_the_disconnected_player_heartbeats_again(): void
    {
        $sesi = GameSession::factory()->create(['mode' => GameMode::Multiplayer, 'status' => GameStatus::Paused]);
        $reconnected = GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'turn_order' => 1,
            'status' => PlayerStatus::Disconnected,
            'last_heartbeat_at' => now(),
        ]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);

        $this->artisan('game:check-heartbeats')->assertExitCode(0);

        $this->assertSame(GameStatus::Playing, $sesi->fresh()->status);
        $this->assertSame(PlayerStatus::Active, $reconnected->fresh()->status);
        $this->assertDatabaseHas('game_logs', [
            'game_session_id' => $sesi->id,
            'event_type' => 'resumed',
        ]);
    }

    public function test_forfeits_the_disconnected_player_after_the_reconnect_grace_period_expires(): void
    {
        $sesi = GameSession::factory()->create(['mode' => GameMode::Multiplayer, 'status' => GameStatus::Paused, 'started_at' => now()->subMinutes(5)]);
        $disconnected = GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'turn_order' => 1,
            'status' => PlayerStatus::Disconnected,
            'last_heartbeat_at' => now()->subSeconds(90),
        ]);
        $opponent = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2, 'skor' => 10]);

        $disconnected->timestamps = false;
        $disconnected->updated_at = now()->subSeconds(90);
        $disconnected->save();

        $this->artisan('game:check-heartbeats')->assertExitCode(0);

        $sesiSegar = $sesi->fresh();
        $this->assertSame(GameStatus::Finished, $sesiSegar->status);
        $this->assertSame($opponent->id, $sesiSegar->winner_game_player_id);
        $this->assertSame(WinReason::Forfeit, $sesiSegar->win_reason);
        $this->assertSame(110, $opponent->fresh()->skor);
    }

    public function test_does_not_forfeit_before_the_grace_period_expires(): void
    {
        $sesi = GameSession::factory()->create(['mode' => GameMode::Multiplayer, 'status' => GameStatus::Paused]);
        $disconnected = GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'turn_order' => 1,
            'status' => PlayerStatus::Disconnected,
            'last_heartbeat_at' => now()->subSeconds(20),
        ]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);

        $disconnected->timestamps = false;
        $disconnected->updated_at = now()->subSeconds(20);
        $disconnected->save();

        $this->artisan('game:check-heartbeats')->assertExitCode(0);

        $this->assertSame(GameStatus::Paused, $sesi->fresh()->status);
    }
}
