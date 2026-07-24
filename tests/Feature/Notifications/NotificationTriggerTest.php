<?php

namespace Tests\Feature\Notifications;

use App\Enums\AchievementCriteriaType;
use App\Enums\GameStatus;
use App\Enums\UserRole;
use App\Models\Achievement;
use App\Models\Feedback;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\GameSetting;
use App\Models\PapanPermainan;
use App\Models\Petak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTriggerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        GameSetting::factory()->create(['key' => 'correct_answer_point', 'value' => '10']);
        GameSetting::factory()->create(['key' => 'wrong_answer_penalty', 'value' => '5']);
        GameSetting::factory()->create(['key' => 'bonus_point', 'value' => '20']);
        GameSetting::factory()->create(['key' => 'tile_penalty_point', 'value' => '5']);
        GameSetting::factory()->create(['key' => 'win_point', 'value' => '100']);
        GameSetting::factory()->create(['key' => 'question_timer_seconds', 'value' => '30']);
    }

    private function diceValueFor(string $seed, int $turn): int
    {
        return (crc32("{$seed}:{$turn}") % 6) + 1;
    }

    private function makeFinishBoard(): PapanPermainan
    {
        $papan = PapanPermainan::factory()->create(['jumlah_petak' => 50, 'jumlah_kolom' => 10]);

        for ($i = 1; $i <= 50; $i++) {
            Petak::factory()->create([
                'papan_id' => $papan->id,
                'posisi' => $i,
                'jenis_petak' => $i === 1 ? 'start' : ($i === 50 ? 'finish' : 'biasa'),
            ]);
        }

        return $papan;
    }

    public function test_finishing_a_game_notifies_the_winner_and_all_admins(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $nilai = $this->diceValueFor('seed-notif-finish', 0);
        $papan = $this->makeFinishBoard();
        $sesi = GameSession::factory()->create([
            'papan_id' => $papan->id,
            'status' => GameStatus::Playing,
            'random_seed' => 'seed-notif-finish',
            'total_turn' => 0,
        ]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 50 - $nilai]);
        GamePlayer::factory()->robot()->create(['game_session_id' => $sesi->id]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll")->assertOk();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $p1->user_id,
            'notifiable_type' => User::class,
        ]);

        $playerNotification = $p1->user->notifications()->first();
        $this->assertSame('game', $playerNotification->data['category']);
        $this->assertSame('Anda Menang!', $playerNotification->data['title']);

        $adminNotification = $admin->notifications()->where('data->category', 'game')->first();
        $this->assertNotNull($adminNotification, 'Admin harus menerima notifikasi permainan selesai.');
    }

    public function test_unlocking_an_achievement_notifies_the_player_and_admins(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        Achievement::factory()->create([
            'kode' => 'PERTAMA_KALI',
            'nama' => 'Pertama Kali',
            'syarat_type' => AchievementCriteriaType::TotalPermainan,
            'syarat_value' => 1,
        ]);

        $nilai = $this->diceValueFor('seed-notif-achievement', 0);
        $papan = $this->makeFinishBoard();
        $sesi = GameSession::factory()->create([
            'papan_id' => $papan->id,
            'status' => GameStatus::Playing,
            'random_seed' => 'seed-notif-achievement',
            'total_turn' => 0,
        ]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1, 'posisi_pion' => 50 - $nilai]);
        GamePlayer::factory()->robot()->create(['game_session_id' => $sesi->id]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $this->actingAs($p1->user)->postJson("/main/{$sesi->id}/roll")->assertOk();

        $achievementNotification = $p1->user->notifications()->where('data->category', 'achievement')->first();
        $this->assertNotNull($achievementNotification);
        $this->assertSame('PERTAMA_KALI', $achievementNotification->data['achievement_id']);

        $adminAchievementNotification = $admin->notifications()->where('data->category', 'achievement')->first();
        $this->assertNotNull($adminAchievementNotification);
    }

    public function test_registering_notifies_all_admins(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->post('/register', [
            'name' => 'Pemain Baru',
            'email' => 'pemain-baru@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => true,
        ]);

        $notification = $admin->notifications()->where('data->category', 'user')->first();
        $this->assertNotNull($notification, 'Admin harus menerima notifikasi user baru mendaftar.');
        $this->assertStringContainsString('Pemain Baru', $notification->data['message']);
    }

    public function test_submitting_feedback_creates_a_record_and_notifies_admins(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $player = User::factory()->create(['role' => UserRole::Player]);

        $this->actingAs($player)->post('/feedback', [
            'type' => 'bug',
            'subject' => 'Papan tidak muncul',
            'message' => 'Papan permainan blank setelah login.',
        ])->assertRedirect();

        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $player->id,
            'subject' => 'Papan tidak muncul',
            'status' => 'baru',
        ]);

        $notification = $admin->notifications()->where('data->category', 'feedback')->first();
        $this->assertNotNull($notification);
    }

    public function test_player_never_sees_admin_notifications_and_vice_versa(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $player = User::factory()->create(['role' => UserRole::Player]);

        $feedback = Feedback::factory()->create(['user_id' => $player->id]);
        app(\App\Services\Notification\NotificationService::class)->sendToAdmins(
            app(\App\Services\Notification\NotificationService::class)->payloadFeedbackReceived($feedback)
        );

        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(0, $player->notifications()->count());
    }
}
