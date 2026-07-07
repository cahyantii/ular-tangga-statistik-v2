<?php

namespace Tests\Feature\Admin;

use App\Enums\GameLogEventType;
use App\Enums\GameMode;
use App\Models\GameLog;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\KategoriMateri;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_empty_states_when_no_game_data_exists(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('Total Pemain');
        $response->assertSee('Belum ada permainan yang selesai.');
    }

    public function test_dashboard_shows_aggregated_statistics_from_finished_sessions(): void
    {
        $admin = User::factory()->admin()->create();
        $kategori = KategoriMateri::factory()->create(['nama' => 'Statistika Dasar']);
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id, 'pertanyaan' => 'Apa itu mean?']);

        $pemainJuara = User::factory()->create();
        $sesi = GameSession::factory()->finished()->create(['mode' => GameMode::VsRobot]);

        $pemenang = GamePlayer::factory()->create([
            'game_session_id' => $sesi->id,
            'user_id' => $pemainJuara->id,
            'skor' => 150,
        ]);
        $lawan = GamePlayer::factory()->robot()->create([
            'game_session_id' => $sesi->id,
            'skor' => 40,
        ]);

        $sesi->update(['winner_game_player_id' => $pemenang->id]);

        $this->assertTrue($lawan->is_robot);

        GameLog::factory()->create([
            'game_session_id' => $sesi->id,
            'user_id' => $pemainJuara->id,
            'event_type' => GameLogEventType::AnswerSubmitted,
            'payload' => [
                'soal_id' => $soal->id,
                'kategori_id' => $kategori->id,
                'is_correct' => true,
            ],
        ]);
        GameLog::factory()->create([
            'game_session_id' => $sesi->id,
            'user_id' => $pemainJuara->id,
            'event_type' => GameLogEventType::AnswerSubmitted,
            'payload' => [
                'soal_id' => $soal->id,
                'kategori_id' => $kategori->id,
                'is_correct' => false,
            ],
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee($pemainJuara->name);
        $response->assertSeeText('150 poin');
        $response->assertSeeText('rata-rata akurasi 50%'); // 1 benar dari 2 jawaban tercatat
    }
}
