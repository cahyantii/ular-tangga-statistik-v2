<?php

namespace Tests\Unit\Events\Game;

use App\Enums\ConnectorType;
use App\Enums\GameMode;
use App\Enums\ScoreEventType;
use App\Enums\WinReason;
use App\Events\Game\ConnectorApplied;
use App\Events\Game\DiceRolled;
use App\Events\Game\GameFinished;
use App\Events\Game\MovementBlocked;
use App\Events\Game\PawnMoved;
use App\Events\Game\QuestionPresented;
use App\Events\Game\ScoreUpdated;
use App\Events\Game\SessionPaused;
use App\Events\Game\SessionResumed;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Room;
use App\Models\Soal;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Tahap 12b: setiap event Game harus broadcast HANYA untuk Multiplayer (Vs
 * Robot cukup lewat respons HTTP, tidak perlu channel realtime), ke channel
 * presence-room.{roomId} yang sudah diotorisasi di routes/channels.php, dan
 * TIDAK PERNAH membocorkan data sensitif (kunci_jawaban/opsi/pertanyaan,
 * random_seed) lewat broadcastWith().
 */
class GameEventsBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    private function multiplayerSession(): GameSession
    {
        $room = Room::factory()->create();

        return GameSession::factory()->create([
            'room_id' => $room->id,
            'mode' => GameMode::Multiplayer,
        ]);
    }

    private function vsRobotSession(): GameSession
    {
        return GameSession::factory()->create([
            'room_id' => null,
            'mode' => GameMode::VsRobot,
        ]);
    }

    public static function eventFactories(): array
    {
        return [
            'DiceRolled' => [fn (GameSession $s, GamePlayer $p) => new DiceRolled($s, $p, 4)],
            'PawnMoved' => [fn (GameSession $s, GamePlayer $p) => new PawnMoved($s, $p, 1, 5)],
            'MovementBlocked' => [fn (GameSession $s, GamePlayer $p) => new MovementBlocked($s, $p, 6)],
            'ConnectorApplied' => [fn (GameSession $s, GamePlayer $p) => new ConnectorApplied($s, $p, ConnectorType::Tangga, 8, 22)],
            'ScoreUpdated' => [fn (GameSession $s, GamePlayer $p) => new ScoreUpdated($s, $p, ScoreEventType::Bonus, 20, 20)],
            'SessionPaused' => [fn (GameSession $s, GamePlayer $p) => new SessionPaused($s, $p)],
            'SessionResumed' => [fn (GameSession $s, GamePlayer $p) => new SessionResumed($s, $p)],
        ];
    }

    #[DataProvider('eventFactories')]
    public function test_event_is_broadcastable_and_carries_no_full_models(callable $make): void
    {
        $sesi = $this->multiplayerSession();
        $player = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);

        $event = $make($sesi, $player);

        $this->assertInstanceOf(ShouldBroadcast::class, $event);

        $payload = $event->broadcastWith();
        $this->assertSame($sesi->id, $payload['game_session_id']);
        $this->assertArrayNotHasKey('random_seed', $payload);
        $this->assertArrayNotHasKey('gameSession', $payload);
        $this->assertArrayNotHasKey('gamePlayer', $payload);
    }

    #[DataProvider('eventFactories')]
    public function test_event_broadcasts_only_for_multiplayer_sessions(callable $make): void
    {
        $sesi = $this->multiplayerSession();
        $player = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);

        $channels = $make($sesi, $player)->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(PresenceChannel::class, $channels[0]);
        $this->assertSame('presence-room.'.$sesi->room_id, $channels[0]->name);
    }

    #[DataProvider('eventFactories')]
    public function test_event_does_not_broadcast_for_vs_robot_sessions(callable $make): void
    {
        $sesi = $this->vsRobotSession();
        $player = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);

        $channels = $make($sesi, $player)->broadcastOn();

        $this->assertSame([], $channels);
    }

    public function test_question_presented_never_broadcasts_the_question_content(): void
    {
        $sesi = $this->multiplayerSession();
        $player = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);
        $soal = Soal::factory()->create(['kunci_jawaban' => 'B']);

        $event = new QuestionPresented($sesi, $player, $soal, 30);
        $payload = $event->broadcastWith();

        $this->assertSame($soal->id, $payload['soal_id']);
        $this->assertArrayNotHasKey('pertanyaan', $payload);
        $this->assertArrayNotHasKey('opsi_jawaban', $payload);
        $this->assertArrayNotHasKey('kunci_jawaban', $payload);
        $this->assertArrayNotHasKey('pembahasan', $payload);
    }

    public function test_game_finished_broadcasts_winner_without_leaking_random_seed(): void
    {
        $sesi = $this->multiplayerSession();
        $pemenang = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);

        $event = new GameFinished($sesi, $pemenang, WinReason::Finish);
        $payload = $event->broadcastWith();

        $this->assertSame($pemenang->id, $payload['winner_game_player_id']);
        $this->assertSame('finish', $payload['win_reason']);
        $this->assertArrayNotHasKey('random_seed', $payload);
    }

    public function test_broadcast_event_names_are_kebab_case(): void
    {
        $sesi = $this->multiplayerSession();
        $player = GamePlayer::factory()->create(['game_session_id' => $sesi->id]);

        $this->assertSame('dice-rolled', (new DiceRolled($sesi, $player, 3))->broadcastAs());
        $this->assertSame('question-presented', (new QuestionPresented($sesi, $player, Soal::factory()->create(), 30))->broadcastAs());
        $this->assertSame('session-paused', (new SessionPaused($sesi, $player))->broadcastAs());
    }
}
