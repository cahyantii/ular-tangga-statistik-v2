<?php
namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\GameSession;
use App\Models\Soal;
use App\Repositories\Game\GameSettingsRepository;
use Illuminate\Http\Request;

class InternalColyseusController extends Controller
{
    public function init(Request $request, GameSettingsRepository $settings)
    {
        $sessionId = $request->input('session_id');
        $session = GameSession::with(['papan.petak', 'papan.papanKonektor', 'players.user'])->find($sessionId);
        if (!$session) return response()->json(['error' => 'Session not found'], 404);

        $soal = Soal::active()->get();

        return response()->json([
            'session' => $session,
            'soal' => $soal,
            'question_timer_seconds' => $settings->getInt('question_timer_seconds')
        ]);
    }
}
