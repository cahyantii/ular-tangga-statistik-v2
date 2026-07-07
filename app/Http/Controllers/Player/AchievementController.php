<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $earned = $user->userAchievements()->get()->keyBy('achievement_id');

        $achievements = Achievement::query()
            ->active()
            ->orderBy('urutan')
            ->get()
            ->map(fn (Achievement $achievement) => [
                'achievement' => $achievement,
                'earned' => $earned->has($achievement->id),
                'earned_at' => $earned->get($achievement->id)?->earned_at,
            ]);

        return view('player.achievements', [
            'achievements' => $achievements,
            'totalDiraih' => $earned->count(),
        ]);
    }
}
