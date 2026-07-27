<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Game\SubmitAnswerRequest;
use App\Http\Resources\GamePlayerResource;
use App\Http\Resources\GameSessionResource;
use App\Http\Resources\SoalPublicResource;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Services\Game\GameSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(private readonly GameSessionService $gameSessionService)
    {
    }

    /**
     * Halaman gameplay Vs Robot: papan interaktif, dadu, dan modal soal
     * (Tahap 10c). Struktur papan (petak/konektor) di-embed sekali di sini;
     * state permainan (posisi/skor/giliran) selalu diambil ulang oleh frontend
     * lewat endpoint state() untuk memenuhi kontrak refresh-recovery.
     */
    public function show(GameSession $gameSession): View
    {
        Gate::authorize('view', $gameSession);

        $gameSession->load(['papan.petak', 'papan.papanKonektor']);

        return view('game.show', [
            'gameSession' => $gameSession,
        ]);
    }

    /**
     * Kontrak pemulihan refresh (Tahap 7, keputusan final): frontend memanggil
     * ini untuk menggambar ulang papan dari state server yang otoritatif,
     * tanpa mengulang animasi masa lalu.
     */
    public function state(GameSession $gameSession): JsonResponse
    {
        Gate::authorize('view', $gameSession);

        $gameSession->load(['players.user']);

        return response()->json(new GameSessionResource($gameSession));
    }

    public function roll(Request $request, GameSession $gameSession): JsonResponse
    {
        Gate::authorize('rollDice', $gameSession);

        $player = $this->resolvePlayer($gameSession, $request);
        $result = $this->gameSessionService->rollDice($gameSession, $player);

        return response()->json($this->formatTurnResult($result));
    }

    public function answer(SubmitAnswerRequest $request, GameSession $gameSession): JsonResponse
    {
        Gate::authorize('submitAnswer', $gameSession);

        $player = $this->resolvePlayer($gameSession, $request);
        $result = $this->gameSessionService->submitAnswer(
            $gameSession,
            $player,
            $request->integer('soal_id'),
            $request->input('jawaban')
        );

        return response()->json($this->formatTurnResult($result));
    }

    public function leave(Request $request, GameSession $gameSession): RedirectResponse|JsonResponse
    {
        Gate::authorize('leave', $gameSession);

        $player = $this->resolvePlayer($gameSession, $request);
        $this->gameSessionService->leave($gameSession, $player);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'left']);
        }

        return redirect()->route('dashboard')->with('status', 'Anda telah keluar dari permainan.');
    }

    /**
     * Sinyal "aku masih di sini" dari klien multiplayer (Tahap 12a) — dipanggil
     * berkala oleh frontend selagi tab terbuka. Vs Robot boleh juga memanggil
     * ini (tidak berefek apa pun selain menyegarkan timestamp) supaya frontend
     * tidak perlu bercabang logic per mode.
     */
    public function heartbeat(Request $request, GameSession $gameSession): JsonResponse
    {
        Gate::authorize('heartbeat', $gameSession);

        $player = $this->resolvePlayer($gameSession, $request);
        $this->gameSessionService->heartbeat($gameSession, $player);

        return response()->json(['status' => 'ok']);
    }

    private function resolvePlayer(GameSession $gameSession, Request $request): GamePlayer
    {
        return $gameSession->players()->where('user_id', $request->user()->id)->firstOrFail();
    }

    private function formatTurnResult(array $result): array
    {
        $response = [
            'type' => $result['type'],
            'player' => new GamePlayerResource($result['player']),
            'session' => new GameSessionResource($result['session']->load('players.user')),
        ];

        if (isset($result['nilai_dadu'])) {
            $response['nilai_dadu'] = $result['nilai_dadu'];
        }

        if (isset($result['soal'])) {
            $response['soal'] = new SoalPublicResource($result['soal']);
        }

        if ($result['type'] === 'answered') {
            $response['benar'] = $result['benar'];
            $response['pembahasan'] = $result['pembahasan'];
            $response['kunci_jawaban'] = $result['kunci_jawaban'];
        }

        $response['robot_turns'] = $result['robot_turns'] ?? [];
        $response['newly_unlocked_achievements'] = $result['newly_unlocked_achievements'] ?? [];

        if (isset($result['toast'])) {
            $response['toast'] = $result['toast'];
        }

        return $response;
    }
}
