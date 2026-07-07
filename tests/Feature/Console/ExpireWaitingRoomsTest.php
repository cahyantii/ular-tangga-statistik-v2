<?php

namespace Tests\Feature\Console;

use App\Enums\GameStatus;
use App\Models\GameSession;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireWaitingRoomsTest extends TestCase
{
    use RefreshDatabase;

    public function test_abandons_a_waiting_room_past_its_expiry(): void
    {
        $room = Room::factory()->create(['status' => GameStatus::Waiting, 'expires_at' => now()->subMinute()]);
        $sesi = GameSession::factory()->create(['room_id' => $room->id, 'status' => GameStatus::Waiting]);

        $this->artisan('game:expire-waiting-rooms')->assertExitCode(0);

        $this->assertSame(GameStatus::Abandoned, $room->fresh()->status);
        $this->assertSame(GameStatus::Abandoned, $sesi->fresh()->status);
        $this->assertNotNull($sesi->fresh()->finished_at);
    }

    public function test_does_not_touch_a_room_that_has_not_expired_yet(): void
    {
        $room = Room::factory()->create(['status' => GameStatus::Waiting, 'expires_at' => now()->addMinutes(5)]);

        $this->artisan('game:expire-waiting-rooms')->assertExitCode(0);

        $this->assertSame(GameStatus::Waiting, $room->fresh()->status);
    }

    public function test_does_not_touch_a_room_that_is_already_playing(): void
    {
        $room = Room::factory()->create(['status' => GameStatus::Playing, 'expires_at' => now()->subMinute()]);

        $this->artisan('game:expire-waiting-rooms')->assertExitCode(0);

        $this->assertSame(GameStatus::Playing, $room->fresh()->status);
    }
}
