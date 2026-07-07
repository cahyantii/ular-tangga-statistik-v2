<?php

namespace Tests\Unit\Services\Game;

use App\Models\GameSession;
use App\Services\Game\DiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_roll_is_always_between_1_and_6(): void
    {
        $service = new DiceService();
        $session = GameSession::factory()->create(['random_seed' => 'seed-abc']);

        for ($turn = 0; $turn < 50; $turn++) {
            $session->total_turn = $turn;
            $nilai = $service->roll($session);
            $this->assertGreaterThanOrEqual(1, $nilai);
            $this->assertLessThanOrEqual(6, $nilai);
        }
    }

    public function test_roll_is_deterministic_for_the_same_seed_and_turn(): void
    {
        $service = new DiceService();
        $sessionA = GameSession::factory()->create(['random_seed' => 'seed-xyz', 'total_turn' => 3]);
        $sessionB = GameSession::factory()->create(['random_seed' => 'seed-xyz', 'total_turn' => 3]);

        $this->assertSame($service->roll($sessionA), $service->roll($sessionB));
    }

    public function test_roll_matches_documented_formula(): void
    {
        $service = new DiceService();
        $session = GameSession::factory()->create(['random_seed' => 'seed-formula', 'total_turn' => 7]);

        $expected = (crc32('seed-formula:7') % 6) + 1;

        $this->assertSame($expected, $service->roll($session));
    }
}
