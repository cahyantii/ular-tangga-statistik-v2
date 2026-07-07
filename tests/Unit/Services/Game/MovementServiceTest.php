<?php

namespace Tests\Unit\Services\Game;

use App\Enums\ConnectorType;
use App\Models\GamePlayer;
use App\Models\PapanKonektor;
use App\Models\PapanPermainan;
use App\Services\Game\MovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_move_advances_pawn_position(): void
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        $player = GamePlayer::factory()->create(['posisi_pion' => 10]);

        $result = (new MovementService())->move($player, 4, $papan);

        $this->assertFalse($result['blocked']);
        $this->assertSame(10, $result['posisi_sebelum']);
        $this->assertSame(14, $result['posisi_sesudah']);
        $this->assertSame(14, $player->fresh()->posisi_pion);
    }

    public function test_move_exceeding_remaining_steps_to_finish_is_blocked(): void
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        $player = GamePlayer::factory()->create(['posisi_pion' => 48]);

        $result = (new MovementService())->move($player, 5, $papan);

        $this->assertTrue($result['blocked']);
        $this->assertSame(48, $result['posisi_sesudah']);
        $this->assertSame(48, $player->fresh()->posisi_pion);
    }

    public function test_move_landing_exactly_on_finish_is_allowed(): void
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        $player = GamePlayer::factory()->create(['posisi_pion' => 48]);

        $result = (new MovementService())->move($player, 2, $papan);

        $this->assertFalse($result['blocked']);
        $this->assertSame(50, $result['posisi_sesudah']);
    }

    public function test_landing_on_connector_start_jumps_to_connector_end(): void
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50]);
        $konektor = PapanKonektor::factory()->create([
            'papan_id' => $papan->id,
            'jenis' => ConnectorType::Tangga,
            'posisi_awal' => 14,
            'posisi_akhir' => 30,
        ]);
        $player = GamePlayer::factory()->create(['posisi_pion' => 10]);

        $result = (new MovementService())->move($player, 4, $papan);

        $this->assertFalse($result['blocked']);
        $this->assertSame(30, $result['posisi_sesudah']);
        $this->assertTrue($konektor->is($result['konektor']));
        $this->assertSame(30, $player->fresh()->posisi_pion);
    }
}
