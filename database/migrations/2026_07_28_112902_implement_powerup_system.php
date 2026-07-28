<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->json('inventory')->nullable()->after('status');
            $table->json('active_buffs')->nullable()->after('inventory');
        });

        // Konversi petak tipe lama menjadi mystery
        DB::table('petak')
            ->whereIn('jenis_petak', ['soal', 'bonus', 'penalti'])
            ->update(['jenis_petak' => 'mystery']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->dropColumn(['inventory', 'active_buffs']);
        });
    }
};
