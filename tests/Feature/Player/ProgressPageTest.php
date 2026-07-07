<?php

namespace Tests\Feature\Player;

use App\Models\KategoriMateri;
use App\Models\LearningProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_overall_aggregate_and_per_category_breakdown(): void
    {
        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create(['nama' => 'Statistika Deskriptif']);

        LearningProgress::create([
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 8,
            'total_benar' => 6,
            'total_salah' => 2,
            'accuracy' => 75,
        ]);

        $response = $this->actingAs($user)->get(route('player.progress'));

        $response->assertOk();
        $response->assertSee('Statistika Deskriptif');
        $response->assertSee('75');
        $response->assertSee('8 soal dijawab', false);
    }

    public function test_categories_never_played_show_as_zero_progress(): void
    {
        $user = User::factory()->create();
        KategoriMateri::factory()->create(['nama' => 'Belum Pernah Dimainkan']);

        $response = $this->actingAs($user)->get(route('player.progress'));

        $response->assertOk();
        $response->assertSee('Belum Pernah Dimainkan');
        $response->assertSee('0 soal dijawab', false);
    }
}
