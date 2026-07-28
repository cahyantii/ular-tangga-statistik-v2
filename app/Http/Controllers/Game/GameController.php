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

        $forcedRoll = $request->input('forced_roll');
        $forcedRoll = is_numeric($forcedRoll) ? (int) $forcedRoll : null;

        $result = $this->gameSessionService->rollDice($gameSession, $player, $forcedRoll);

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

    public function duelAnswer(Request $request, GameSession $gameSession): JsonResponse
    {
        Gate::authorize('duelAnswer', $gameSession);

        $request->validate([
            'soal_id' => 'required|integer',
            'jawaban' => 'nullable|string',
            'time_taken_ms' => 'required|integer|min:0',
        ]);

        $player = $this->resolvePlayer($gameSession, $request);
        
        $result = $this->gameSessionService->submitDuelAnswer(
            $gameSession,
            $player,
            $request->integer('soal_id'),
            $request->input('jawaban'),
            $request->integer('time_taken_ms')
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

    public function usePowerUp(Request $request, GameSession $gameSession, \App\Services\Game\PowerUpService $powerUpService): JsonResponse
    {
        Gate::authorize('view', $gameSession);

        $request->validate([
            'item_id' => 'required|string'
        ]);

        $player = $this->resolvePlayer($gameSession, $request);

        // Pastikan game sedang aktif
        if ($gameSession->status->value !== 'playing') {
            return response()->json(['error' => 'Permainan tidak sedang berjalan.'], 403);
        }

        // Pastikan giliran player tersebut (opsional, tapi disarankan)
        if ($gameSession->current_turn_game_player_id !== $player->id) {
            return response()->json(['error' => 'Bukan giliran Anda.'], 403);
        }

        try {
            $result = $powerUpService->useItem($gameSession, $player, $request->input('item_id'));
            
            // Broadcast ke pemain lain lewat soket (misal memanfaatkan GameSessionService atau event)
            broadcast(new \App\Events\Game\PowerupUsed(
                $gameSession, 
                $player, 
                $request->input('item_id'), 
                "{$player->user->name} {$result['message']}"
            ))->toOthers();
            
            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'session' => new GameSessionResource($gameSession->load('players.user'))
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function resolveInventory(Request $request, GameSession $gameSession): JsonResponse
    {
        Gate::authorize('view', $gameSession);

        $request->validate([
            'action' => 'required|in:keep,discard'
        ]);

        $player = $this->resolvePlayer($gameSession, $request);

        $inventory = $player->inventory ?? [];
        if (count($inventory) <= 3) {
            return response()->json(['error' => 'Inventory belum penuh.'], 400);
        }

        if ($request->input('action') === 'keep') {
            // Hapus index 0 (paling lama)
            array_splice($inventory, 0, 1);
        } else {
            // Hapus index terakhir (yang baru didapat)
            array_splice($inventory, count($inventory) - 1, 1);
        }

        $player->update(['inventory' => $inventory]);

        return response()->json([
            'status' => 'success',
            'session' => new GameSessionResource($gameSession->load('players.user'))
        ]);
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
            $response['raw_nilai_dadu'] = $result['raw_nilai_dadu'] ?? $result['nilai_dadu'];
            $response['double_dice_active'] = $result['double_dice_active'] ?? false;
        }

        if (isset($result['soal'])) {
            $response['soal'] = new SoalPublicResource($result['soal']);
        }

        if ($result['type'] === 'answered') {
            $response['benar'] = $result['benar'];
            $response['pembahasan'] = $result['pembahasan'];
            $response['kunci_jawaban'] = $result['kunci_jawaban'];
            $response['konektor_applied'] = $result['konektor_applied'] ?? false;
            $response['konektor_info'] = $result['konektor_info'] ?? null;

            // Sertakan mystery tile yang dipicu setelah konektor (tangga/ular)
            if (isset($result['mystery_after_konektor'])) {
                $response['mystery_after_konektor'] = $result['mystery_after_konektor'];
            }
        }

        if ($result['type'] === 'duel_answered') {
            $response['benar'] = $result['benar'];
            $response['pembahasan'] = $result['pembahasan'] ?? null;
            $response['kunci_jawaban'] = $result['kunci_jawaban'] ?? null;
        }

        if (isset($result['duel'])) {
            $response['duel'] = $result['duel']->toArray();
            if ($result['duel']->relationLoaded('questions')) {
                $response['duel']['questions'] = $result['duel']->questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'order' => $q->order,
                        'soal' => new SoalPublicResource($q->soal)
                    ];
                });
            }
        }

        $response['robot_turns'] = $result['robot_turns'] ?? [];
        $response['newly_unlocked_achievements'] = $result['newly_unlocked_achievements'] ?? [];

        if (isset($result['toast'])) {
            $response['toast'] = $result['toast'];
        }

        if ($result['type'] === 'mystery') {
            $response['item_id'] = $result['item_id'] ?? null;
            $response['item_name'] = $result['item_name'] ?? null;
            $response['item_type'] = $result['item_type'] ?? null;
        }

        return $response;
    }
}
