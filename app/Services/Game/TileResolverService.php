<?php

namespace App\Services\Game;

use App\Enums\ScoreEventType;
use App\Enums\TileType;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Petak;
use App\Services\Game\PowerUpService;

/**
 * Menentukan (dan menerapkan) efek petak selain "soal" (yang butuh ronde
 * tanya-jawab terpisah, ditangani QuestionService oleh orkestrator).
 *
 * Petak jenis "tangga"/"ular" tidak pernah muncul di sini — itu murni penanda
 * visual di petak posisi_awal konektor, sudah diselesaikan MovementService
 * sebelum landing (Tahap 17, keputusan final).
 */
class TileResolverService
{
    public function __construct()
    {
    }

    /**
     * @return array{type: string, delta?: int, hasil?: string}
     */
    public function resolve(GameSession $gameSession, GamePlayer $gamePlayer, Petak $petak): array
    {
        // Petak yang dinonaktifkan admin (is_active=false) diperlakukan seperti
        // petak biasa — efeknya dimatikan sementara tanpa kehilangan jenis_petak
        // aslinya di database (bisa diaktifkan kembali kapan saja dari Editor Petak).
        if (! $petak->is_active) {
            return ['type' => 'none'];
        }

        return match ($petak->jenis_petak) {
            TileType::Mystery => $this->resolveMystery($gameSession, $gamePlayer),
            default => ['type' => 'none'],
        };
    }

    /**
     * Efek gacha mendapatkan power up dari tile misteri.
     */
    private function resolveMystery(GameSession $gameSession, GamePlayer $gamePlayer): array
    {
        $hash = crc32($gameSession->random_seed.':mystery:'.$gameSession->total_turn);
        $powerUps = array_keys(PowerUpService::getAvailablePowerUps());
        $index = $hash % count($powerUps);
        $obtainedItem = $powerUps[$index];
        
        $itemDetail = PowerUpService::getAvailablePowerUps()[$obtainedItem];

        return [
            'type' => 'mystery', 
            'item_id' => $obtainedItem, 
            'item_name' => $itemDetail['name'],
            'item_type' => $itemDetail['type'],
            'item_description' => $itemDetail['description']
        ];
    }
}
