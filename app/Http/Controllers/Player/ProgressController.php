<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Services\Progress\LearningProgressService;
use App\Services\Progress\LevelService;
use App\Services\Progress\RecentActivityService;
use App\Services\Progress\StreakService;
use App\Services\Progress\TipsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgressController extends Controller
{
    public function __construct(
        private readonly LearningProgressService $learningProgress,
        private readonly LevelService $level,
        private readonly StreakService $streak,
        private readonly TipsService $tips,
        private readonly RecentActivityService $recentActivity,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $perKategori = $this->learningProgress->perKategori($user);

        return view('player.progress', [
            'overall' => $this->learningProgress->overallAggregate($user),
            'perKategori' => $perKategori,
            'level' => $this->level->snapshot($user),
            'streak' => $this->streak->currentStreak($user),
            'tip' => $this->tips->dailyTip($user, $perKategori),
            'recentActivity' => $this->recentActivity->recent($user),
        ]);
    }
}
