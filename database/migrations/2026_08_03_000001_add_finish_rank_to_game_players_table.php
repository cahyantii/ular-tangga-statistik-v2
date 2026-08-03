<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->unsignedTinyInteger('finish_rank')->nullable()->after('posisi_pion');
            $table->unsignedInteger('finished_at_turn')->nullable()->after('finish_rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->dropColumn(['finish_rank', 'finished_at_turn']);
        });
    }
};
