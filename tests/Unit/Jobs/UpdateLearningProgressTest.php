<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateLearningProgress;
use App\Models\KategoriMateri;
use App\Models\LearningProgress;
use App\Models\User;
use App\Services\Progress\LearningProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UpdateLearningProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_new_row_on_the_first_answer(): void
    {
        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create();

        (new UpdateLearningProgress($user, $kategori->id, true))->handle(new LearningProgressService());

        $this->assertDatabaseHas('learning_progress', [
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 1,
            'total_benar' => 1,
            'total_salah' => 0,
        ]);
        $this->assertSame(100.0, (float) LearningProgress::first()->accuracy);
        $this->assertNotNull(LearningProgress::first()->last_played_at);
    }

    public function test_increments_an_existing_row_and_recomputes_accuracy(): void
    {
        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create();

        LearningProgress::create([
            'user_id' => $user->id,
            'kategori_id' => $kategori->id,
            'total_dijawab' => 3,
            'total_benar' => 2,
            'total_salah' => 1,
            'accuracy' => 66.67,
        ]);

        (new UpdateLearningProgress($user, $kategori->id, false))->handle(new LearningProgressService());

        $progress = LearningProgress::first();
        $this->assertSame(4, $progress->total_dijawab);
        $this->assertSame(2, $progress->total_benar);
        $this->assertSame(2, $progress->total_salah);
        $this->assertSame(50.0, (float) $progress->accuracy);
    }

    public function test_forgets_the_player_progress_cache(): void
    {
        $user = User::factory()->create();
        $kategori = KategoriMateri::factory()->create();
        $service = new LearningProgressService();

        Cache::put($service->cacheKey($user), ['stale' => true], now()->addMinutes(5));

        (new UpdateLearningProgress($user, $kategori->id, true))->handle($service);

        $this->assertFalse(Cache::has($service->cacheKey($user)));
    }

    public function test_tracks_separate_rows_per_category(): void
    {
        $user = User::factory()->create();
        $kategoriA = KategoriMateri::factory()->create();
        $kategoriB = KategoriMateri::factory()->create();
        $service = new LearningProgressService();

        (new UpdateLearningProgress($user, $kategoriA->id, true))->handle($service);
        (new UpdateLearningProgress($user, $kategoriB->id, false))->handle($service);

        $this->assertSame(2, LearningProgress::count());
        $this->assertDatabaseHas('learning_progress', ['kategori_id' => $kategoriA->id, 'total_benar' => 1]);
        $this->assertDatabaseHas('learning_progress', ['kategori_id' => $kategoriB->id, 'total_salah' => 1]);
    }
}
