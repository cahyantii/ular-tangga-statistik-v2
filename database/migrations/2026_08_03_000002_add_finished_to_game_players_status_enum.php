<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambah nilai enum `finished` ke kolom `game_players.status` — dibutuhkan
 * untuk sistem multi-winner (Juara 1, 2, 3) supaya pemain yang sudah
 * menyentuh garis Finish bisa di-skip dari rotasi giliran tanpa mengakhiri
 * sesi permainan untuk pemain lain yang masih aktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE game_players MODIFY status ENUM('active', 'disconnected', 'forfeited', 'finished') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE game_players SET status = 'active' WHERE status = 'finished'");
        DB::statement("ALTER TABLE game_players MODIFY status ENUM('active', 'disconnected', 'forfeited') NOT NULL DEFAULT 'active'");
    }
};
