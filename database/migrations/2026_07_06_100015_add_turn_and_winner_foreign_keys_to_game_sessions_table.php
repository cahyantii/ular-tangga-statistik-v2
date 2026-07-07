<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->foreignId('current_turn_game_player_id')
                ->nullable()
                ->after('status')
                ->constrained('game_players')
                ->nullOnDelete();

            $table->foreignId('winner_game_player_id')
                ->nullable()
                ->after('win_reason')
                ->constrained('game_players')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_turn_game_player_id');
            $table->dropConstrainedForeignId('winner_game_player_id');
        });
    }
};
