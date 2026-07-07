<?php

namespace Tests\Unit\Services\Game;

use App\Models\GamePlayer;
use App\Models\PapanPermainan;
use App\Services\Game\WinConditionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WinConditionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_at_final_tile_has_won(): void
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        $player = GamePlayer::factory()->create(['posisi_pion' => 50]);

        $this->assertTrue((new WinConditionService())->hasWon($player, $papan));
    }

    public function test_player_not_at_final_tile_has_not_won(): void
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        $player = GamePlayer::factory()->create(['posisi_pion' => 49]);

        $this->assertFalse((new WinConditionService())->hasWon($player, $papan));
    }
}
