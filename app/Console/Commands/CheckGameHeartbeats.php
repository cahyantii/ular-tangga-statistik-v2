<?php

namespace App\Console\Commands;

use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\PlayerStatus;
use App\Models\GameSession;
use App\Repositories\Game\GameSettingsRepository;
use App\Services\Game\GameSessionService;
use Illuminate\Console\Command;

/**
 * Deteksi disconnect multiplayer (Tahap 12a, keputusan final Stage 1):
 * heartbeat basi -> sesi Paused; jika tidak reconnect dalam
 * `reconnect_timeout_seconds` -> lawan menang WO. Dijadwalkan sub-menit
 * (lihat routes/console.php) karena cron biasa tidak bisa di bawah 1 menit —
 * produksi harus menjalankan `php artisan schedule:work` (proses panjang),
 * bukan cron sekali per menit, supaya deteksi ini terasa responsif.
 */
class CheckGameHeartbeats extends Command
{
    protected $signature = 'game:check-heartbeats';

    protected $description = 'Deteksi disconnect pemain multiplayer via heartbeat, jeda sesi, dan forfeit setelah masa tenggang habis';

    public function handle(GameSessionService $gameSessionService, GameSettingsRepository $settings): int
    {
        $heartbeatTimeout = $settings->getInt('heartbeat_timeout_seconds');
        $reconnectTimeout = $settings->getInt('reconnect_timeout_seconds');

        $this->detectNewDisconnects($gameSessionService, $heartbeatTimeout);
        $this->resolvePausedSessions($gameSessionService, $heartbeatTimeout, $reconnectTimeout);

        return self::SUCCESS;
    }

    private function detectNewDisconnects(GameSessionService $gameSessionService, int $heartbeatTimeout): void
    {
        $playingSessions = GameSession::query()
            ->where('mode', GameMode::Multiplayer)
            ->where('status', GameStatus::Playing)
            ->with('players')
            ->get();

        foreach ($playingSessions as $gameSession) {
            foreach ($gameSession->players as $player) {
                $isStale = $player->status === PlayerStatus::Active
                    && $player->last_heartbeat_at !== null
                    && $player->last_heartbeat_at->lt(now()->subSeconds($heartbeatTimeout));

                if ($isStale) {
                    $gameSessionService->pauseForDisconnect($gameSession, $player);
                    break;
                }
            }
        }
    }

    private function resolvePausedSessions(GameSessionService $gameSessionService, int $heartbeatTimeout, int $reconnectTimeout): void
    {
        $pausedSessions = GameSession::query()
            ->where('mode', GameMode::Multiplayer)
            ->where('status', GameStatus::Paused)
            ->with('players')
            ->get();

        foreach ($pausedSessions as $gameSession) {
            $disconnected = $gameSession->players->firstWhere('status', PlayerStatus::Disconnected);

            if (! $disconnected) {
                continue;
            }

            $stillStale = $disconnected->last_heartbeat_at === null
                || $disconnected->last_heartbeat_at->lt(now()->subSeconds($heartbeatTimeout));

            if (! $stillStale) {
                $gameSessionService->resumeFromDisconnect($gameSession, $disconnected);

                continue;
            }

            if ($disconnected->updated_at->lt(now()->subSeconds($reconnectTimeout))) {
                $gameSessionService->forfeitDueToDisconnect($gameSession, $disconnected);
            }
        }
    }
}
