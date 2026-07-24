<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `certificates.user_id` sebelumnya hanya punya index biasa (bukan unique),
 * padahal komentar EvaluateCertificateEligibility sendiri menyatakan "satu
 * jenis sertifikat, sekali seumur hidup per user". Tanpa constraint di level
 * database, pengecekan "sudah punya sertifikat?" di awal job (baca-lalu-tulis
 * tanpa lock) punya jendela race: dua job untuk user yang sama yang berjalan
 * nyaris bersamaan (dua sesi permainan selesai berdekatan) bisa lolos
 * pengecekan itu bersamaan dan masing-masing membuat baris sertifikat
 * sendiri-sendiri. Constraint unique ini jadi jaring pengaman level DB yang
 * tidak bisa ditembus race apa pun - dipasangkan dengan penanganan
 * UniqueConstraintViolationException di EvaluateCertificateEligibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
