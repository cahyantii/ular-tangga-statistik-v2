<?php

namespace Tests\Unit\Services\Game;

use App\Enums\ScoreEventType;
use App\Models\GamePlayer;
use App\Models\GameSetting;
use App\Repositories\Game\GameSettingsRepository;
use App\Services\Game\ScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): ScoreService
    {
        GameSetting::factory()->create(['key' => 'correct_answer_point', 'value' => '10']);
        GameSetting::factory()->create(['key' => 'wrong_answer_penalty', 'value' => '5']);
        GameSetting::factory()->create(['key' => 'bonus_point', 'value' => '20']);
        GameSetting::factory()->create(['key' => 'tile_penalty_point', 'value' => '7']);
        GameSetting::factory()->create(['key' => 'win_point', 'value' => '100']);

        return new ScoreService(new GameSettingsRepository());
    }

    public function test_correct_answer_adds_configured_points(): void
    {
        $player = GamePlayer::factory()->create(['skor' => 0]);
        $delta = $this->makeService()->apply($player, ScoreEventType::CorrectAnswer);

        $this->assertSame(10, $delta);
        $this->assertSame(10, $player->fresh()->skor);
    }

    public function test_wrong_answer_subtracts_configured_penalty(): void
    {
        $player = GamePlayer::factory()->create(['skor' => 20]);
        $delta = $this->makeService()->apply($player, ScoreEventType::WrongAnswer);

        $this->assertSame(-5, $delta);
        $this->assertSame(15, $player->fresh()->skor);
    }

    public function test_tile_penalty_is_a_separate_configurable_value_from_wrong_answer(): void
    {
        $player = GamePlayer::factory()->create(['skor' => 20]);
        $delta = $this->makeService()->apply($player, ScoreEventType::TilePenalty);

        $this->assertSame(-7, $delta);
        $this->assertSame(13, $player->fresh()->skor);
    }

    public function test_win_adds_configured_win_point(): void
    {
        $player = GamePlayer::factory()->create(['skor' => 50]);
        $delta = $this->makeService()->apply($player, ScoreEventType::Win);

        $this->assertSame(100, $delta);
        $this->assertSame(150, $player->fresh()->skor);
    }
}
