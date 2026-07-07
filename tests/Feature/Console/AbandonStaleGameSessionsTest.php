<?php

namespace Tests\Feature\Console;

use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbandonStaleGameSessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_sessions_inactive_for_more_than_24_hours_as_abandoned(): void
    {
        $user = User::factory()->create();
        $sesiBasi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        GamePlayer::factory()->create(['game_session_id' => $sesiBasi->id, 'user_id' => $user->id]);
        $sesiBasi->timestamps = false;
        $sesiBasi->updated_at = now()->subHours(25);
        $sesiBasi->save();

        $this->artisan('game:abandon-stale-sessions')->assertExitCode(0);

        $this->assertSame(GameStatus::Abandoned, $sesiBasi->fresh()->status);
        $this->assertNotNull($sesiBasi->fresh()->finished_at);
        $this->assertDatabaseHas('game_logs', [
            'game_session_id' => $sesiBasi->id,
            'event_type' => 'forfeited',
        ]);
    }

    public function test_does_not_touch_recently_active_sessions(): void
    {
        $sesiAktif = GameSession::factory()->create(['status' => GameStatus::Playing]);

        $this->artisan('game:abandon-stale-sessions')->assertExitCode(0);

        $this->assertSame(GameStatus::Playing, $sesiAktif->fresh()->status);
    }

    public function test_does_not_touch_sessions_that_are_already_finished(): void
    {
        $sesiSelesai = GameSession::factory()->create(['status' => GameStatus::Finished, 'finished_at' => now()->subDays(2)]);
        $sesiSelesai->timestamps = false;
        $sesiSelesai->updated_at = now()->subDays(2);
        $sesiSelesai->save();

        $this->artisan('game:abandon-stale-sessions')->assertExitCode(0);

        $this->assertSame(GameStatus::Finished, $sesiSelesai->fresh()->status);
    }
}
