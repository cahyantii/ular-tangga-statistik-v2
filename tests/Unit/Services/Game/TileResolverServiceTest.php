<?php

namespace Tests\Unit\Services\Game;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\GameSetting;
use App\Models\Petak;
use App\Repositories\Game\GameSettingsRepository;
use App\Services\Game\ScoreService;
use App\Services\Game\TileResolverService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TileResolverServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): TileResolverService
    {
        GameSetting::factory()->create(['key' => 'bonus_point', 'value' => '20']);
        GameSetting::factory()->create(['key' => 'tile_penalty_point', 'value' => '7']);

        return new TileResolverService(new ScoreService(new GameSettingsRepository()));
    }

    public function test_biasa_tile_has_no_effect(): void
    {
        $session = GameSession::factory()->create();
        $player = GamePlayer::factory()->create(['skor' => 0]);
        $petak = Petak::factory()->create(['jenis_petak' => 'biasa']);

        $result = $this->makeService()->resolve($session, $player, $petak);

        $this->assertSame('none', $result['type']);
        $this->assertSame(0, $player->fresh()->skor);
    }

    public function test_bonus_tile_adds_bonus_point(): void
    {
        $session = GameSession::factory()->create();
        $player = GamePlayer::factory()->create(['skor' => 0]);
        $petak = Petak::factory()->create(['jenis_petak' => 'bonus']);

        $result = $this->makeService()->resolve($session, $player, $petak);

        $this->assertSame('bonus', $result['type']);
        $this->assertSame(20, $result['delta']);
        $this->assertSame(20, $player->fresh()->skor);
    }

    public function test_penalti_tile_subtracts_tile_penalty_point(): void
    {
        $session = GameSession::factory()->create();
        $player = GamePlayer::factory()->create(['skor' => 20]);
        $petak = Petak::factory()->create(['jenis_petak' => 'penalti']);

        $result = $this->makeService()->resolve($session, $player, $petak);

        $this->assertSame('penalti', $result['type']);
        $this->assertSame(-7, $result['delta']);
        $this->assertSame(13, $player->fresh()->skor);
    }

    public function test_soal_tile_returns_soal_type_without_score_side_effect(): void
    {
        $session = GameSession::factory()->create();
        $player = GamePlayer::factory()->create(['skor' => 0]);
        $petak = Petak::factory()->create(['jenis_petak' => 'soal']);

        $result = $this->makeService()->resolve($session, $player, $petak);

        $this->assertSame('soal', $result['type']);
        $this->assertSame(0, $player->fresh()->skor);
    }

    public function test_mystery_tile_effect_is_deterministic_for_same_seed_and_turn(): void
    {
        $service = $this->makeService();
        $petak = Petak::factory()->create(['jenis_petak' => 'mystery']);

        $sessionA = GameSession::factory()->create(['random_seed' => 'seed-1', 'total_turn' => 5]);
        $playerA = GamePlayer::factory()->create(['skor' => 0]);
        $resultA = $service->resolve($sessionA, $playerA, $petak);

        $sessionB = GameSession::factory()->create(['random_seed' => 'seed-1', 'total_turn' => 5]);
        $playerB = GamePlayer::factory()->create(['skor' => 0]);
        $resultB = $service->resolve($sessionB, $playerB, $petak);

        $this->assertSame($resultA['hasil'], $resultB['hasil']);
        $this->assertSame($resultA['delta'], $resultB['delta']);
        $this->assertContains($resultA['hasil'], ['bonus', 'penalti']);
    }
}
