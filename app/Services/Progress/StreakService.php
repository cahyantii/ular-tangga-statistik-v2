<?php

namespace App\Services\Progress;

use App\Enums\GameStatus;
use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Streak belajar harian (kartu "Streak Belajar" di halaman Progress Belajar),
 * dihitung dari tanggal aktivitas nyata pemain — sesi permainan yang selesai
 * dan tanggal terakhir mengerjakan soal per kategori — bukan counter
 * tersimpan terpisah yang bisa berbeda dari histori sebenarnya.
 */
class StreakService
{
    private const TTL_MINUTES = 5;

    public function cacheKey(User $user): string
    {
        return "player.stats.streak.{$user->id}";
    }

    public function currentStreak(User $user): int
    {
        return Cache::remember(
            $this->cacheKey($user),
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($user) {
                $gameDates = GamePlayer::query()
                    ->join('game_sessions', 'game_sessions.id', '=', 'game_players.game_session_id')
                    ->where('game_players.user_id', $user->id)
                    ->where('game_sessions.status', GameStatus::Finished->value)
                    ->whereNotNull('game_sessions.finished_at')
                    ->pluck('game_sessions.finished_at');

                $progressDates = $user->learningProgress()
                    ->whereNotNull('last_played_at')
                    ->pluck('last_played_at');

                $activeDates = $gameDates->merge($progressDates)
                    ->map(fn ($date) => Carbon::parse($date)->toDateString())
                    ->unique()
                    ->sortDesc()
                    ->values();

                if ($activeDates->isEmpty()) {
                    return 0;
                }

                $today = Carbon::today();
                $yesterday = $today->copy()->subDay()->toDateString();
                $mostRecent = $activeDates->first();

                if ($mostRecent !== $today->toDateString() && $mostRecent !== $yesterday) {
                    return 0;
                }

                $cursor = Carbon::parse($mostRecent);
                $streak = 0;

                foreach ($activeDates as $date) {
                    if ($date === $cursor->toDateString()) {
                        $streak++;
                        $cursor = $cursor->copy()->subDay();

                        continue;
                    }

                    break;
                }

                return $streak;
            }
        );
    }
}
