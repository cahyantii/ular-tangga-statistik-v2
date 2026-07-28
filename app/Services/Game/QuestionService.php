<?php

namespace App\Services\Game;

use App\Models\GameQuestionUsed;
use App\Models\GameSession;
use App\Models\Petak;
use App\Models\Soal;
use App\Repositories\Game\GameSettingsRepository;

/**
 * Pemilihan soal (Tahap 1/17, keputusan final):
 * - Soal tidak boleh berulang dalam satu sesi (dicatat di game_questions_used,
 *   direset otomatis karena tabel ini per-sesi, bukan per-user).
 * - Jika pool soal kategori tersebut sudah habis dalam sesi ini, petak
 *   diperlakukan seperti "biasa" (tidak error, tidak dianggap pelanggaran).
 * - Pemilihan deterministik crc32(seed:soal:turn) — bukan mt_rand — demi
 *   konsistensi dengan dice/mystery dan reproducibility untuk audit.
 */
class QuestionService
{
    public function __construct(private readonly GameSettingsRepository $settings)
    {
    }

    public function selectQuestion(GameSession $gameSession, Petak $petak): ?Soal
    {
        $usedIds = GameQuestionUsed::query()
            ->where('game_session_id', $gameSession->id)
            ->pluck('soal_id');

        $query = Soal::query()->active()->whereNotIn('id', $usedIds);

        if ($petak->kategori_id !== null) {
            $query->where('kategori_id', $petak->kategori_id);
        }

        $availableIds = $query->orderBy('id')->pluck('id')->values();

        if ($availableIds->isEmpty()) {
            return null;
        }

        $hash = crc32($gameSession->random_seed.':soal:'.$gameSession->total_turn);
        $index = $hash % $availableIds->count();
        $soal = Soal::query()->findOrFail($availableIds[$index]);

        GameQuestionUsed::create([
            'game_session_id' => $gameSession->id,
            'soal_id' => $soal->id,
            'used_at' => now(),
        ]);

        $gameSession->update([
            'active_question_id' => $soal->id,
            'active_question_expires_at' => now()->addSeconds($this->settings->getInt('question_timer_seconds')),
        ]);

        return $soal;
    }
}
