<?php

namespace Tests\Unit\Services\Game;

use App\Models\GameQuestionUsed;
use App\Models\GameSession;
use App\Models\GameSetting;
use App\Models\KategoriMateri;
use App\Models\Petak;
use App\Models\Soal;
use App\Repositories\Game\GameSettingsRepository;
use App\Services\Game\QuestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): QuestionService
    {
        GameSetting::factory()->create(['key' => 'question_timer_seconds', 'value' => '30']);

        return new QuestionService(new GameSettingsRepository());
    }

    public function test_selects_a_question_and_marks_it_used(): void
    {
        $kategori = KategoriMateri::factory()->create();
        Soal::factory()->count(3)->create(['kategori_id' => $kategori->id]);
        $session = GameSession::factory()->create();
        $petak = Petak::factory()->create(['jenis_petak' => 'tangga', 'kategori_id' => $kategori->id]);

        $soal = $this->makeService()->selectQuestion($session, $petak);

        $this->assertNotNull($soal);
        $this->assertDatabaseHas('game_questions_used', ['game_session_id' => $session->id, 'soal_id' => $soal->id]);
        $this->assertSame($soal->id, $session->fresh()->active_question_id);
        $this->assertNotNull($session->fresh()->active_question_expires_at);
    }

    public function test_never_repeats_a_question_within_the_same_session(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soalList = Soal::factory()->count(3)->create(['kategori_id' => $kategori->id]);
        $session = GameSession::factory()->create();
        $petak = Petak::factory()->create(['jenis_petak' => 'tangga', 'kategori_id' => $kategori->id]);

        $service = $this->makeService();
        $seenIds = [];

        foreach (range(1, 3) as $turn) {
            $session->total_turn = $turn;
            $soal = $service->selectQuestion($session, $petak);
            $this->assertNotContains($soal->id, $seenIds);
            $seenIds[] = $soal->id;
        }

        $this->assertCount(3, array_unique($seenIds));
    }

    public function test_returns_null_when_question_pool_is_exhausted(): void
    {
        $kategori = KategoriMateri::factory()->create();
        $soal = Soal::factory()->create(['kategori_id' => $kategori->id]);
        $session = GameSession::factory()->create();
        $petak = Petak::factory()->create(['jenis_petak' => 'tangga', 'kategori_id' => $kategori->id]);

        GameQuestionUsed::create([
            'game_session_id' => $session->id,
            'soal_id' => $soal->id,
            'used_at' => now(),
        ]);

        $result = $this->makeService()->selectQuestion($session, $petak);

        $this->assertNull($result);
    }

    public function test_returns_null_when_no_active_soal_exists_at_all(): void
    {
        // kategori_id null TIDAK berarti "tanpa soal" - itu justru artinya
        // "acak dari semua kategori aktif" (lihat selectQuestion(): filter
        // kategori_id cuma diterapkan kalau petak->kategori_id !== null).
        // Null di sini cuma sengaja tidak ada satupun baris Soal aktif di DB
        // sama sekali, supaya pool-nya benar-benar kosong.
        $session = GameSession::factory()->create();
        $petak = Petak::factory()->create(['jenis_petak' => 'biasa', 'kategori_id' => null]);

        $result = $this->makeService()->selectQuestion($session, $petak);

        $this->assertNull($result);
    }
}
