<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hapus nilai enum 'soal', 'bonus', 'penalti' dari kolom `petak.jenis_petak`.
 * Ketiga jenis petak ini tidak lagi digunakan dalam permainan — hanya
 * 'start', 'finish', 'biasa', 'tangga', 'ular', dan 'mystery' yang aktif.
 * Data sudah dikonversi ke 'biasa' sebelum migration ini dijalankan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Fallback safety: pastikan tidak ada sisa data dengan jenis lama
        DB::statement("UPDATE petak SET jenis_petak = 'biasa' WHERE jenis_petak IN ('soal', 'bonus', 'penalti')");

        DB::statement("ALTER TABLE petak MODIFY jenis_petak ENUM('start', 'finish', 'biasa', 'tangga', 'ular', 'mystery') NOT NULL DEFAULT 'biasa'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE petak MODIFY jenis_petak ENUM('start', 'finish', 'biasa', 'soal', 'tangga', 'ular', 'bonus', 'penalti', 'mystery') NOT NULL DEFAULT 'biasa'");
    }
};
