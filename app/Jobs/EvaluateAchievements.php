<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Achievement\AchievementEvaluationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

/**
 * Achievement (Tahap 5/15/20, keputusan final: "Queue untuk proses non-inti").
 * Di-dispatch oleh GameSessionService::finishSession() untuk setiap partisipan
 * MANUSIA setiap kali sebuah sesi selesai (menang maupun kalah) — total_permainan
 * harus tetap bertambah walau kalah, jadi evaluasi tidak boleh hanya jalan
 * untuk pemenang.
 */
class EvaluateAchievements implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly User $user)
    {
    }

    public function handle(AchievementEvaluationService $achievementEvaluation): void
    {
        $achievementEvaluation->evaluate($this->user);
    }
}
