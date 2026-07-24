<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AdminStatsRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Menyediakan data siap-tampil untuk Admin Analytics Dashboard,
 * dengan cache per-widget (Tahap 14, keputusan final).
 *
 * Setiap konstanta *_CACHE_KEY di sini WAJIB dipanggil Cache::forget()
 * oleh operasi tulis terkait pada Tahap CRUD Master:
 * - STATS_PLAYERS_CACHE_KEY  -> dibuat/dihapusnya User (role player)
 * - STATS_GAMES_CACHE_KEY    -> game_sessions selesai/berubah status
 * - STATS_QUESTIONS_CACHE_KEY-> create/update/delete Soal, GameLog answer_submitted baru
 * - STATS_LEADERBOARD_CACHE_KEY -> game_sessions selesai (winner ditentukan)
 * - CHART_GAMES_DAILY_CACHE_KEY -> game_sessions selesai
 * - CHART_QUESTIONS_ACCURACY_CACHE_KEY -> GameLog answer_submitted baru, atau Soal dihapus
 */
class AdminDashboardService
{
    public const STATS_PLAYERS_CACHE_KEY = 'admin.stats.players';

    public const STATS_GAMES_CACHE_KEY = 'admin.stats.games';

    public const STATS_QUESTIONS_CACHE_KEY = 'admin.stats.questions';

    public const STATS_LEADERBOARD_CACHE_KEY = 'admin.stats.leaderboard';

    public const CHART_GAMES_DAILY_CACHE_KEY = 'admin.chart.games.daily';

    public const CHART_QUESTIONS_ACCURACY_CACHE_KEY = 'admin.chart.questions.accuracy';

    public const STATS_ACHIEVEMENTS_CACHE_KEY = 'admin.stats.achievements';

    private const TTL_MINUTES = 5;

    public function __construct(private readonly AdminStatsRepository $repository)
    {
    }

    public function statistikPemain(): array
    {
        return Cache::remember(
            self::STATS_PLAYERS_CACHE_KEY,
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->repository->totalPemain()
        );
    }

    public function statistikPermainan(): array
    {
        return Cache::remember(
            self::STATS_GAMES_CACHE_KEY,
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->repository->totalPermainan()
        );
    }

    public function statistikSoal(): array
    {
        return Cache::remember(
            self::STATS_QUESTIONS_CACHE_KEY,
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->repository->statistikSoal()
        );
    }

    public function leaderboardRingkas(int $limit = 5): array
    {
        return Cache::remember(
            self::STATS_LEADERBOARD_CACHE_KEY,
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->repository->leaderboardRingkas($limit)->all()
        );
    }

    public function chartPermainanHarian(int $hari, Carbon $sampai): array
    {
        return Cache::remember(
            self::CHART_GAMES_DAILY_CACHE_KEY.".{$hari}.{$sampai->toDateString()}",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->repository->gamesPerHari($hari, $sampai)->all()
        );
    }

    public function chartAkurasiSoal(): array
    {
        return Cache::remember(
            self::CHART_QUESTIONS_ACCURACY_CACHE_KEY,
            now()->addMinutes(self::TTL_MINUTES),
            fn () => [
                'per_kategori' => $this->repository->akurasiPerKategori()->all(),
                'soal_tersulit' => $this->repository->soalTersulit(10)->all(),
            ]
        );
    }

    public function achievementTerbaru(int $hari, Carbon $sampai, int $limit = 5): array
    {
        return Cache::remember(
            self::STATS_ACHIEVEMENTS_CACHE_KEY.".{$hari}.{$sampai->toDateString()}.{$limit}",
            now()->addMinutes(self::TTL_MINUTES),
            fn () => $this->repository->achievementTerbaru($hari, $sampai, $limit)->all()
        );
    }
}
