<?php

namespace App\Console\Commands;

use App\Enums\GameLogEventType;
use App\Enums\GameStatus;
use App\Models\GameSession;
use App\Services\Game\GameLogService;
use App\Services\Game\PlayerStatsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Tahap 18/11, keputusan final: sesi yang tidak ada aktivitas >24 jam
 * (kemungkinan pemain menutup tab tanpa menekan "Keluar") ditandai Abandoned
 * agar tidak menyumbat pengecekan "sesi aktif" saat pemain mau membuat sesi
 * baru. Dijalankan berkala lewat scheduler, bukan trigger request pengguna.
 */
class AbandonStaleGameSessions extends Command
{
    protected $signature = 'game:abandon-stale-sessions';

    protected $description = 'Tandai Abandoned sesi permainan yang tidak ada aktivitas lebih dari 24 jam';

    public function handle(GameLogService $gameLog, PlayerStatsService $playerStats): int
    {
        $staleSessions = GameSession::query()
            ->where('status', GameStatus::Playing)
            ->where('updated_at', '<', now()->subDay())
            ->with('players.user')
            ->get();

        foreach ($staleSessions as $gameSession) {
            DB::transaction(function () use ($gameSession, $gameLog, $playerStats) {
                $gameSession->update(['status' => GameStatus::Abandoned, 'finished_at' => now()]);

                $gameLog->log($gameSession, GameLogEventType::Forfeited, null, $gameSession->total_turn, [
                    'reason' => 'stale_24h',
                ]);

                foreach ($gameSession->players as $player) {
                    if ($player->user_id !== null) {
                        Cache::forget($playerStats->cacheKey($player->user));
                    }
                }
            });
        }

        $this->info("Sesi ditandai Abandoned karena tidak aktif >24 jam: {$staleSessions->count()}");

        return self::SUCCESS;
    }
}
