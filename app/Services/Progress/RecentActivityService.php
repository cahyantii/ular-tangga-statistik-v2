<?php

namespace App\Services\Progress;

use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Support\Collection;

/**
 * Aktivitas terakhir pemain (kartu "Aktivitas Terakhir" di halaman Progress
 * Belajar), digabung dari dua sumber riwayat yang sudah ada: sesi permainan
 * yang selesai (GamePlayer/GameSession) dan achievement yang diraih
 * (UserAchievement) — diurutkan menurun berdasarkan waktu kejadian.
 */
class RecentActivityService
{
    public function recent(User $user, int $limit = 6): Collection
    {
        $sessions = GamePlayer::query()
            ->with(['gameSession.papan'])
            ->where('user_id', $user->id)
            ->whereHas('gameSession', fn ($query) => $query->where('status', GameStatus::Finished->value))
            ->get()
            ->map(function (GamePlayer $player) {
                $session = $player->gameSession;
                $isWinner = $session->winner_game_player_id === $player->id;
                $namaPapan = $session->papan->nama ?? 'Papan Permainan';

                return [
                    'type' => 'game',
                    'icon' => $isWinner ? 'trophy' : 'gamepad',
                    'color' => $isWinner ? 'green' : 'blue',
                    'title' => ($isWinner ? 'Menang di papan ' : 'Bermain di papan ').$namaPapan,
                    'subtitle' => 'Skor '.$player->skor,
                    'at' => $session->finished_at,
                ];
            });

        $achievements = UserAchievement::query()
            ->with('achievement')
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn (UserAchievement $item) => $item->achievement !== null)
            ->map(fn (UserAchievement $item) => [
                'type' => 'achievement',
                'icon' => $item->achievement->icon ?? 'trophy',
                'color' => 'amber',
                'title' => 'Meraih achievement "'.$item->achievement->nama.'"',
                'subtitle' => '+'.$item->achievement->reward_poin.' Poin',
                'at' => $item->earned_at,
            ]);

        return $sessions->merge($achievements)
            ->filter(fn (array $item) => $item['at'] !== null)
            ->sortByDesc('at')
            ->take($limit)
            ->values();
    }
}
