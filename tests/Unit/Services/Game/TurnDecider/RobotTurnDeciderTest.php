<?php

namespace Tests\Unit\Services\Game\TurnDecider;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Soal;
use App\Services\Game\TurnDecider\RobotTurnDecider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobotTurnDeciderTest extends TestCase
{
    use RefreshDatabase;

    public function test_should_auto_play_only_for_robot_players(): void
    {
        $decider = new RobotTurnDecider();
        $robot = GamePlayer::factory()->robot()->make();
        $human = GamePlayer::factory()->make(['is_robot' => false]);

        $this->assertTrue($decider->shouldAutoPlay($robot));
        $this->assertFalse($decider->shouldAutoPlay($human));
    }

    public function test_decide_answer_returns_the_correct_key_when_deterministic_hash_is_below_the_rate(): void
    {
        $decider = new RobotTurnDecider();
        $soal = Soal::factory()->create(['kunci_jawaban' => 'B']);

        [$seed, $turn] = $this->findSeedTurnForOutcome(true);
        $session = GameSession::factory()->make(['random_seed' => $seed, 'total_turn' => $turn]);
        $robot = GamePlayer::factory()->robot()->make();

        $jawaban = $decider->decideAnswer($session, $robot, $soal);

        $this->assertSame('B', $jawaban);
    }

    public function test_decide_answer_returns_a_wrong_key_when_deterministic_hash_is_at_or_above_the_rate(): void
    {
        $decider = new RobotTurnDecider();
        $soal = Soal::factory()->create(['kunci_jawaban' => 'B']);

        [$seed, $turn] = $this->findSeedTurnForOutcome(false);
        $session = GameSession::factory()->make(['random_seed' => $seed, 'total_turn' => $turn]);
        $robot = GamePlayer::factory()->robot()->make();

        $jawaban = $decider->decideAnswer($session, $robot, $soal);

        $this->assertNotNull($jawaban);
        $this->assertNotSame('B', strtoupper($jawaban));
    }

    public function test_decide_answer_is_deterministic_for_the_same_seed_and_turn(): void
    {
        $decider = new RobotTurnDecider();
        $soal = Soal::factory()->create(['kunci_jawaban' => 'C']);
        $session = GameSession::factory()->make(['random_seed' => 'fixed-seed', 'total_turn' => 5]);
        $robot = GamePlayer::factory()->robot()->make();

        $first = $decider->decideAnswer($session, $robot, $soal);
        $second = $decider->decideAnswer($session, $robot, $soal);

        $this->assertSame($first, $second);
    }

    public function test_correctness_rate_across_many_turns_is_approximately_70_percent(): void
    {
        $decider = new RobotTurnDecider();
        $soal = Soal::factory()->create(['kunci_jawaban' => 'A']);
        $robot = GamePlayer::factory()->robot()->make();

        $benar = 0;
        $total = 500;

        for ($turn = 0; $turn < $total; $turn++) {
            $session = GameSession::factory()->make(['random_seed' => 'rate-check-seed', 'total_turn' => $turn]);
            if (strtoupper((string) $decider->decideAnswer($session, $robot, $soal)) === 'A') {
                $benar++;
            }
        }

        $rate = $benar / $total * 100;

        $this->assertEqualsWithDelta(RobotTurnDecider::CORRECT_ANSWER_RATE, $rate, 8.0);
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function findSeedTurnForOutcome(bool $wantCorrect): array
    {
        for ($turn = 0; $turn < 1000; $turn++) {
            $hash = crc32('probe-seed:robot_jawab:'.$turn);
            $isCorrect = ($hash % 100) < RobotTurnDecider::CORRECT_ANSWER_RATE;

            if ($isCorrect === $wantCorrect) {
                return ['probe-seed', $turn];
            }
        }

        $this->fail('Tidak menemukan kombinasi seed/turn yang cocok untuk outcome yang diinginkan.');
    }
}
