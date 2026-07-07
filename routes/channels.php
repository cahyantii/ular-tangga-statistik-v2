<?php

use App\Models\GamePlayer;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

/**
 * Presence channel untuk room multiplayer (Tahap 12/19).
 * Hanya user yang terdaftar sebagai peserta (game_players) pada sesi milik room ini
 * yang boleh subscribe - mencegah user lain menguping/menyusup ke room orang lain.
 */
Broadcast::channel('presence-room.{roomId}', function (User $user, int $roomId) {
    $isParticipant = GamePlayer::query()
        ->where('user_id', $user->id)
        ->whereHas('gameSession', fn ($query) => $query->where('room_id', $roomId))
        ->exists();

    return $isParticipant ? ['id' => $user->id, 'name' => $user->name] : false;
});
