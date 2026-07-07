<?php

namespace App\Http\Controllers\Player;

use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Services\Leaderboard\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __construct(private readonly LeaderboardService $leaderboard)
    {
    }

    /**
     * Satu halaman, tab/filter Global/Robot/Multiplayer (Tahap 3, keputusan
     * final) — bukan rute terpisah per jenis papan peringkat.
     */
    public function index(Request $request): View
    {
        $tab = $request->string('tab', 'global')->toString();

        $mode = match ($tab) {
            'robot' => GameMode::VsRobot,
            'multiplayer' => GameMode::Multiplayer,
            default => null,
        };

        return view('player.leaderboard', [
            'tab' => in_array($tab, ['global', 'robot', 'multiplayer'], true) ? $tab : 'global',
            'peringkat' => $this->leaderboard->top($mode),
            'currentUserId' => $request->user()->id,
        ]);
    }
}
