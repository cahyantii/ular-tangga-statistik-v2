<?php

namespace App\Http\Controllers\Game;

use App\Enums\GameStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Game\JoinRoomRequest;
use App\Models\Room;
use App\Services\Game\MatchmakingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Lobby, Quick Match, dan Private Room (Tahap 12a). Berbeda dari GameController
 * (papan/dadu/soal saat SUDAH bermain), controller ini murni menangani fase
 * SEBELUM bermain: memilih mode, mengantre lawan, atau menunggu di Waiting Room.
 */
class MatchmakingController extends Controller
{
    public function __construct(private readonly MatchmakingService $matchmaking)
    {
    }

    /**
     * Lobby (Tahap 3, keputusan final): halaman pilih Quick Match atau
     * Private Room, bukan langsung salah satu — juga menyediakan form
     * "join via kode room".
     */
    public function lobby(): View
    {
        return view('game.multiplayer.lobby');
    }

    public function quickMatch(Request $request): RedirectResponse
    {
        $room = $this->matchmaking->quickMatch($request->user());

        return redirect()->route('game.room.show', $room);
    }

    public function createRoom(Request $request): RedirectResponse
    {
        $room = $this->matchmaking->createPrivateRoom($request->user());

        return redirect()->route('game.room.show', $room);
    }

    public function joinRoom(JoinRoomRequest $request): RedirectResponse
    {
        try {
            $room = $this->matchmaking->joinPrivateRoom($request->user(), $request->string('kode_room')->toString());
        } catch (ModelNotFoundException) {
            return back()->withInput()->with('error', 'Kode room tidak ditemukan.');
        }

        return redirect()->route('game.room.show', $room);
    }

    /**
     * Waiting Room (Tahap 4, keputusan final): kode room, tombol salin,
     * status "menunggu lawan", tombol batalkan. Begitu room Playing, tautkan
     * langsung ke papan permainan — tidak ada polling/realtime di sini
     * (ditambahkan Tahap 12c), pemain me-refresh manual untuk saat ini.
     */
    public function showRoom(Room $room): View|RedirectResponse
    {
        Gate::authorize('view', $room);

        $room->load(['gameSession.players.user']);

        if ($room->status === GameStatus::Playing && $room->gameSession) {
            return redirect()->route('game.show', $room->gameSession);
        }

        return view('game.multiplayer.room', [
            'room' => $room,
        ]);
    }

    public function cancelRoom(Room $room): RedirectResponse
    {
        Gate::authorize('cancel', $room);

        $this->matchmaking->cancelRoom($room);

        return redirect()->route('dashboard')->with('status', 'Room dibatalkan.');
    }
}
