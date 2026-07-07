<?php

namespace App\Jobs;

use App\Models\LearningProgress;
use App\Models\User;
use App\Services\Progress\LearningProgressService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Progress belajar (Tahap 5/15, keputusan final: "Queue untuk proses non-inti").
 * Di-dispatch oleh GameSessionService::executeAnswer() setiap kali pemain
 * MANUSIA menjawab soal (benar maupun salah/timeout) — robot tidak punya
 * user_id, jadi tidak pernah memicu job ini.
 */
class UpdateLearningProgress implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly int $kategoriId,
        public readonly bool $isCorrect,
    ) {
    }

    public function handle(LearningProgressService $learningProgress): void
    {
        DB::transaction(function () {
            $progress = LearningProgress::query()
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $this->user->id, 'kategori_id' => $this->kategoriId],
                    ['total_dijawab' => 0, 'total_benar' => 0, 'total_salah' => 0, 'accuracy' => 0]
                );

            $progress->total_dijawab += 1;

            if ($this->isCorrect) {
                $progress->total_benar += 1;
            } else {
                $progress->total_salah += 1;
            }

            $progress->accuracy = round($progress->total_benar / $progress->total_dijawab * 100, 2);
            $progress->last_played_at = now();
            $progress->save();
        });

        Cache::forget($learningProgress->cacheKey($this->user));
    }
}
