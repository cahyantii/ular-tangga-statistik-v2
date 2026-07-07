<?php

namespace App\Services\Game;

use App\Models\GameSession;

/**
 * Dadu deterministik via crc32(random_seed . ':' . total_turn) % 6 + 1
 * (Tahap 17, keputusan final) — BUKAN mt_rand/mt_srand, karena itu memakai
 * state global yang tidak aman di bawah request konkuren, dan tidak bisa
 * direproduksi ulang untuk audit.
 */
class DiceService
{
    public function roll(GameSession $gameSession): int
    {
        $hash = crc32($gameSession->random_seed.':'.$gameSession->total_turn);

        return ($hash % 6) + 1;
    }
}
