<?php

namespace App\Services\Game;

use App\Enums\GameLogEventType;
use App\Models\GameLog;
use App\Models\GameSession;

/**
 * Helper tulis Game Log (Tahap 5, keputusan final): setiap aksi signifikan
 * dalam permainan harus tercatat, dipakai untuk audit/debug/analitik.
 */
class GameLogService
{
    public function log(
        GameSession $gameSession,
        GameLogEventType $eventType,
        ?int $userId,
        ?int $turnNumber,
        array $payload = []
    ): GameLog {
        return GameLog::create([
            'game_session_id' => $gameSession->id,
            'user_id' => $userId,
            'event_type' => $eventType,
            'turn_number' => $turnNumber,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
