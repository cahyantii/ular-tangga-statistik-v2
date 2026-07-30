<?php

namespace App\Services\Achievement;

use App\Enums\AchievementCriteriaType;
use App\Enums\GameLogEventType;
use App\Enums\GameStatus;
use App\Models\Achievement;
use App\Models\GameLog;
use App\Models\GameSession;
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
 * "achievement terdekat" di Player Dashboard dan logika pemberian achievement
 * otomatis (`evaluate()`).
 *
 * `progressSnapshot()` (Tahap Achievement Page redesign) adalah SATU-SATUNYA
 * query progress-semua-achievement, dipakai bersama oleh halaman Achievement
 * (seluruh kartu) dan `nearestUpcoming()` (teaser dashboard) — supaya tidak ada
 * dua cara berbeda menghitung hal yang sama.
 *
 * Cache key `player.stats.achievement.{userId}` HARUS di-forget() di dalam Job
 * EvaluateAchievements setiap sesi selesai (sudah ditangani di evaluate()).
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
            AchievementCriteriaType::SelisihKemenanganTerbesar => $this->selisihKemenanganTerbesar($user),
            AchievementCriteriaType::TotalAngkaEnam => $this->totalAngkaEnam($user),
        };
    }

    /**
     * Selisih posisi_pion terjauh yang pernah dicapai pemain saat menang
     * (posisi pemenang selalu di kotak Finish, lihat WinConditionService —
     * exact-landing rule — sehingga selisih = jumlah_petak - posisi lawan
     * terjauh dari Finish).
     */
    private function selisihKemenanganTerbesar(User $user): int
    {
        return GameSession::query()
            ->where('status', GameStatus::Finished->value)
            ->whereNotNull('winner_game_player_id')
            ->whereHas('players', fn ($query) => $query->where('user_id', $user->id))
            ->with('players')
            ->get()
            ->map(function (GameSession $session) use ($user) {
                $winner = $session->players->firstWhere('id', $session->winner_game_player_id);

                if (! $winner || $winner->user_id !== $user->id) {
                    return 0;
                }

                $posisiLawanTerjauh = $session->players
                    ->where('id', '!=', $winner->id)
                    ->min('posisi_pion') ?? 0;

                return max($winner->posisi_pion - $posisiLawanTerjauh, 0);
            })
            ->max() ?? 0;
    }

    /**
     * Total lemparan dadu bernilai 6 sepanjang riwayat permainan pemain,
     * dihitung dari Game Log (payload `nilai` ditulis oleh
     * GameSessionService::rollDice(), lihat GameLogEventType::DiceRolled).
     */
    private function totalAngkaEnam(User $user): int
    {
        return GameLog::query()
            ->where('user_id', $user->id)
            ->where('event_type', GameLogEventType::DiceRolled->value)
            ->get()
            ->filter(fn (GameLog $log) => (int) ($log->payload['nilai'] ?? 0) === 6)
            ->count();
    }

    /**
     * Progress seluruh achievement aktif untuk satu pemain: status raih,
     * nilai saat ini, target, persentase, dan sisa (gap). Dipakai langsung
     * oleh halaman Achievement (semua kartu) dan oleh nearestUpcoming() di
     * bawah (teaser dashboard) — satu query, dua pemakai.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function progressSnapshot(User $user): Collection
    {
        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $result */
        $result = Cache::remember(
            $this->cacheKey($user),
            now()->addMinutes(self::TTL_MINUTES),
            /** @phpstan-ignore-next-line */
            function () use ($user) {
                $earned = $user->userAchievements()->get()->keyBy('achievement_id');

                /** @phpstan-ignore-next-line */
                return Achievement::query()
                    ->active()
                    ->orderBy('urutan')
                    ->get()
                    ->map(function (Achievement $achievement) use ($user, $earned) {
                        $current = $this->currentValue($user, $achievement->syarat_type);
                        $target = $achievement->syarat_value;
                        /** @var \App\Models\UserAchievement|null $userAchievement */
                        $userAchievement = $earned->get($achievement->id);
                        $isEarned = $userAchievement !== null;

                        return [
                            'achievement' => $achievement,
                            'earned' => $isEarned,
                            'earned_at' => $userAchievement ? \Illuminate\Support\Carbon::parse($userAchievement->earned_at) : null,
                            'current' => $current,
                            'target' => $target,
                            'percent' => $target > 0 ? min(100, (int) round($current / $target * 100)) : 0,
                            'gap' => max($target - $current, 0),
                        ];
                    });
            }
        );

        return $result;
    }

    /**
     * Achievement belum diraih yang paling dekat tercapai (teaser dashboard).
     * Null jika tidak ada achievement aktif yang belum diraih.
     */
    public function nearestUpcoming(User $user): ?array
    {
        return $this->progressSnapshot($user)
            ->reject(fn (array $item) => $item['earned'])
            ->filter(fn (array $item) => $item['gap'] > 0)
            ->sortBy('gap')
            ->first();
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
