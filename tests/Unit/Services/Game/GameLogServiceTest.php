<?php

namespace Tests\Unit\Services\Game;

use App\Enums\GameLogEventType;
use App\Models\GameSession;
use App\Models\User;
use App\Services\Game\GameLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameLogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_persists_all_fields_correctly(): void
    {
        $session = GameSession::factory()->create();
        $user = User::factory()->create();

        $log = (new GameLogService())->log(
            $session,
            GameLogEventType::DiceRolled,
            $user->id,
            3,
            ['nilai' => 5]
        );

        $this->assertDatabaseHas('game_logs', [
            'id' => $log->id,
            'game_session_id' => $session->id,
            'user_id' => $user->id,
            'event_type' => 'dice_rolled',
            'turn_number' => 3,
        ]);
        $this->assertSame(5, $log->fresh()->payload['nilai']);
    }

    public function test_log_allows_null_user_for_robot_actions(): void
    {
        $session = GameSession::factory()->create();

        $log = (new GameLogService())->log($session, GameLogEventType::DiceRolled, null, 1, []);

        $this->assertNull($log->user_id);
    }
}
