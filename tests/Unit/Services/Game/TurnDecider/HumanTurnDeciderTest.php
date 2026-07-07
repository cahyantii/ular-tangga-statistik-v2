<?php

namespace Tests\Unit\Services\Game\TurnDecider;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Soal;
use App\Services\Game\TurnDecider\HumanTurnDecider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class HumanTurnDeciderTest extends TestCase
{
    use RefreshDatabase;

    public function test_never_auto_plays(): void
    {
        $decider = new HumanTurnDecider();
        $human = GamePlayer::factory()->make(['is_robot' => false]);

        $this->assertFalse($decider->shouldAutoPlay($human));
    }

    public function test_deciding_an_answer_is_not_supported(): void
    {
        $decider = new HumanTurnDecider();
        $human = GamePlayer::factory()->make(['is_robot' => false]);
        $session = GameSession::factory()->make();
        $soal = Soal::factory()->make();

        $this->expectException(LogicException::class);

        $decider->decideAnswer($session, $human, $soal);
    }
}
