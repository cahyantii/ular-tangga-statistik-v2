<?php

namespace Tests\Feature\Player;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AchievementPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_earned_and_unearned_achievements(): void
    {
        $user = User::factory()->create();
        $earned = Achievement::factory()->create(['nama' => 'Juara Bertahan']);
        $notEarned = Achievement::factory()->create(['nama' => 'Master Statistik']);

        UserAchievement::create([
            'user_id' => $user->id,
            'achievement_id' => $earned->id,
            'earned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('player.achievements'));

        $response->assertOk();
        $response->assertSee('Juara Bertahan');
        $response->assertSee('Master Statistik');
        $response->assertSee('Diraih');
        // Dipecah jadi dua assertSee(), bukan satu string utuh "1 dari 2
        // achievement" - markup aslinya (achievement-summary-card.blade.php)
        // membungkus "1 dari 2" dalam <span> terpisah dari kata "achievement"
        // sesudahnya, jadi satu assertSee() gabungan gagal mendeteksi teks
        // yang justru tampil BENAR ke pengguna (cuma terpisah tag, bukan
        // rusak). Dua assertSee() ini tetap memverifikasi kedua bagian teks
        // benar-benar ada di halaman, tanpa terikat pada struktur tag persis.
        $response->assertSee('1 dari 2');
        $response->assertSee('achievement telah diraih');
    }

    public function test_inactive_achievements_are_not_shown(): void
    {
        $user = User::factory()->create();
        Achievement::factory()->create(['nama' => 'Achievement Nonaktif', 'is_active' => false]);

        $response = $this->actingAs($user)->get(route('player.achievements'));

        $response->assertOk();
        $response->assertDontSee('Achievement Nonaktif');
    }
}
