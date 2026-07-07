<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Services\Game\GameSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RobotSessionController extends Controller
{
    public function __construct(private readonly GameSessionService $gameSessionService)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $gameSession = $this->gameSessionService->createVsRobotSession($request->user());

        return redirect()->route('game.show', $gameSession);
    }
}
