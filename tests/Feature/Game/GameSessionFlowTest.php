<?php

namespace Tests\Feature\Game;

use App\Enums\GameStatus;
use App\Enums\PlayerStatus;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\GameSetting;
use App\Models\KategoriMateri;
use App\Models\PapanPermainan;
use App\Models\Petak;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameSessionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        GameSetting::factory()->create(['key' => 'correct_answer_point', 'value' => '10']);
        GameSetting::factory()->create(['key' => 'wrong_answer_penalty', 'value' => '5']);
        GameSetting::factory()->create(['key' => 'bonus_point', 'value' => '20']);
        GameSetting::factory()->create(['key' => 'tile_penalty_point', 'value' => '5']);
        GameSetting::factory()->create(['key' => 'win_point', 'value' => '100']);
        GameSetting::factory()->create(['key' => 'question_timer_seconds', 'value' => '30']);
    }

    private function diceValueFor(string $seed, int $turn): int
    {
        return (crc32("{$seed}:{$turn}") % 6) + 1;
    }

    /**
     * Buat papan 50 petak (default biasa), lalu set petak posisi tertentu ke jenis khusus.
     */
    private function makeBoardWithTile(int $posisi, string $jenis, ?int $kategoriId = null): PapanPermainan
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50, 'jumlah_kolom' => 10]);

        for ($i = 1; $i <= 50; $i++) {
            Petak::factory()->create([
                'papan_id' => $papan->id,
                'posisi' => $i,
                'jenis_petak' => $i === $posisi ? $jenis : ($i === 1 ? 'start' : ($i === 50 ? 'finish' : 'biasa')),
                'kategori_id' => $i === $posisi ? $kategoriId : null,
            ]);
        }

        return $papan;
    }

    public function test_creating_vs_robot_session_creates_two_players_and_starts_playing(): void
    {
        PapanPermainan::factory()->create(['is_active' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/main/vs-robot');

        $gameSession = GameSession::query()->latest('id')->firstOrFail();
        $response->assertRedirect(route('game.show', $gameSession));

        $this->assertSame(GameStatus::Playing, $gameSession->status);
        $this->assertCount(2, $gameSession->players);
        $this->assertTrue($gameSession->players->contains(fn (GamePlayer $p) => $p->user_id === $user->id && ! $p->is_robot));
        $this->assertTrue($gameSession->players->contains(fn (GamePlayer $p) => $p->is_robot && $p->user_id === null));
        $this->assertNotNull($gameSession->current_turn_game_player_id);
    }

    public function test_cannot_create_a_second_vs_robot_session_while_one_is_active(): void
    {
        PapanPermainan::factory()->create(['is_active' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/main/vs-robot')->assertRedirect();
        $response = $this->actingAs($user)->post('/main/vs-robot');

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertCount(1, GameSession::all());
    }

    public function test_only_the_current_turn_player_can_roll_dice(): void
    {
        $papan = $this->makeBoardWithTile(10, 'biasa');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p2->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertStatus(403);
    }

    public function test_rolling_dice_moves_pawn_and_advances_turn_on_biasa_tile(): void
    {
        $papan = $this->makeBoardWithTile(10, 'biasa');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-1', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 0]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2, 'posisi_pion' => 0]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $nilai = $this->diceValueFor('seed-1', 0);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonPath('nilai_dadu', $nilai);
        $this->assertSame($nilai, $p1->fresh()->posisi_pion);
        $this->assertSame($p2->id, $sesi->fresh()->current_turn_game_player_id);
        $this->assertSame(1, $sesi->fresh()->total_turn);
        $this->assertDatabaseHas('game_logs', ['game_session_id' => $sesi->id, 'event_type' => 'dice_rolled']);
        $this->assertDatabaseHas('game_logs', ['game_session_id' => $sesi->id, 'event_type' => 'pawn_moved']);
    }

    public function test_dice_roll_beyond_finish_is_blocked_but_still_advances_turn(): void
    {
        $papan = $this->makeBoardWithTile(10, 'biasa');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-block', 'total_turn' => 0]);
        $nilai = $this->diceValueFor('seed-block', 0);

        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 50 - $nilai + 1]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);
        $posisiSebelum = $p1->posisi_pion;

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonPath('type', 'blocked');
        $this->assertSame($posisiSebelum, $p1->fresh()->posisi_pion);
        $this->assertSame($p2->id, $sesi->fresh()->current_turn_game_player_id);
        $this->assertDatabaseHas('game_logs', ['game_session_id' => $sesi->id, 'event_type' => 'movement_blocked']);
    }

    public function test_landing_on_bonus_tile_adds_points_and_advances_turn(): void
    {
        $nilai = $this->diceValueFor('seed-bonus', 0);
        $papan = $this->makeBoardWithTile($nilai, 'bonus');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-bonus', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 0, 'skor' => 0]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonPath('type', 'bonus');
        $this->assertSame(20, $p1->fresh()->skor);
        $this->assertSame($p2->id, $sesi->fresh()->current_turn_game_player_id);
    }

    public function test_landing_on_soal_tile_presents_question_without_advancing_turn(): void
    {
        $kategori = KategoriMateri::factory()->create();
        Soal::factory()->create(['kategori_id' => $kategori->id, 'kunci_jawaban' => 'B']);

        $nilai = $this->diceValueFor('seed-soal', 0);
        $papan = $this->makeBoardWithTile($nilai, 'soal', $kategori->id);
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-soal', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 0]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonPath('type', 'soal');
        $response->assertJsonMissingPath('soal.kunci_jawaban');
        // Giliran TIDAK berpindah - masih menunggu jawaban.
        $this->assertSame($p1->id, $sesi->fresh()->current_turn_game_player_id);
        $this->assertNotNull($sesi->fresh()->active_question_id);
    }

    public function test_submitting_correct_answer_awards_points_and_advances_turn(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id, 'kunci_jawaban' => 'B']);

        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'skor' => 0]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update([
            'current_turn_game_player_id' => $p1->id,
            'active_question_id' => $soal->id,
            'active_question_expires_at' => now()->addSeconds(30),
        ]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/answer", [
            'soal_id' => $soal->id,
            'jawaban' => 'B',
        ]);

        $response->assertOk();
        $response->assertJsonPath('benar', true);
        $this->assertSame(10, $p1->fresh()->skor);
        $this->assertSame($p2->id, $sesi->fresh()->current_turn_game_player_id);
        $this->assertNull($sesi->fresh()->active_question_id);
    }

    public function test_submitting_wrong_answer_penalizes_and_advances_turn(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id, 'kunci_jawaban' => 'B']);

        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'skor' => 20]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update([
            'current_turn_game_player_id' => $p1->id,
            'active_question_id' => $soal->id,
            'active_question_expires_at' => now()->addSeconds(30),
        ]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/answer", [
            'soal_id' => $soal->id,
            'jawaban' => 'A',
        ]);

        $response->assertOk();
        $response->assertJsonPath('benar', false);
        $this->assertSame(15, $p1->fresh()->skor);
        $this->assertSame($p2->id, $sesi->fresh()->current_turn_game_player_id);
    }

    public function test_submitting_an_answer_updates_the_players_learning_progress(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id, 'kunci_jawaban' => 'B']);

        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update([
            'current_turn_game_player_id' => $p1->id,
            'active_question_id' => $soal->id,
            'active_question_expires_at' => now()->addSeconds(30),
        ]);

        $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/answer", [
            'soal_id' => $soal->id,
            'jawaban' => 'B',
        ])->assertOk();

        $this->assertDatabaseHas('learning_progress', [
            'user_id' => $p1->user_id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 1,
            'total_benar' => 1,
        ]);
    }

    public function test_reaching_finish_evaluates_and_grants_a_newly_earned_achievement(): void
    {
        $achievement = \App\Models\Achievement::factory()->create([
            'syarat_type' => \App\Enums\AchievementCriteriaType::TotalPermainan,
            'syarat_value' => 1,
        ]);

        $nilai = $this->diceValueFor('seed-achievement', 0);
        $papan = $this->makeBoardWithTile(50, 'finish');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-achievement', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 50 - $nilai]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll")->assertOk();

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $p1->user_id,
            'achievement_id' => $achievement->id,
        ]);
    }

    public function test_reaching_finish_issues_a_certificate_when_the_player_is_already_eligible(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $kategori = KategoriMateri::factory()->create();

        $nilai = $this->diceValueFor('seed-sertifikat', 0);
        $papan = $this->makeBoardWithTile(50, 'finish');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-sertifikat', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 50 - $nilai]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        // Pemain ini sudah punya progress belajar 100% akurasi di satu-satunya
        // kategori aktif SEBELUM permainan ini - permainan ini sendiri yang
        // melengkapi syarat ketiga ("minimal satu game selesai").
        \App\Models\LearningProgress::create([
            'user_id' => $p1->user_id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 5,
            'total_benar' => 5,
            'accuracy' => 100,
        ]);

        $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll")->assertOk();

        $this->assertDatabaseHas('certificates', ['user_id' => $p1->user_id]);
    }

    public function test_expired_question_is_always_resolved_as_wrong_even_if_answer_would_be_correct(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id, 'kunci_jawaban' => 'B']);

        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'skor' => 20]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update([
            'current_turn_game_player_id' => $p1->id,
            'active_question_id' => $soal->id,
            'active_question_expires_at' => now()->subSecond(),
        ]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/answer", [
            'soal_id' => $soal->id,
            'jawaban' => 'B',
        ]);

        $response->assertOk();
        $response->assertJsonPath('benar', false);
        $this->assertSame(15, $p1->fresh()->skor);
    }

    public function test_submitting_answer_for_a_stale_question_id_is_rejected(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id]);

        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1]);
        $sesi->update(['current_turn_game_player_id' => $p1->id, 'active_question_id' => null]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/answer", [
            'soal_id' => $soal->id,
            'jawaban' => 'A',
        ]);

        $response->assertStatus(409);
    }

    public function test_reaching_finish_ends_the_game_and_awards_win_point(): void
    {
        $nilai = $this->diceValueFor('seed-finish', 0);
        $papan = $this->makeBoardWithTile(50, 'finish');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-finish', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 50 - $nilai, 'skor' => 30]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonPath('type', 'finished');
        $sesiSegar = $sesi->fresh();
        $this->assertSame(GameStatus::Finished, $sesiSegar->status);
        $this->assertSame($p1->id, $sesiSegar->winner_game_player_id);
        $this->assertSame(130, $p1->fresh()->skor); // 30 + win_point(100)
        $this->assertNotNull($sesiSegar->finished_at);
        $this->assertNotNull($sesiSegar->duration_seconds);
    }

    public function test_robot_turn_is_played_automatically_after_human_turn_on_biasa_tile(): void
    {
        $papan = $this->makeBoardWithTile(10, 'biasa');
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-robot-basic', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 0]);
        $robot = GamePlayer::factory()->robot()->create(['game_session_id' => $sesi->id, 'turn_order' => 2, 'posisi_pion' => 0]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $nilaiHuman = $this->diceValueFor('seed-robot-basic', 0);
        $nilaiRobot = $this->diceValueFor('seed-robot-basic', 1);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonPath('nilai_dadu', $nilaiHuman);
        $response->assertJsonCount(1, 'robot_turns');
        $response->assertJsonPath('robot_turns.0.nilai_dadu', $nilaiRobot);

        $this->assertSame($nilaiHuman, $p1->fresh()->posisi_pion);
        $this->assertSame($nilaiRobot, $robot->fresh()->posisi_pion);
        $this->assertSame($p1->id, $sesi->fresh()->current_turn_game_player_id);
        $this->assertSame(2, $sesi->fresh()->total_turn);
        $this->assertDatabaseHas('game_logs', [
            'game_session_id' => $sesi->id,
            'event_type' => 'dice_rolled',
            'user_id' => null,
        ]);
    }

    public function test_robot_answers_a_soal_tile_immediately_without_a_separate_request(): void
    {
        $kategori = KategoriMateri::factory()->create();
        Soal::factory()->create(['kategori_id' => $kategori->id, 'kunci_jawaban' => 'B']);

        $nilaiRobot = $this->diceValueFor('seed-robot-soal', 1);
        $papan = $this->makeBoardWithTile($nilaiRobot, 'soal', $kategori->id);
        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-robot-soal', 'total_turn' => 0]);

        // Pemain manusia dimulai jauh dari petak soal supaya tidak ikut mendarat di sana.
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 20]);
        $robot = GamePlayer::factory()->robot()->create(['game_session_id' => $sesi->id, 'turn_order' => 2, 'posisi_pion' => 0]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonCount(1, 'robot_turns');
        $response->assertJsonPath('robot_turns.0.type', 'soal');
        $this->assertIsBool($response->json('robot_turns.0.benar'));

        $this->assertSame($nilaiRobot, $robot->fresh()->posisi_pion);
        $this->assertSame($p1->id, $sesi->fresh()->current_turn_game_player_id);
        $this->assertNull($sesi->fresh()->active_question_id);
        $this->assertDatabaseHas('game_logs', [
            'game_session_id' => $sesi->id,
            'event_type' => 'answer_submitted',
        ]);
    }

    public function test_robot_reaching_finish_ends_the_game_during_the_automatic_turn(): void
    {
        $papan = $this->makeBoardWithTile(50, 'finish');
        $nilaiRobot = $this->diceValueFor('seed-robot-finish', 1);

        $sesi = GameSession::factory()->create(['papan_id' => $papan->id, 'status' => GameStatus::Playing, 'random_seed' => 'seed-robot-finish', 'total_turn' => 0]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 20]);
        $robot = GamePlayer::factory()->robot()->create(['game_session_id' => $sesi->id, 'turn_order' => 2, 'posisi_pion' => 50 - $nilaiRobot]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll");

        $response->assertOk();
        $response->assertJsonCount(1, 'robot_turns');
        $response->assertJsonPath('robot_turns.0.type', 'finished');

        $sesiSegar = $sesi->fresh();
        $this->assertSame(GameStatus::Finished, $sesiSegar->status);
        $this->assertSame($robot->id, $sesiSegar->winner_game_player_id);
        $this->assertNotNull($sesiSegar->finished_at);
    }

    public function test_leaving_a_vs_robot_game_marks_it_abandoned(): void
    {
        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->post("/main/{$sesi->id}/leave");

        $response->assertRedirect(route('dashboard'));
        $this->assertSame(GameStatus::Abandoned, $sesi->fresh()->status);
    }

    public function test_state_endpoint_returns_authoritative_session_data(): void
    {
        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'skor' => 42]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->getJson("/main/{$sesi->id}/state");

        $response->assertOk();
        $response->assertJsonPath('id', $sesi->id);
        $response->assertJsonPath('players.0.skor', 42);
    }

    public function test_state_endpoint_exposes_active_question_content_for_refresh_recovery(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id, 'pertanyaan' => 'Apa itu median?']);

        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);
        $sesi->update([
            'current_turn_game_player_id' => $p1->id,
            'active_question_id' => $soal->id,
            'active_question_expires_at' => now()->addSeconds(30),
        ]);

        $response = $this->actingAs($p1->user)->getJson("/main/{$sesi->id}/state");

        $response->assertOk();
        $response->assertJsonPath('active_question.id', $soal->id);
        $response->assertJsonPath('active_question.pertanyaan', 'Apa itu median?');
        $response->assertJsonMissingPath('active_question.kunci_jawaban');
    }

    public function test_state_endpoint_exposes_room_id_and_reconnect_deadline_only_when_paused(): void
    {
        GameSetting::factory()->create(['key' => 'reconnect_timeout_seconds', 'value' => '60']);

        $room = \App\Models\Room::factory()->create();
        $sesi = GameSession::factory()->create([
            'mode' => \App\Enums\GameMode::Multiplayer,
            'room_id' => $room->id,
            'status' => GameStatus::Playing,
        ]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->getJson("/main/{$sesi->id}/state");
        $response->assertOk();
        $response->assertJsonPath('room_id', $room->id);
        $response->assertJsonMissingPath('reconnect_deadline_at');

        $sesi->update(['status' => GameStatus::Paused]);

        $response = $this->actingAs($p1->user)->getJson("/main/{$sesi->id}/state");
        $response->assertOk();
        $this->assertNotNull($response->json('reconnect_deadline_at'));
    }

    public function test_show_page_embeds_heartbeat_url_and_paused_banner_markup(): void
    {
        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->get("/main/{$sesi->id}");

        $response->assertOk();
        $response->assertSee(route('game.heartbeat', $sesi), false);
        $response->assertSee('game-paused-banner', false);
    }

    public function test_heartbeat_refreshes_last_heartbeat_at(): void
    {
        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'last_heartbeat_at' => now()->subMinutes(5)]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/heartbeat");

        $response->assertOk();
        $this->assertTrue($p1->fresh()->last_heartbeat_at->gt(now()->subSeconds(5)));
    }

    public function test_heartbeat_resumes_a_paused_multiplayer_session_immediately(): void
    {
        $sesi = GameSession::factory()->create(['mode' => \App\Enums\GameMode::Multiplayer, 'status' => GameStatus::Paused]);
        $disconnected = GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'turn_order' => 1,
            'status' => \App\Enums\PlayerStatus::Disconnected,
        ]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);

        $response = $this->actingAs($disconnected->user)->postJson("/main/{$sesi->id}/heartbeat");

        $response->assertOk();
        $this->assertSame(GameStatus::Playing, $sesi->fresh()->status);
        $this->assertSame(\App\Enums\PlayerStatus::Active, $disconnected->fresh()->status);
        $this->assertDatabaseHas('game_logs', [
            'game_session_id' => $sesi->id,
            'event_type' => 'resumed',
        ]);
    }

    public function test_non_participant_cannot_act_on_someone_elses_session(): void
    {
        $sesi = GameSession::factory()->create(['status' => GameStatus::Playing]);
        GamePlayer::factory()->create(['game_session_id' => $sesi->id]);
        $outsider = User::factory()->create();

        $this->actingAs($outsider)->postJson("/main/{$sesi->id}/roll")->assertStatus(403);
        $this->actingAs($outsider)->getJson("/main/{$sesi->id}/state")->assertStatus(403);
    }
}
