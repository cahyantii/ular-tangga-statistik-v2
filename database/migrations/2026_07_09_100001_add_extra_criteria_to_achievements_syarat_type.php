<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menambah 2 jenis kriteria achievement baru (Tahap Achievement Page redesign):
 * - selisih_kemenangan_terbesar: selisih posisi_pion terjauh saat menang.
 * - total_angka_enam: total dadu bernilai 6 sepanjang riwayat permainan.
 *
 * Kolom `syarat_type` dibuat sebagai MySQL ENUM di migration awal, jadi
 * menambah nilai baru butuh ALTER TABLE eksplisit (Schema::table biasa tidak
 * bisa mengubah daftar nilai ENUM).
 */
return new class extends Migration
{
    public function up(): void
    {
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
        DB::statement("ALTER TABLE achievements MODIFY syarat_type ENUM(
            'total_menang',
            'akurasi_keseluruhan',
            'total_permainan'
        ) NOT NULL");
    }
};
