<?php

namespace App\Services\Achievement;

use App\Enums\AchievementCriteriaType;
use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Evaluasi kriteria achievement seorang pemain.
 *
 * `currentValue()` adalah SATU-SATUNYA pemetaan syarat_type -> nilai statistik
 * pemain saat ini (Tahap 15/20, keputusan final) — dipakai bersama oleh teaser
 * "achievement terdekat" di Player Dashboard (tahap ini) dan logika pemberian
 * achievement otomatis (`evaluate()`, ditambahkan pada Tahap Achievement).
 *
 * Cache key `player.stats.achievement.{userId}` HARUS di-forget() di dalam Job
 * EvaluateAchievements pada tahap Achievement, setelah job tersebut commit.
 */
class AchievementEvaluationService
{
    private const TTL_MINUTES = 5;

    public function __construct(
        private readonly PlayerStatsService $playerStats,
        private readonly LearningProgressService $learningProgress,
    ) {
    }

    public function cacheKey(User $user): string
    {
        return "player.stats.achievement.{$user->id}";
    }

    public function currentValue(User $user, AchievementCriteriaType $type): int
    {
        return match ($type) {
            AchievementCriteriaType::TotalMenang => $this->playerStats->sessionSummary($user)['total_menang'],
            AchievementCriteriaType::TotalPermainan => $this->playerStats->sessionSummary($user)['total_main'],
            AchievementCriteriaType::AkurasiKeseluruhan => (int) round(
                $this->learningProgress->overallAggregate($user)['akurasi_keseluruhan']
            ),
        };
    }

    /**
     * Achievement belum diraih yang paling dekat tercapai (teaser dashboard).
     * Null jika tidak ada achievement aktif yang belum diraih.
     */
    public function nearestUpcoming(User $user): ?array
    {
        return Cache::remember(
            $this->cacheKey($user),
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($user) {
                $earnedIds = $user->userAchievements()->pluck('achievement_id');

                $kandidat = Achievement::query()
                    ->active()
                    ->whereNotIn('id', $earnedIds)
                    ->orderBy('urutan')
                    ->get()
                    ->map(function (Achievement $achievement) use ($user) {
                        $current = $this->currentValue($user, $achievement->syarat_type);

                        return [
                            'achievement' => $achievement,
                            'current' => $current,
                            'target' => $achievement->syarat_value,
                            'gap' => max($achievement->syarat_value - $current, 0),
                        ];
                    })
                    ->filter(fn (array $item) => $item['gap'] > 0)
                    ->sortBy('gap');

                return $kandidat->first();
            }
        );
    }

    /**
     * Beri achievement yang syaratnya baru saja terpenuhi (Tahap 13a, keputusan
     * final: memakai ULANG currentValue() yang sama, bukan menduplikasi
     * perhitungan). Dipanggil dari Job EvaluateAchievements setiap sesi selesai.
     *
     * @return Collection<int, Achievement> achievement yang baru diraih pada pemanggilan ini
     */
    public function evaluate(User $user): Collection
    {
        $earnedIds = $user->userAchievements()->pluck('achievement_id');

        $newlyGranted = Achievement::query()
            ->active()
            ->whereNotIn('id', $earnedIds)
            ->get()
            ->filter(fn (Achievement $achievement) => $this->currentValue($user, $achievement->syarat_type) >= $achievement->syarat_value);

        if ($newlyGranted->isEmpty()) {
            return $newlyGranted->values();
        }

        foreach ($newlyGranted as $achievement) {
            UserAchievement::create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'earned_at' => now(),
            ]);
        }

        Cache::forget($this->cacheKey($user));

        return $newlyGranted->values();
    }
}
