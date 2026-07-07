<?php

namespace App\Services\Game;

use App\Models\GamePlayer;
use App\Models\PapanPermainan;

/**
 * Tidak ada seri (Tahap 1, keputusan final) — permainan selalu berakhir saat
 * seorang pemain mencapai petak Finish persis (exact landing, Tahap 17).
 */
class WinConditionService
{
    public function hasWon(GamePlayer $gamePlayer, PapanPermainan $papan): bool
    {
        return $gamePlayer->posisi_pion === $papan->jumlah_petak;
    }
}
