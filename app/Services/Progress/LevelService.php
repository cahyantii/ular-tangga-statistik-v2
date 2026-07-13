<?php

namespace App\Services\Progress;

use App\Models\User;
use App\Services\Game\PlayerStatsService;
use Illuminate\Support\Facades\Cache;

/**
 * Level & XP pemain (kartu "Level Kamu" di Progress Belajar, badge di Profil
 * & Leaderboard).
 *
 * XP dihitung dari sinyal aktivitas nyata yang sudah ada: soal dijawab benar
 * (LearningProgressService), kemenangan permainan (PlayerStatsService), dan
 * reward_poin achievement yang sudah diraih (Achievement::reward_poin) —
 * tidak ada nilai yang disimpan/di-hardcode terpisah, semua dihitung ulang
 * dari data sumber yang sudah ada.
 *
 * `fromXp()` dipisah dari `snapshot()` (murni, tanpa query) supaya bisa
 * dipakai untuk menghitung badge banyak pemain sekaligus (LeaderboardService)
 * dari XP yang sudah dihitung batch, tanpa mengulang query per-user (N+1).
 */
class LevelService
{
    private const TTL_MINUTES = 5;

    public const XP_PER_LEVEL = 100;

    public const XP_PER_SOAL_BENAR = 10;

    public const XP_PER_KEMENANGAN = 25;

    public const TIER_NAMES = [
        'Pemula',
        'Pembelajar',
        'Rajin Belajar',
        'Ahli Statistik',
        'Master BPS',
        'Legenda Statistik',
    ];

    public const TIER_COLORS = [
        'slate',
        'blue',
        'green',
        'amber',
        'purple',
        'rose',
    ];

    public function __construct(
        private readonly PlayerStatsService $playerStats,
        private readonly LearningProgressService $learningProgress,
    ) {
    }

    public function cacheKey(User $user): string
    {
        return "player.stats.level.{$user->id}";
    }

    public function snapshot(User $user): array
    {
        return Cache::remember(
            $this->cacheKey($user),
            now()->addMinutes(self::TTL_MINUTES),
            fn () => self::fromXp($this->computeXp($user))
        );
    }

    public function computeXp(User $user): int
    {
        $stats = $this->playerStats->sessionSummary($user);
        $progress = $this->learningProgress->overallAggregate($user);
        $rewardPoin = (int) $user->achievements()->sum('reward_poin');

        return ($progress['total_benar'] * self::XP_PER_SOAL_BENAR)
            + ($stats['total_menang'] * self::XP_PER_KEMENANGAN)
            + $rewardPoin;
    }

    public static function fromXp(int $xp): array
    {
        $level = intdiv($xp, self::XP_PER_LEVEL) + 1;
        $xpIntoLevel = $xp % self::XP_PER_LEVEL;
        $tierIndex = min(intdiv($level - 1, 3), count(self::TIER_NAMES) - 1);

        return [
            'xp' => $xp,
            'level' => $level,
            'level_name' => self::TIER_NAMES[$tierIndex],
            'badge_color' => self::TIER_COLORS[$tierIndex],
            'xp_into_level' => $xpIntoLevel,
            'xp_for_next_level' => self::XP_PER_LEVEL,
            'percent' => (int) round($xpIntoLevel / self::XP_PER_LEVEL * 100),
        ];
    }
}
