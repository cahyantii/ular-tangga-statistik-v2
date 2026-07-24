<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Verifikasi email kini diwajibkan untuk SEMUA role (sebelumnya hanya admin,
 * lihat komentar lama di routes/web.php "Tahap 13"). Akun yang sudah ada
 * sebelum perubahan ini di-grandfather (dianggap terverifikasi) supaya tidak
 * ada pengguna yang tiba-tiba terkunci dari dashboard karena aturan baru -
 * hanya pendaftaran BARU setelah migrasi ini yang wajib verifikasi email.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    /**
     * Data backfill tidak reversibel (tidak ada cara mengetahui baris mana
     * yang sebelumnya null) - down() sengaja no-op.
     */
    public function down(): void
    {
        //
    }
};
