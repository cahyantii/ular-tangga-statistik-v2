<?php

namespace App\Http\Controllers\Player;

use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Services\Leaderboard\LeaderboardService;
use App\Services\Progress\LearningProgressService;
use App\Services\Progress\TipsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    public function __construct(
        private readonly LeaderboardService $leaderboard,
        private readonly LearningProgressService $learningProgress,
        private readonly TipsService $tips,
    ) {
    }

    /**
     * Satu halaman, tab/filter Global/Robot/Multiplayer (Tahap 3, keputusan
     * final) — bukan rute terpisah per jenis papan peringkat.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->string('tab', 'global')->toString();
        $tab = in_array($tab, ['global', 'robot', 'multiplayer'], true) ? $tab : 'global';

        $mode = match ($tab) {
            'robot' => GameMode::VsRobot,
            'multiplayer' => GameMode::Multiplayer,
            default => null,
        };

        $perKategori = $this->learningProgress->perKategori($user);

        return view('player.leaderboard', [
            'tab' => $tab,
            'board' => $this->leaderboard->board($mode, $user),
            'currentUserId' => $user->id,
            'tip' => $this->tips->dailyTip($user, $perKategori),
        ]);
    }
}
