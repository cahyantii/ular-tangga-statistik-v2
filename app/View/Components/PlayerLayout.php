<?php

namespace App\View\Components;

use App\Services\Game\PlayerStatsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class PlayerLayout extends Component
{
    public function render(): View
    {
        $user = Auth::user();

        $score = $user
            ? app(PlayerStatsService::class)->sessionSummary($user)['skor_tertinggi']
            : null;

        return view('layouts.player', ['navScore' => $score]);
    }
}
