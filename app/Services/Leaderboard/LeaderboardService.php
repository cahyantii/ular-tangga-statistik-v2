<?php

namespace App\Services\Leaderboard;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Leaderboard pemain (Tahap 3, keputusan final): satu halaman, tab/filter
 * Global/Robot/Multiplayer — bukan rute terpisah per jenis. Terpisah dari
 * `AdminStatsRepository::leaderboardRingkas()` (widget ringkas 5 besar khusus
 * Admin Dashboard, Tahap 7) karena kebutuhannya beda: halaman ini butuh daftar
 * lebih panjang DAN filter per mode, bukan cuma ringkasan global.
 */
class LeaderboardService
{
    private const TTL_MINUTES = 5;

    public function cacheKey(?GameMode $mode): string
    {
        return 'leaderboard.'.($mode?->value ?? 'global');
    }

    /**
     * @return Collection<int, array{user_id: int, nama: string, total_skor: int, total_menang: int, total_main: int}>
     */
    public function top(?GameMode $mode = null, int $limit = 20): Collection
    {
        return Cache::remember(
            $this->cacheKey($mode),
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($mode, $limit) {
                $query = GamePlayer::query()
                    ->join('game_sessions', 'game_sessions.id', '=', 'game_players.game_session_id')
                    ->where('game_sessions.status', GameStatus::Finished->value)
                    ->where('game_players.is_robot', false)
                    ->whereNotNull('game_players.user_id');

                if ($mode !== null) {
                    $query->where('game_sessions.mode', $mode->value);
                }

                $rows = $query
                    ->select(
                        'game_players.user_id',
                        DB::raw('SUM(game_players.skor) as total_skor'),
                        DB::raw('COUNT(*) as total_main'),
                        DB::raw('SUM(CASE WHEN game_sessions.winner_game_player_id = game_players.id THEN 1 ELSE 0 END) as total_menang')
                    )
                    ->groupBy('game_players.user_id')
                    ->orderByDesc('total_skor')
                    ->orderByDesc('total_menang')
                    ->limit($limit)
                    ->get();

                $userMap = User::withTrashed()->whereIn('id', $rows->pluck('user_id'))->get()->keyBy('id');

                return $rows->map(fn ($row) => [
                    'user_id' => (int) $row->user_id,
                    'nama' => $userMap->get($row->user_id)?->name ?? '(pengguna dihapus)',
                    'total_skor' => (int) $row->total_skor,
                    'total_main' => (int) $row->total_main,
                    'total_menang' => (int) $row->total_menang,
                ])->values();
            }
        );
    }

    public function forgetAll(): void
    {
        Cache::forget($this->cacheKey(null));
        Cache::forget($this->cacheKey(GameMode::VsRobot));
        Cache::forget($this->cacheKey(GameMode::Multiplayer));
    }
}
