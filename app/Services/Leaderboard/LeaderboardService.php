<?php

namespace App\Services\Leaderboard;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Models\LearningProgress;
use App\Models\User;
use App\Services\Progress\LevelService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Leaderboard pemain (Tahap 3, keputusan final): satu halaman, tab/filter
 * Global/Robot/Multiplayer — bukan rute terpisah per jenis. Terpisah dari
 * `AdminStatsRepository::leaderboardRingkas()` (widget ringkas 5 besar khusus
 * Admin Dashboard, Tahap 7) karena kebutuhannya beda: halaman ini butuh daftar
 * lebih panjang DAN filter per mode, bukan cuma ringkasan global.
 *
 * Peringkat (RANK() OVER, mendukung seri/tie) diurutkan: total_skor DESC,
 * total_menang DESC, tanggal bergabung ASC — dihitung SEKALI per mode lalu
 * di-cache (rankedBoard()), bukan per pemain, supaya menampilkan N pemain
 * tetap konstan jumlah query-nya (tidak N+1).
 *
 * Badge/tier tiap pemain memakai rumus XP yang SAMA dengan LevelService
 * (Progress & Profil) — tapi dihitung batch di sini (bulkXp()) dari XP
 * GLOBAL pemain (bukan yang difilter mode), supaya badge seorang pemain
 * konsisten di semua tab, bukan berubah-ubah tergantung tab yang aktif.
 */
class LeaderboardService
{
    private const TTL_MINUTES = 5;

    public function cacheKey(?GameMode $mode): string
    {
        return 'leaderboard.board.'.($mode?->value ?? 'global');
    }

    /**
     * @return array{top3: Collection, table: Collection, current: ?array, total_pemain: int}
     */
    public function board(?GameMode $mode, User $currentUser, int $tableSize = 5): array
    {
        $ranked = $this->rankedBoard($mode);

        $top3 = $ranked->take(3)->values();

        $tableRows = $ranked->slice(3, $tableSize)
            ->reject(fn (array $row) => $row['user_id'] === $currentUser->id)
            ->values();

        return [
            'top3' => $top3,
            'table' => $tableRows,
            'current' => $ranked->firstWhere('user_id', $currentUser->id),
            'total_pemain' => $ranked->count(),
        ];
    }

    public function forgetAll(): void
    {
        Cache::forget($this->cacheKey(null));
        Cache::forget($this->cacheKey(GameMode::VsRobot));
        Cache::forget($this->cacheKey(GameMode::Multiplayer));
    }

    /**
     * @return Collection<int, array{user_id: int, rank: int, nama: string, avatar_url: ?string, badge: string, badge_color: string, total_skor: int, total_main: int, total_menang: int}>
     */
    private function rankedBoard(?GameMode $mode): Collection
    {
        return Cache::remember(
            $this->cacheKey($mode),
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($mode) {
                $rows = $this->rankedRows($mode);

                if ($rows->isEmpty()) {
                    return collect();
                }

                $userIds = $rows->pluck('user_id');
                $users = User::withTrashed()->whereIn('id', $userIds)->get()->keyBy('id');
                $xpByUser = $this->bulkXp($userIds);

                return $rows->map(function ($row) use ($users, $xpByUser) {
                    $user = $users->get($row->user_id);
                    $badge = LevelService::fromXp($xpByUser[$row->user_id] ?? 0);

                    return [
                        'user_id' => (int) $row->user_id,
                        'rank' => (int) $row->rnk,
                        'nama' => $user?->name ?? '(pengguna dihapus)',
                        'avatar_url' => $user?->avatar_url,
                        'badge' => $badge['level_name'],
                        'badge_color' => $badge['badge_color'],
                        'total_skor' => (int) $row->total_skor,
                        'total_main' => (int) $row->total_main,
                        'total_menang' => (int) $row->total_menang,
                    ];
                })->values();
            }
        );
    }

    private function rankedRows(?GameMode $mode): Collection
    {
        $aggregated = DB::table('game_players')
            ->join('game_sessions', 'game_sessions.id', '=', 'game_players.game_session_id')
            ->join('users', 'users.id', '=', 'game_players.user_id')
            ->where('game_sessions.status', GameStatus::Finished->value)
            ->where('game_players.is_robot', false)
            ->whereNotNull('game_players.user_id')
            ->when($mode !== null, fn ($query) => $query->where('game_sessions.mode', $mode->value))
            ->groupBy('game_players.user_id', 'users.created_at')
            ->select(
                'game_players.user_id',
                DB::raw('SUM(game_players.skor) as total_skor'),
                DB::raw('COUNT(*) as total_main'),
                DB::raw('SUM(CASE WHEN game_sessions.winner_game_player_id = game_players.id THEN 1 ELSE 0 END) as total_menang'),
                'users.created_at as joined_at'
            );

        return DB::query()
            ->fromSub($aggregated, 'agg')
            ->selectRaw('agg.*, RANK() OVER (ORDER BY agg.total_skor DESC, agg.total_menang DESC, agg.joined_at ASC) as rnk')
            ->orderBy('rnk')
            ->get();
    }

    /**
     * XP global (semua mode) untuk sekumpulan user_id, dihitung dengan rumus
     * yang sama dengan LevelService::computeXp() tapi dalam query batch
     * (3 query total untuk berapa pun jumlah pemain), bukan 1 query per
     * pemain seperti memanggil LevelService->snapshot() di dalam loop.
     *
     * @param  Collection<int, int>  $userIds
     * @return array<int, int>
     */
    private function bulkXp(Collection $userIds): array
    {
        $totalBenarByUser = LearningProgress::query()
            ->whereIn('user_id', $userIds)
            ->selectRaw('user_id, SUM(total_benar) as total_benar')
            ->groupBy('user_id')
            ->pluck('total_benar', 'user_id');

        $totalMenangByUser = DB::table('game_players')
            ->join('game_sessions', 'game_sessions.id', '=', 'game_players.game_session_id')
            ->where('game_sessions.status', GameStatus::Finished->value)
            ->where('game_players.is_robot', false)
            ->whereIn('game_players.user_id', $userIds)
            ->selectRaw('game_players.user_id as uid, SUM(CASE WHEN game_sessions.winner_game_player_id = game_players.id THEN 1 ELSE 0 END) as total_menang')
            ->groupBy('game_players.user_id')
            ->pluck('total_menang', 'uid');

        $rewardPoinByUser = DB::table('user_achievements')
            ->join('achievements', 'achievements.id', '=', 'user_achievements.achievement_id')
            ->whereIn('user_achievements.user_id', $userIds)
            ->selectRaw('user_achievements.user_id as uid, SUM(achievements.reward_poin) as reward_poin')
            ->groupBy('user_achievements.user_id')
            ->pluck('reward_poin', 'uid');

        return $userIds->mapWithKeys(function ($id) use ($totalBenarByUser, $totalMenangByUser, $rewardPoinByUser) {
            $xp = ((int) ($totalBenarByUser[$id] ?? 0)) * LevelService::XP_PER_SOAL_BENAR
                + ((int) ($totalMenangByUser[$id] ?? 0)) * LevelService::XP_PER_KEMENANGAN
                + (int) ($rewardPoinByUser[$id] ?? 0);

            return [$id => $xp];
        })->all();
    }
}
