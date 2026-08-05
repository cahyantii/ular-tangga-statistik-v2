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
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->timestamp('current_turn_started_at')->nullable()->after('finished_at');
        });

        Schema::table('game_players', function (Blueprint $table) {
            $table->integer('auto_rolls_count')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn('current_turn_started_at');
        });

        Schema::table('game_players', function (Blueprint $table) {
            $table->dropColumn('auto_rolls_count');
        });
    }
};
