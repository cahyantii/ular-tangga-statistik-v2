<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tambah nilai enum `forfeited` ke kolom `game_players.status` - dibutuhkan
 * supaya pemain yang timeout/keluar permanen di game 3-6 pemain bisa
 * di-skip terus dari rotasi giliran (lihat TurnService::nextPlayer()) TANPA
 * mengakhiri sesi untuk pemain lain yang masih aktif, beda dari
 * `disconnected` yang masih bisa reconnect (lihat GameSessionService).
 *
 * Kolom didefinisikan sebagai `enum()` MySQL murni (bukan lewat Doctrine
 * DBAL yang tidak ter-install di proyek ini) sehingga perlu ALTER TABLE
 * mentah, bukan `$table->enum(...)->change()`.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE game_players MODIFY status ENUM('active', 'disconnected', 'forfeited') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("UPDATE game_players SET status = 'disconnected' WHERE status = 'forfeited'");
        DB::statement("ALTER TABLE game_players MODIFY status ENUM('active', 'disconnected') NOT NULL DEFAULT 'active'");
    }
};
