<?php

namespace App\Services\Game\TurnDecider;

use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Soal;
use Illuminate\Support\Collection;

/**
 * Robot (Tahap 11, keputusan final): tingkat jawaban benar TETAP 70%, bukan
 * konfigurasi admin (berbeda dari game_settings yang memang dibuat "no
 * hardcoding" - ini murni parameter AI, bukan parameter permainan). Deterministik
 * lewat crc32(seed:robot_jawab:turn), pola yang sama dengan dadu/mystery/soal
 * agar reproducible untuk audit, BUKAN mt_rand.
 */
class RobotTurnDecider implements TurnDeciderInterface
{
    public const CORRECT_ANSWER_RATE = 70;

    public function shouldAutoPlay(GamePlayer $gamePlayer): bool
    {
        return $gamePlayer->is_robot;
    }

    public function decideAnswer(GameSession $gameSession, GamePlayer $gamePlayer, Soal $soal): ?string
    {
        $hash = crc32($gameSession->random_seed.':robot_jawab:'.$gameSession->total_turn);

        if (($hash % 100) < self::CORRECT_ANSWER_RATE) {
            return $soal->kunci_jawaban;
        }

        return $this->pilihJawabanSalah($soal);
    }

    private function pilihJawabanSalah(Soal $soal): ?string
    {
        /** @var Collection<int, string> $opsiSalah */
        $opsiSalah = collect($soal->opsi_jawaban)
            ->keys()
            ->reject(fn (string $kunci) => strtoupper($kunci) === strtoupper($soal->kunci_jawaban));

        return $opsiSalah->first();
    }
}
