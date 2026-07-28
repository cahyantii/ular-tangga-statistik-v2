<?php

namespace App\Policies;

use App\Enums\GameStatus;
use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    /**
     * User boleh melihat sesi jika ia salah satu peserta (game_players) di dalamnya.
     */
    public function view(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession);
    }

    public function rollDice(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession)
            && $gameSession->status === GameStatus::Playing;
    }

    public function submitAnswer(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession)
            && $gameSession->status === GameStatus::Playing;
    }

    public function duelAnswer(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession)
            && $gameSession->status === GameStatus::Duel;
    }

    public function resume(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession)
            && $gameSession->status === GameStatus::Paused;
    }

    public function leave(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession)
            && in_array($gameSession->status, [GameStatus::Playing, GameStatus::Paused], true);
    }

    /**
     * Tahap 12a: heartbeat harus tetap diterima selagi Paused (justru itu
     * jalur cepat reconnect) — bukan hanya Playing seperti rollDice/submitAnswer.
     */
    public function heartbeat(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession)
            && in_array($gameSession->status, [GameStatus::Playing, GameStatus::Paused], true);
    }

    public function viewResult(User $user, GameSession $gameSession): bool
    {
        return $this->isParticipant($user, $gameSession);
    }

    private function isParticipant(User $user, GameSession $gameSession): bool
    {
        return $gameSession->players()->where('user_id', $user->id)->exists();
    }
}
