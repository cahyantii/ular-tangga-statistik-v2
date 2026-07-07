<?php

namespace Tests\Feature\Player;

use App\Enums\AchievementCriteriaType;
use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\PlayerStatus;
use App\Models\Achievement;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\KategoriMateri;
use App\Models\LearningProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_active_vs_robot_and_multiplayer_shortcuts_when_no_active_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Main vs Robot');
        $response->assertSee(route('game.robot.store'), false);
        $response->assertSee('Main Multiplayer');
        $response->assertSee(route('game.multiplayer.lobby'), false);
        $response->assertDontSee('Segera hadir');
        $response->assertDontSee('Lanjutkan Permainan');
    }

    public function test_dashboard_shows_continue_playing_card_when_active_session_exists(): void
    {
        $user = User::factory()->create();
        $sesi = GameSession::factory()->create(['mode' => GameMode::VsRobot, 'status' => GameStatus::Playing]);
        GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'user_id' => $user->id,
            'status' => PlayerStatus::Active,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Lanjutkan Permainan');
        $response->assertDontSee('Segera hadir');
    }

    public function test_dashboard_continue_playing_card_points_to_waiting_room_for_a_waiting_multiplayer_session(): void
    {
        $user = User::factory()->create();
        $room = \App\Models\Room::factory()->create(['created_by' => $user->id]);
        $sesi = GameSession::factory()->create([
            'room_id' => $room->id,
            'mode' => GameMode::Multiplayer,
            'status' => GameStatus::Waiting,
        ]);
        GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'user_id' => $user->id,
            'turn_order' => 1,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Lanjutkan Permainan');
        $response->assertSee(route('game.room.show', $room), false);
    }

    public function test_participant_can_view_their_game_session_status_page(): void
    {
        $user = User::factory()->create();
        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'user_id' => $user->id]);

        $this->actingAs($user)->get("/main/{$sesi->id}")->assertOk();
    }

    public function test_non_participant_cannot_view_someone_elses_game_session(): void
    {
        $user = User::factory()->create();
        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id]);

        $this->actingAs($user)->get("/main/{$sesi->id}")->assertForbidden();
    }

    public function test_dashboard_shows_aggregated_stats_progress_and_achievement_teaser(): void
    {
        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create(['nama' => 'Statistika Dasar']);

        LearningProgress::factory()->create([
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 10,
            'total_benar' => 8,
            'total_salah' => 2,
            'accuracy' => 80,
        ]);

        $sesi = GameSession::factory()->finished()->create(['mode' => GameMode::VsRobot]);
        $pemenang = GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'user_id' => $user->id,
            'skor' => 120,
        ]);
        $sesi->update(['winner_game_player_id' => $pemenang->id]);

        Achievement::factory()->create([
            'nama' => 'Menang Pertama',
            'syarat_type' => AchievementCriteriaType::TotalMenang,
            'syarat_value' => 3,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSeeText('Statistika Dasar');
        $response->assertSeeText('Menang Pertama');
        $response->assertSeeText('1 / 3');
    }
}
