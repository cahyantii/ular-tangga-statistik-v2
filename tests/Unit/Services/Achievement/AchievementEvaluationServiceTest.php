<?php

namespace Tests\Unit\Services\Achievement;

use App\Enums\AchievementCriteriaType;
use App\Enums\GameStatus;
use App\Models\Achievement;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\KategoriMateri;
use App\Models\LearningProgress;
use App\Models\User;
use App\Services\Achievement\AchievementEvaluationService;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementEvaluationServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeService(): AchievementEvaluationService
    {
        return new AchievementEvaluationService(new PlayerStatsService(), new LearningProgressService());
    }

    public function test_grants_an_achievement_whose_criteria_is_already_met(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::factory()->create([
            'syarat_type' => AchievementCriteriaType::TotalMenang,
            'syarat_value' => 2,
        ]);

        $sesi1 = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi1->id, 'user_id' => $user->id]);
        $sesi1->update(['winner_game_player_id' => GamePlayer::where('game_session_id', $sesi1->id)->first()->id]);

        $sesi2 = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi2->id, 'user_id' => $user->id]);
        $sesi2->update(['winner_game_player_id' => GamePlayer::where('game_session_id', $sesi2->id)->first()->id]);

        $granted = $this->makeService()->evaluate($user);

        $this->assertCount(1, $granted);
        $this->assertTrue($granted->contains('id', $achievement->id));
        $this->assertDatabaseHas('user_achievements', ['user_id' => $user->id, 'achievement_id' => $achievement->id]);
    }

    public function test_does_not_grant_an_achievement_whose_criteria_is_not_yet_met(): void
    {
        $user = User::factory()->create();
        Achievement::factory()->create([
            'syarat_type' => AchievementCriteriaType::TotalMenang,
            'syarat_value' => 5,
        ]);

        $granted = $this->makeService()->evaluate($user);

        $this->assertCount(0, $granted);
        $this->assertDatabaseCount('user_achievements', 0);
    }

    public function test_does_not_re_grant_an_achievement_already_earned(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::factory()->create([
            'syarat_type' => AchievementCriteriaType::TotalPermainan,
            'syarat_value' => 1,
        ]);

        $sesi = GameSession::factory()->finished()->create();
        GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'user_id' => $user->id]);

        $service = $this->makeService();
        $first = $service->evaluate($user);
        $second = $service->evaluate($user);

        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
        $this->assertDatabaseCount('user_achievements', 1);
    }

    public function test_evaluates_accuracy_criteria_from_learning_progress(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::factory()->create([
            'syarat_type' => AchievementCriteriaType::AkurasiKeseluruhan,
            'syarat_value' => 80,
        ]);

        $kategori = KategoriMateri::factory()->create();
        LearningProgress::create([
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 10,
            'total_benar' => 9,
            'total_salah' => 1,
            'accuracy' => 90,
        ]);

        $granted = $this->makeService()->evaluate($user);

        $this->assertCount(1, $granted);
        $this->assertTrue($granted->contains('id', $achievement->id));
    }

    public function test_ignores_inactive_achievements(): void
    {
        $user = User::factory()->create();
        Achievement::factory()->create([
            'syarat_type' => AchievementCriteriaType::TotalPermainan,
            'syarat_value' => 0,
            'is_active' => false,
        ]);

        $granted = $this->makeService()->evaluate($user);

        $this->assertCount(0, $granted);
    }
}
