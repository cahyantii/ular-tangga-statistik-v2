<?php

namespace App\Services\Game;

use App\Actions\GenerateRoomCode;
use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\PawnColor;
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
    public function quickMatch(User $user, ?string $pawnColor = null): ?Room
    {
        return Cache::lock('matchmaking-queue', 10)->block(5, function () use ($user, $pawnColor) {
            $this->gameSessionService->assertNoActiveSession($user);

            $room = DB::transaction(function () use ($user, $pawnColor) {
                $room = Room::query()
                    ->where('status', GameStatus::Waiting)
                    ->lockForUpdate()
                    ->get()
                    ->first(function ($r) {
                        $count = $r->gameSession?->players()->count() ?? 0;
                        return $count < $r->jumlah_pemain;
                    });

                if ($room) {
                    $this->joinExistingRoom($room, $user, $pawnColor);

                    return $room->fresh();
                }

                return null;
            });

            if ($room) {
                $this->notifyRoomOutcome($room, $user);
            }

            return $room;
        });
    }

    public function createPrivateRoom(User $user, ?string $pawnColor = null, int $jumlahPemain = 2): Room
    {
        $jumlahPemain = max(2, min(6, $jumlahPemain));

        return Cache::lock("create-session-user-{$user->id}", 10)->block(5, function () use ($user, $pawnColor, $jumlahPemain) {
            $this->gameSessionService->assertNoActiveSession($user);

            $room = DB::transaction(fn () => $this->createRoomWithFirstPlayer(
                $user,
                RoomType::Private,
                ($this->generateRoomCode)(),
                $pawnColor,
                $jumlahPemain,
            ));

            $this->notifyRoomOutcome($room, $user);

            return $room;
        });
    }

    public function joinPrivateRoom(User $user, string $kodeRoom, ?string $pawnColor = null): Room
    {
        return Cache::lock("create-session-user-{$user->id}", 10)->block(5, function () use ($user, $kodeRoom, $pawnColor) {
            $this->gameSessionService->assertNoActiveSession($user);

            $room = DB::transaction(function () use ($user, $kodeRoom, $pawnColor) {
                $room = Room::query()
                    ->where('kode_room', strtoupper($kodeRoom))
                    ->lockForUpdate()
                    ->firstOrFail();

                Gate::forUser($user)->authorize('join', $room);

                $this->joinExistingRoom($room, $user, $pawnColor);

                return $room->fresh();
            });

            $this->notifyRoomOutcome($room, $user);

            return $room;
        });
    }

    /**
     * Notifikasi Bell (Tahap 19) dikirim SETELAH transaksi commit (disiplin
     * yang sama dengan GameSessionService) — room baru terbentuk (fan-out
     * admin), pemain bergabung tapi belum penuh (room 3-6 pemain), atau room
     * terisi penuh (semua pemain yang sudah gabung diberi tahu permainan mulai).
     */
    private function notifyRoomOutcome(Room $room, User $actingUser): void
    {
        $jumlahBergabung = $room->gameSession?->players()->count() ?? 0;
        $sudahPenuh = $jumlahBergabung >= $room->jumlah_pemain;

        if ($sudahPenuh) {
            $room->gameSession->players()
                ->where('user_id', '!=', $actingUser->id)
                ->whereNotNull('user_id')
                ->get()
                ->each(fn ($p) => $this->notifications->send($p->user, $this->notifications->payloadOpponentJoined($room, $actingUser)));

            $this->notifications->sendToAdmins($this->notifications->payloadRoomActivityAdmin(
                $room,
                "{$actingUser->name} bergabung ke room {$room->kode_room}, permainan dimulai ({$jumlahBergabung}/{$room->jumlah_pemain} pemain)."
            ));

            return;
        }

        $pesan = $jumlahBergabung <= 1
            ? "{$actingUser->name} membuat room ({$room->tipe->label()}) dan menunggu pemain lain."
            : "{$actingUser->name} bergabung ke room {$room->kode_room} ({$jumlahBergabung}/{$room->jumlah_pemain} pemain), masih menunggu.";

        $this->notifications->sendToAdmins($this->notifications->payloadRoomActivityAdmin($room, $pesan));
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

    private function joinExistingRoom(Room $room, User $user, ?string $pawnColor = null): void
    {
        $gameSession = $room->gameSession()->lockForUpdate()->firstOrFail();
        $existingPlayers = $gameSession->players()->orderBy('turn_order')->get();

        if ($existingPlayers->count() >= $room->jumlah_pemain) {
            throw new RoomFullException();
        }

        $pertama = $existingPlayers->firstWhere('turn_order', 1);

        GamePlayer::create([
            'game_session_id' => $gameSession->id,
            'user_id' => $user->id,
            'is_robot' => false,
            'turn_order' => $existingPlayers->count() + 1,
            'pawn_color' => $this->resolvePawnColor($pawnColor, PawnColor::Merah->value, $existingPlayers->pluck('pawn_color')->all()),
            'posisi_pion' => 0,
            'skor' => 0,
            'status' => PlayerStatus::Active,
            'last_heartbeat_at' => now(),
        ]);

        // Room baru dimulai (status Playing + giliran pertama di-set) SETELAH
        // jumlah pemain mencapai kapasitas `jumlah_pemain` - sebelum penuh,
        // room/session tetap Waiting supaya pemain lain masih bisa gabung.
        if ($existingPlayers->count() + 1 >= $room->jumlah_pemain) {
            $gameSession->update([
                'status' => GameStatus::Playing,
                'current_turn_game_player_id' => $pertama->id,
                'started_at' => now(),
                'current_turn_started_at' => now(),
            ]);

            $room->update(['status' => GameStatus::Playing]);
        }
    }

    /**
     * Cegah dua pion warna sama di satu game session (mis. pemain baru
     * sengaja/tidak sengaja pilih warna yang sudah dipakai pemain lain yang
     * sudah gabung) — jatuh ke warna PawnColor pertama yang belum dipakai
     * siapa pun kalau bentrok.
     *
     * @param  string[]  $avoid
     */
    private function resolvePawnColor(?string $pawnColor, string $default, array $avoid = []): string
    {
        $color = $pawnColor ?? $default;

        if (in_array($color, $avoid, true)) {
            $fallback = collect(PawnColor::cases())->first(fn (PawnColor $c) => ! in_array($c->value, $avoid, true));

            return $fallback ? $fallback->value : $color;
        }

        return $color;
    }

    private function createRoomWithFirstPlayer(User $user, RoomType $tipe, ?string $kodeRoom, ?string $pawnColor = null, int $jumlahPemain = 2): Room
    {
        $room = Room::create([
            'kode_room' => $kodeRoom,
            'tipe' => $tipe,
            'jumlah_pemain' => $jumlahPemain,
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
            'pawn_color' => $pawnColor ?? PawnColor::Biru->value,
            'posisi_pion' => 0,
            'skor' => 0,
            'status' => PlayerStatus::Active,
            'last_heartbeat_at' => now(),
        ]);

        return $room->fresh();
    }
}
