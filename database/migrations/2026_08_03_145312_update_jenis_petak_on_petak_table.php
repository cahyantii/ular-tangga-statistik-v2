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
        DB::table('petak')->whereIn('jenis_petak', ['soal', 'bonus', 'penalti'])->update(['jenis_petak' => 'biasa']);
        DB::statement("ALTER TABLE petak MODIFY COLUMN jenis_petak ENUM('start', 'finish', 'biasa', 'tangga', 'ular', 'mystery') DEFAULT 'biasa'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE petak MODIFY COLUMN jenis_petak ENUM('start', 'finish', 'biasa', 'soal', 'tangga', 'ular', 'bonus', 'penalti', 'mystery') DEFAULT 'biasa'");
    }
};
