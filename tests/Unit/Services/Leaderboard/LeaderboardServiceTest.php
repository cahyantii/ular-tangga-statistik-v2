<?php

namespace Tests\Unit\Services\Leaderboard;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\User;
use App\Services\Leaderboard\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LeaderboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranks_players_by_total_score_descending(): void
    {
        $tinggi = User::factory()->create();
        $rendah = User::factory()->create();
        // Viewer terpisah dari kedua pemain di atas - board() butuh user yang
        // sedang melihat papan (dipakai untuk highlight baris "current" &
        // dikecualikan dari "table"), tidak relevan untuk urutan top3 di sini.
        $viewer = User::factory()->create();

        $sesi1 = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi1->id, 'user_id' => $tinggi->id, 'skor' => 100]);

        $sesi2 = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi2->id, 'user_id' => $rendah->id, 'skor' => 10]);

        $board = (new LeaderboardService())->board(null, $viewer);

        $this->assertSame($tinggi->id, $board['top3']->first()['user_id']);
        $this->assertSame(100, $board['top3']->first()['total_skor']);
        $this->assertSame($rendah->id, $board['top3']->last()['user_id']);
    }

    public function test_excludes_robot_players_and_unfinished_sessions(): void
    {
        $viewer = User::factory()->create();

        $sesiBelumSelesai = GameSession::factory()->create(['status' => GameStatus::Playing]);
        GamePlayer::factory()->create(['game_session_id' => $sesiBelumSelesai->id, 'skor' => 999]);

        $sesiSelesai = GameSession::factory()->finished()->create();
        GamePlayer::factory()->robot()->create(['game_session_id' => $sesiSelesai->id, 'skor' => 500]);

        $board = (new LeaderboardService())->board(null, $viewer);

        $this->assertSame(0, $board['total_pemain']);
        $this->assertCount(0, $board['top3']);
    }

    public function test_filters_by_mode(): void
    {
        $vsRobotUser = User::factory()->create();
        $multiplayerUser = User::factory()->create();
        $viewer = User::factory()->create();

        $sesiRobot = GameSession::factory()->finished()->create(['mode' => GameMode::VsRobot]);
        GamePlayer::factory()->create(['game_session_id' => $sesiRobot->id, 'user_id' => $vsRobotUser->id, 'skor' => 50]);

        $sesiMulti = GameSession::factory()->finished()->create(['mode' => GameMode::Multiplayer]);
        GamePlayer::factory()->create(['game_session_id' => $sesiMulti->id, 'user_id' => $multiplayerUser->id, 'skor' => 70]);

        $service = new LeaderboardService();

        $boardRobot = $service->board(GameMode::VsRobot, $viewer);
        $boardMulti = $service->board(GameMode::Multiplayer, $viewer);

        $this->assertSame(1, $boardRobot['total_pemain']);
        $this->assertSame($vsRobotUser->id, $boardRobot['top3']->first()['user_id']);

        $this->assertSame(1, $boardMulti['total_pemain']);
        $this->assertSame($multiplayerUser->id, $boardMulti['top3']->first()['user_id']);
    }

    public function test_forget_all_clears_the_global_and_per_mode_cache(): void
    {
        $service = new LeaderboardService();

        Cache::put($service->cacheKey(null), 'stale', now()->addMinutes(5));
        Cache::put($service->cacheKey(GameMode::VsRobot), 'stale', now()->addMinutes(5));
        Cache::put($service->cacheKey(GameMode::Multiplayer), 'stale', now()->addMinutes(5));

        $service->forgetAll();

        $this->assertFalse(Cache::has($service->cacheKey(null)));
        $this->assertFalse(Cache::has($service->cacheKey(GameMode::VsRobot)));
        $this->assertFalse(Cache::has($service->cacheKey(GameMode::Multiplayer)));
    }
}
