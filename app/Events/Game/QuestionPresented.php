<?php

namespace App\Events\Game;

use App\Events\Game\Concerns\BroadcastsToGameRoom;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Soal;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionPresented implements ShouldBroadcast
{
    use BroadcastsToGameRoom;
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly GameSession $gameSession,
        public readonly GamePlayer $gamePlayer,
        public readonly Soal $soal,
        public readonly int $batasWaktuDetik,
    ) {
    }

    /**
     * Sengaja TIDAK menyertakan isi soal (pertanyaan/opsi/kunci_jawaban) —
     * broadcast ini hanya untuk memberi tahu LAWAN bahwa pemain sedang
     * menjawab soal, bukan untuk menampilkan soalnya ke lawan. Menyiarkan
     * model Soal utuh lewat channel presence akan membocorkan kunci_jawaban
     * ke browser lawan sebelum dijawab.
     */
    public function broadcastWith(): array
    {
        return [
            'game_session_id' => $this->gameSession->id,
            'game_player_id' => $this->gamePlayer->id,
            'soal_id' => $this->soal->id,
            'batas_waktu_detik' => $this->batasWaktuDetik,
        ];
    }
}
