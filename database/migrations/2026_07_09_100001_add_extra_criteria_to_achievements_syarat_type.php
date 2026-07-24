<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah 2 jenis kriteria achievement baru (Tahap Achievement Page redesign):
 * - selisih_kemenangan_terbesar: selisih posisi_pion terjauh saat menang.
 * - total_angka_enam: total dadu bernilai 6 sepanjang riwayat permainan.
 *
 * Kolom `syarat_type` dibuat sebagai MySQL ENUM di migration awal, jadi
 * menambah nilai baru butuh ALTER TABLE eksplisit (Schema::table biasa tidak
 * bisa mengubah daftar nilai ENUM). SQLite (dipakai untuk test suite, lihat
 * phpunit.xml) tidak mendukung sintaks MODIFY ini sama sekali — di driver itu
 * kolom diubah jadi string biasa; keamanan tipe tetap terjaga lewat cast enum
 * PHP `AchievementCriteriaType` di model Achievement, jadi CHECK constraint
 * di level DB SQLite tidak dibutuhkan lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('achievements', function (Blueprint $table) {
                $table->string('syarat_type')->change();
            });

            return;
        }

        DB::statement("ALTER TABLE achievements MODIFY syarat_type ENUM(
            'total_menang',
            'akurasi_keseluruhan',
            'total_permainan',
            'selisih_kemenangan_terbesar',
            'total_angka_enam'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE achievements MODIFY syarat_type ENUM(
            'total_menang',
            'akurasi_keseluruhan',
            'total_permainan'
        ) NOT NULL");
    }
};
