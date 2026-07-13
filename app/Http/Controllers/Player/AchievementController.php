<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Services\Achievement\AchievementEvaluationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(Request $request, AchievementEvaluationService $achievementEvaluation): View
    {
        $achievements = $achievementEvaluation->progressSnapshot($request->user());

        return view('player.achievements', [
            'achievements' => $achievements,
            'totalAchievements' => $achievements->count(),
            'totalDiraih' => $achievements->where('earned', true)->count(),
        ]);
    }
}
