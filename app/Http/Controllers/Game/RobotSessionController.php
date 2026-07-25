<?php

namespace App\Http\Controllers\Game;

use App\Enums\PawnColor;
use App\Http\Controllers\Controller;
use App\Services\Game\GameSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RobotSessionController extends Controller
{
    public function __construct(private readonly GameSessionService $gameSessionService)
    {
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pawn_color' => ['nullable', Rule::enum(PawnColor::class)],
        ]);

        $gameSession = $this->gameSessionService->createVsRobotSession(
            $request->user(),
            $validated['pawn_color'] ?? null,
        );

        return redirect()->route('game.show', $gameSession);
    }
}
