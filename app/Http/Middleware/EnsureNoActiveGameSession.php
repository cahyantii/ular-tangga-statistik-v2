<?php

namespace App\Http\Middleware;

use App\Enums\GameStatus;
use App\Exceptions\ActiveGameSessionExistsException;
use App\Models\GamePlayer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencegah pemain membuat sesi permainan baru (Vs Robot / Multiplayer) selagi
 * masih memiliki sesi berstatus Waiting/Playing/Paused - lihat keputusan
 * Tahap 7 & 15. Waiting ditambahkan di Tahap 12a: seorang pemain yang sudah
 * mengantre Quick Match atau membuat/join Private Room (Room/GameSession
 * langsung dibuat sejak Waiting, bukan hanya saat Playing) tidak boleh
 * mengantre/membuat sesi kedua secara bersamaan.
 *
 * Guard ini adalah lapisan pertama (pengecekan cepat sebelum request diproses).
 * Guard atomic sesungguhnya (mencegah race condition) dilakukan lewat
 * Cache::lock() di dalam Service pembuat sesi (GameSessionService), bukan di sini.
 */
class EnsureNoActiveGameSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasActiveSession = GamePlayer::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('gameSession', function ($query) {
                $query->whereIn('status', [GameStatus::Waiting, GameStatus::Playing, GameStatus::Paused]);
            })
            ->exists();

        if ($hasActiveSession) {
            throw new ActiveGameSessionExistsException();
        }

        return $next($request);
    }
}
