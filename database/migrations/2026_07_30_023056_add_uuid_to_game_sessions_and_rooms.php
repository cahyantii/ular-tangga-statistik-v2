<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        // Populate existing
        DB::table('game_sessions')->orderBy('id')->chunk(100, function ($sessions) {
            foreach ($sessions as $session) {
                DB::table('game_sessions')->where('id', $session->id)->update(['uuid' => Str::uuid()->toString()]);
            }
        });

        DB::table('rooms')->orderBy('id')->chunk(100, function ($rooms) {
            foreach ($rooms as $room) {
                DB::table('rooms')->where('id', $room->id)->update(['uuid' => Str::uuid()->toString()]);
            }
        });

        // Make non-nullable and add unique index
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
