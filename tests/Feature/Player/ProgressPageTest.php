<?php

namespace Tests\Feature\Player;

use App\Models\KategoriMateri;
use App\Models\LearningProgress;
use App\Models\Soal;
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
        // LearningProgressService::perKategori() menghitung total_soal dari
        // JUMLAH BARIS Soal aktif kategori itu sungguhan (bukan dari kolom
        // manapun di LearningProgress) - tanpa baris Soal ini, total_soal
        // selalu 0 dan subtitle-nya jadi "8 dari 0 soal dijawab" (status
        // 'sedang_belajar', bukan 'selesai'), bukan "8 soal dijawab" seperti
        // yang test ini awalnya asumsikan.
        Soal::factory()->count(10)->create(['kategori_id' => $kategori->id]);

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
        $response->assertSee('8 dari 10 soal dijawab', false);
    }

    public function test_categories_never_played_show_as_zero_progress(): void
    {
        $user = User::factory()->create();
        KategoriMateri::factory()->create(['nama' => 'Belum Pernah Dimainkan']);

        $response = $this->actingAs($user)->get(route('player.progress'));

        $response->assertOk();
        $response->assertSee('Belum Pernah Dimainkan');
        // Kategori yang BELUM PERNAH dimainkan sama sekali (tidak ada baris
        // LearningProgress) masuk status 'belum_dimulai' di
        // LearningProgressService::perKategori(), yang subtitle-nya literal
        // "Belum ada soal dijawab" (lihat progress-category-card.blade.php)
        // - BUKAN "0 soal dijawab" seperti yang test ini awalnya asumsikan.
        $response->assertSee('Belum ada soal dijawab');
    }
}
