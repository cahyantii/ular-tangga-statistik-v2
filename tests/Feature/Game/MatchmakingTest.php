<?php

namespace Tests\Feature\Game;

use App\Enums\GameStatus;
use App\Enums\RoomType;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PapanPermainan;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchmakingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        PapanPermainan::factory()->create(['is_active' => true]);
    }

    public function test_lobby_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('game.multiplayer.lobby'));

        $response->assertOk();
        $response->assertSee('Quick Match');
        $response->assertSee('Private Room');
    }

    public function test_quick_match_creates_a_waiting_room_when_none_available(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('game.multiplayer.quick-match'));

        $room = Room::query()->latest('id')->firstOrFail();
        $response->assertRedirect(route('game.room.show', $room));
        $this->assertSame(RoomType::QuickMatch, $room->tipe);
        $this->assertSame(GameStatus::Waiting, $room->status);
        $this->assertSame(GameStatus::Waiting, $room->gameSession->status);
        $this->assertCount(1, $room->gameSession->players);
    }

    public function test_second_quick_match_joins_the_waiting_room_and_starts_the_game(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->post(route('game.multiplayer.quick-match'));
        $room = Room::query()->latest('id')->firstOrFail();

        $response = $this->actingAs($second)->post(route('game.multiplayer.quick-match'));

        $response->assertRedirect(route('game.room.show', $room));
        $room->refresh();
        $this->assertSame(GameStatus::Playing, $room->status);
        $this->assertSame(GameStatus::Playing, $room->gameSession->status);
        $this->assertCount(2, $room->gameSession->players);
        $this->assertNotNull($room->gameSession->current_turn_game_player_id);
        $this->assertNotNull($room->gameSession->started_at);
    }

    public function test_user_with_a_waiting_session_cannot_queue_for_another(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('game.multiplayer.quick-match'));

        $response = $this->actingAs($user)->post(route('game.multiplayer.quick-match'));

        $response->assertSessionHas('error');
        $this->assertCount(1, Room::all());
    }

    public function test_private_room_can_be_created_and_joined_via_code(): void
    {
        $creator = User::factory()->create();
        $joiner = User::factory()->create();

        $this->actingAs($creator)->post(route('game.multiplayer.room.store'));
        $room = Room::query()->latest('id')->firstOrFail();

        $this->assertSame(RoomType::Private, $room->tipe);
        $this->assertNotNull($room->kode_room);

        $response = $this->actingAs($joiner)->post(route('game.multiplayer.room.join'), [
            'kode_room' => $room->kode_room,
        ]);

        $response->assertRedirect(route('game.room.show', $room));
        $room->refresh();
        $this->assertSame(GameStatus::Playing, $room->status);
        $this->assertCount(2, $room->gameSession->players);
    }

    public function test_joining_with_an_unknown_room_code_shows_an_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('game.multiplayer.room.join'), [
            'kode_room' => 'ZZZZZZ',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_cannot_join_a_room_that_is_already_full(): void
    {
        $creator = User::factory()->create();
        $second = User::factory()->create();
        $third = User::factory()->create();

        $this->actingAs($creator)->post(route('game.multiplayer.room.store'));
        $room = Room::query()->latest('id')->firstOrFail();
        $this->actingAs($second)->post(route('game.multiplayer.room.join'), ['kode_room' => $room->kode_room]);

        // RoomPolicy::join sudah menolak lebih dulu (403) begitu room != Waiting -
        // RoomFullException/422 khusus menjaga race condition di dalam transaksi
        // (dua join nyaris bersamaan lolos policy check tapi tidak keduanya bisa masuk).
        $response = $this->actingAs($third)->postJson(route('game.multiplayer.room.join'), ['kode_room' => $room->kode_room]);

        $response->assertStatus(403);
    }

    public function test_only_the_creator_can_cancel_a_waiting_room(): void
    {
        $creator = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($creator)->post(route('game.multiplayer.room.store'));
        $room = Room::query()->latest('id')->firstOrFail();

        $this->actingAs($other)->post(route('game.room.cancel', $room))->assertStatus(403);

        $response = $this->actingAs($creator)->post(route('game.room.cancel', $room));
        $response->assertRedirect(route('dashboard'));

        $room->refresh();
        $this->assertSame(GameStatus::Abandoned, $room->status);
        $this->assertSame(GameStatus::Abandoned, $room->gameSession->status);
    }

    public function test_non_participant_cannot_view_someone_elses_room(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($creator)->post(route('game.multiplayer.room.store'));
        $room = Room::query()->latest('id')->firstOrFail();

        $this->actingAs($outsider)->get(route('game.room.show', $room))->assertStatus(403);
    }

    public function test_waiting_room_view_shows_room_code_and_cancel_button_for_creator(): void
    {
        $creator = User::factory()->create();
        $this->actingAs($creator)->post(route('game.multiplayer.room.store'));
        $room = Room::query()->latest('id')->firstOrFail();

        $response = $this->actingAs($creator)->get(route('game.room.show', $room));

        $response->assertOk();
        $response->assertSee($room->kode_room);
        $response->assertSee('Batalkan Room');
    }

    public function test_multiplayer_leave_immediately_forfeits_to_the_opponent(): void
    {
        $sesi = GameSession::factory()->create(['mode' => \App\Enums\GameMode::Multiplayer, 'status' => GameStatus::Playing]);
        $p1 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 1]);
        $p2 = GamePlayer::factory()->create(['game_session_id' => $sesi->id, 'turn_order' => 2]);
        $sesi->update(['current_turn_game_player_id' => $p1->id]);

        $response = $this->actingAs($p1->user)->post("/main/{$sesi->id}/leave");

        $response->assertRedirect(route('dashboard'));
        $sesiSegar = $sesi->fresh();
        $this->assertSame(GameStatus::Finished, $sesiSegar->status);
        $this->assertSame($p2->id, $sesiSegar->winner_game_player_id);
        $this->assertSame(\App\Enums\WinReason::Forfeit, $sesiSegar->win_reason);
    }
}
