<?php

namespace App\Services\Game;

use App\Enums\ScoreEventType;
use App\Enums\TileType;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Petak;

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
    public function __construct(private readonly ScoreService $scoreService)
    {
    }

    /**
     * @return array{type: string, delta?: int, hasil?: string}
     */
    public function resolve(GameSession $gameSession, GamePlayer $gamePlayer, Petak $petak): array
    {
        return match ($petak->jenis_petak) {
            TileType::Bonus => [
                'type' => 'bonus',
                'delta' => $this->scoreService->apply($gamePlayer, ScoreEventType::Bonus),
            ],
            TileType::Penalti => [
                'type' => 'penalti',
                'delta' => $this->scoreService->apply($gamePlayer, ScoreEventType::TilePenalty),
            ],
            TileType::Mystery => $this->resolveMystery($gameSession, $gamePlayer),
            TileType::Soal => ['type' => 'soal'],
            default => ['type' => 'none'],
        };
    }

    /**
     * Efek acak bonus/penalti (Tahap 17, keputusan final), coin-flip
     * deterministik crc32(seed:mystery:turn) % 2 — bukan mt_rand.
     */
    private function resolveMystery(GameSession $gameSession, GamePlayer $gamePlayer): array
    {
        $hash = crc32($gameSession->random_seed.':mystery:'.$gameSession->total_turn);
        $isBonus = ($hash % 2) === 0;

        $delta = $this->scoreService->apply($gamePlayer, $isBonus ? ScoreEventType::Bonus : ScoreEventType::TilePenalty);

        return ['type' => 'mystery', 'hasil' => $isBonus ? 'bonus' : 'penalti', 'delta' => $delta];
    }
}
