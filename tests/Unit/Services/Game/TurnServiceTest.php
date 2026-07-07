<?php

namespace Tests\Unit\Services\Game;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Services\Game\TurnService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurnServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_advance_moves_to_the_next_player_in_turn_order_and_wraps_around(): void
    {
        $session = GameSession::factory()->create(['total_turn' => 0]);
        $playerA = GamePlayer::factory()->create(['game_session_id' => $session->id, 'turn_order' => 1]);
        $playerB = GamePlayer::factory()->create(['game_session_id' => $session->id, 'turn_order' => 2]);
        $session->update(['current_turn_game_player_id' => $playerA->id]);

        $service = new TurnService();

        $next = $service->advance($session->fresh());
        $this->assertTrue($next->is($playerB));
        $this->assertSame(1, $session->fresh()->total_turn);

        $next = $service->advance($session->fresh());
        $this->assertTrue($next->is($playerA));
        $this->assertSame(2, $session->fresh()->total_turn);
    }
}
