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
        // Sistem heartbeat disconnect dimatikan.
        // $heartbeatTimeout = $settings->getInt('heartbeat_timeout_seconds');
        // $reconnectTimeout = $settings->getInt('reconnect_timeout_seconds');
        // $this->detectNewDisconnects($gameSessionService, $heartbeatTimeout);
        // $this->resolveDisconnectedPlayers($gameSessionService, $heartbeatTimeout, $reconnectTimeout);

        // Hanya jalankan autoroll untuk pemain AFK
        $this->checkAutoRolls($gameSessionService);

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

    /**
     * Cek SEMUA pemain berstatus Disconnected di sesi Playing MAUPUN Paused
     * (bukan cuma sesi Paused seperti sebelumnya) - untuk game 3-6 pemain,
     * sesi bisa TETAP Playing walau satu pemain terputus (lihat
     * GameSessionService::pauseForDisconnect(), "lanjut tanpa dia"), jadi
     * pemain yang terputus itu tetap perlu dipantau reconnect/timeout-nya
     * walau sesinya sendiri tidak pernah masuk status Paused. Looping semua
     * pemain Disconnected (bukan cuma firstWhere) juga penting kalau lebih
     * dari satu pemain terputus bersamaan di sesi yang sama.
     */
    private function resolveDisconnectedPlayers(GameSessionService $gameSessionService, int $heartbeatTimeout, int $reconnectTimeout): void
    {
        $sessions = GameSession::query()
            ->where('mode', GameMode::Multiplayer)
            ->whereIn('status', [GameStatus::Playing, GameStatus::Paused])
            ->with('players')
            ->get();

        foreach ($sessions as $gameSession) {
            foreach ($gameSession->players->where('status', PlayerStatus::Disconnected) as $disconnected) {
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

    private function checkAutoRolls(GameSessionService $gameSessionService): void
    {
        $playingSessions = GameSession::query()
            ->where('status', GameStatus::Playing)
            ->whereNull('active_question_id') // only process when waiting for dice roll
            ->with('players')
            ->get();

        foreach ($playingSessions as $gameSession) {
            // Check if 28+ seconds have passed since turn started
            if ($gameSession->current_turn_started_at && \Carbon\Carbon::parse($gameSession->current_turn_started_at)->diffInSeconds(now()) >= 28) {
                $player = $gameSession->players->firstWhere('id', $gameSession->current_turn_game_player_id);
                if ($player && !$player->is_robot && $player->status === PlayerStatus::Active) {
                    $gameSessionService->autoRoll($gameSession, $player);
                }
            }
        }
    }
}
