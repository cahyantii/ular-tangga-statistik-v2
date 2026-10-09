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
 *
 * Nama channel di sini TANPA prefix "presence-": Laravel membuang prefix itu
 * sebelum mencocokkan pola, jadi 'presence-room.{roomId}' tidak pernah cocok
 * dan otorisasi selalu ditolak (403). Event tetap dikirim lewat
 * PresenceChannel('room.{id}') dan frontend tetap Echo.join('room.{id}').
 */
Broadcast::channel('room.{roomId}', function (User $user, int $roomId) {
    $isParticipant = GamePlayer::query()
        ->where('user_id', $user->id)
        ->whereHas('gameSession', fn ($query) => $query->where('room_id', $roomId))
        ->exists();

    return $isParticipant ? ['id' => $user->id, 'name' => $user->name] : false;
});
