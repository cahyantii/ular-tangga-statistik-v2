<?php

namespace App\Services\Game;

use App\Actions\GenerateRoomCode;
use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\PlayerStatus;
use App\Enums\RoomType;
use App\Exceptions\RoomFullException;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PapanPermainan;
use App\Models\Room;
use App\Models\User;
use App\Repositories\Game\GameSettingsRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Room & Matchmaking (Tahap 12a, keputusan final Stage 5/1): dua jenis room
 * — Quick Match (auto-pairing) dan Private Room (kode). Room+GameSession+
 * GamePlayer pertama dibuat sejak status Waiting (bukan menunggu di memori),
 * supaya EnsureNoActiveGameSession/PlayerStatsService::activeSession() bisa
 * menemukannya kalau pembuat me-refresh/menutup tab lalu kembali.
 */
class MatchmakingService
{
    public function __construct(
        private readonly GameSessionService $gameSessionService,
        private readonly GameSettingsRepository $settings,
        private readonly GenerateRoomCode $generateRoomCode,
        private readonly NotificationService $notifications,
    ) {
    }

    /**
     * Satu lock global (Tahap 12, keputusan final) — quick match secara alami
     * memproses antrean satu per satu, jadi tidak butuh lock per-user seperti
     * pembuatan sesi Vs Robot/Private Room.
     */
    public function quickMatch(User $user): Room
    {
        return Cache::lock('matchmaking-queue', 10)->block(5, function () use ($user) {
            $this->gameSessionService->assertNoActiveSession($user);

            $room = DB::transaction(function () use ($user) {
                $room = Room::query()
                    ->where('tipe', RoomType::QuickMatch)
                    ->where('status', GameStatus::Waiting)
                    ->lockForUpdate()
                    ->first();

                if ($room) {
                    $this->joinExistingRoom($room, $user);

                    return $room->fresh();
                }

                return $this->createRoomWithFirstPlayer($user, RoomType::QuickMatch, null);
            });

            $this->notifyRoomOutcome($room, $user);

            return $room;
        });
    }

    public function createPrivateRoom(User $user): Room
    {
        return Cache::lock("create-session-user-{$user->id}", 10)->block(5, function () use ($user) {
            $this->gameSessionService->assertNoActiveSession($user);

            $room = DB::transaction(fn () => $this->createRoomWithFirstPlayer(
                $user,
                RoomType::Private,
                ($this->generateRoomCode)()
            ));

            $this->notifyRoomOutcome($room, $user);

            return $room;
        });
    }

    public function joinPrivateRoom(User $user, string $kodeRoom): Room
    {
        return Cache::lock("create-session-user-{$user->id}", 10)->block(5, function () use ($user, $kodeRoom) {
            $this->gameSessionService->assertNoActiveSession($user);

            $room = DB::transaction(function () use ($user, $kodeRoom) {
                $room = Room::query()
                    ->where('kode_room', strtoupper($kodeRoom))
                    ->lockForUpdate()
                    ->firstOrFail();

                Gate::forUser($user)->authorize('join', $room);

                $this->joinExistingRoom($room, $user);

                return $room->fresh();
            });

            $this->notifyRoomOutcome($room, $user);

            return $room;
        });
    }

    /**
     * Notifikasi Bell (Tahap 19) dikirim SETELAH transaksi commit (disiplin
     * yang sama dengan GameSessionService) — room baru terbentuk (fan-out
     * admin) atau room terisi penuh (pembuat room diberi tahu lawan bergabung).
     */
    private function notifyRoomOutcome(Room $room, User $actingUser): void
    {
        $sudahPenuh = $room->gameSession?->players()->count() >= 2;

        if ($sudahPenuh && $room->created_by !== $actingUser->id) {
            $this->notifications->send($room->createdBy, $this->notifications->payloadOpponentJoined($room, $actingUser));
            $this->notifications->sendToAdmins($this->notifications->payloadRoomActivityAdmin(
                $room,
                "{$actingUser->name} bergabung ke room {$room->kode_room}, permainan dimulai."
            ));

            return;
        }

        if (! $sudahPenuh) {
            $this->notifications->sendToAdmins($this->notifications->payloadRoomActivityAdmin(
                $room,
                "{$actingUser->name} membuat room ({$room->tipe->label()}) dan menunggu lawan."
            ));
        }
    }

    /**
     * Hanya pembuat room, hanya selagi Waiting (RoomPolicy::cancel) — dibatalkan
     * berarti Room dan GameSession-nya (yang baru berisi 1 pemain) di-Abandoned.
     */
    public function cancelRoom(Room $room): void
    {
        DB::transaction(function () use ($room) {
            $room = Room::query()->lockForUpdate()->findOrFail($room->id);

            if ($room->status !== GameStatus::Waiting) {
                return;
            }

            $room->update(['status' => GameStatus::Abandoned]);
            $room->gameSession?->update(['status' => GameStatus::Abandoned, 'finished_at' => now()]);
        });
    }

    private function joinExistingRoom(Room $room, User $user): void
    {
        $gameSession = $room->gameSession()->lockForUpdate()->firstOrFail();

        if ($gameSession->players()->count() >= 2) {
            throw new RoomFullException();
        }

        $pertama = $gameSession->players()->where('turn_order', 1)->firstOrFail();

        GamePlayer::create([
            'game_session_id' => $gameSession->id,
            'user_id' => $user->id,
            'is_robot' => false,
            'turn_order' => 2,
            'pawn_color' => 'red',
            'posisi_pion' => 0,
            'skor' => 0,
            'status' => PlayerStatus::Active,
            'last_heartbeat_at' => now(),
        ]);

        $gameSession->update([
            'status' => GameStatus::Playing,
            'current_turn_game_player_id' => $pertama->id,
            'started_at' => now(),
        ]);

        $room->update(['status' => GameStatus::Playing]);
    }

    private function createRoomWithFirstPlayer(User $user, RoomType $tipe, ?string $kodeRoom): Room
    {
        $room = Room::create([
            'kode_room' => $kodeRoom,
            'tipe' => $tipe,
            'status' => GameStatus::Waiting,
            'created_by' => $user->id,
            'expires_at' => now()->addMinutes($this->settings->getInt('room_waiting_expiry_minutes')),
        ]);

        $papan = PapanPermainan::query()->active()->inRandomOrder()->firstOrFail();

        $gameSession = GameSession::create([
            'room_id' => $room->id,
            'papan_id' => $papan->id,
            'mode' => GameMode::Multiplayer,
            'status' => GameStatus::Waiting,
            'random_seed' => Str::random(16),
            'total_turn' => 0,
        ]);

        GamePlayer::create([
            'game_session_id' => $gameSession->id,
            'user_id' => $user->id,
            'is_robot' => false,
            'turn_order' => 1,
            'pawn_color' => 'blue',
            'posisi_pion' => 0,
            'skor' => 0,
            'status' => PlayerStatus::Active,
            'last_heartbeat_at' => now(),
        ]);

        return $room->fresh();
    }
}
