<?php

namespace App\Services\Game;

use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Statistik permainan seorang pemain (dashboard, achievement, sertifikat).
 *
 * Cache key `player.stats.session.{userId}` (Tahap 15, keputusan final) HARUS
 * di-forget() secara sinkron di dalam GameSessionService::finish() pada tahap
 * Game Engine, karena statistik ini berubah persis saat sebuah sesi selesai.
 */
class PlayerStatsService
{
    private const TTL_MINUTES = 5;

    public function cacheKey(User $user): string
    {
        return "player.stats.session.{$user->id}";
    }

    /**
     * Agregat satu query SQL: total_main, total_menang, skor_tertinggi,
     * rata_rata_skor, total_waktu_detik — dari seluruh sesi Finished pemain.
     */
    public function sessionSummary(User $user): array
    {
        return Cache::remember(
            $this->cacheKey($user),
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($user) {
                $row = GamePlayer::query()
                    ->join('game_sessions', 'game_sessions.id', '=', 'game_players.game_session_id')
                    ->where('game_players.user_id', $user->id)
                    ->where('game_sessions.status', GameStatus::Finished->value)
                    ->selectRaw('
                        COUNT(*) as total_main,
                        SUM(CASE WHEN game_sessions.winner_game_player_id = game_players.id THEN 1 ELSE 0 END) as total_menang,
                        COALESCE(MAX(game_players.skor), 0) as skor_tertinggi,
                        COALESCE(AVG(game_players.skor), 0) as rata_rata_skor,
                        COALESCE(SUM(game_sessions.duration_seconds), 0) as total_waktu_detik
                    ')
                    ->first();

                return [
                    'total_main' => (int) ($row->total_main ?? 0),
                    'total_menang' => (int) ($row->total_menang ?? 0),
                    'skor_tertinggi' => (int) ($row->skor_tertinggi ?? 0),
                    'rata_rata_skor' => round((float) ($row->rata_rata_skor ?? 0), 1),
                    'total_waktu_detik' => (int) ($row->total_waktu_detik ?? 0),
                ];
            }
        );
    }

    /**
     * Sesi yang masih bisa dilanjutkan (Waiting/Playing/Paused) — untuk kartu
     * "Lanjutkan Permainan". Waiting ditambahkan Tahap 12a: pemain yang sedang
     * mengantre Quick Match atau menunggu lawan di Private Room juga harus
     * diarahkan kembali ke sesi itu, bukan diam-diam dibiarkan mengira belum
     * punya sesi apa pun. Selalu live (tidak di-cache), karena status ini
     * berubah setiap saat selama gameplay.
     */
    public function activeSession(User $user): ?GameSession
    {
        $gamePlayer = GamePlayer::query()
            ->where('user_id', $user->id)
            ->whereHas('gameSession', function ($query) {
                $query->whereIn('status', [GameStatus::Waiting->value, GameStatus::Playing->value, GameStatus::Paused->value]);
            })
            ->with('gameSession')
            ->latest('id')
            ->first();

        return $gamePlayer?->gameSession;
    }
}
