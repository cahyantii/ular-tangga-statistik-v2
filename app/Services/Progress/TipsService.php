<?php

namespace App\Services\Progress;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Tip belajar harian (kartu "Tips Hari Ini" di halaman Progress Belajar).
 *
 * Jika pemain punya kategori dengan akurasi rendah, tip diarahkan langsung
 * ke kategori tersebut (dipersonalisasi dari data `perKategori()`). Jika
 * belum ada data untuk dipersonalisasi, tip umum dipilih secara berputar
 * berdasarkan hari & user, bukan selalu tip pertama yang sama.
 */
class TipsService
{
    private const AMBANG_AKURASI_RENDAH = 80;

    private const TIPS_UMUM = [
        'Kerjakan soal secara rutin untuk meningkatkan pemahaman dan akurasi jawabanmu!',
        'Bermain bersama teman membantumu belajar sambil bersenang-senang.',
        'Baca pembahasan setiap soal, bukan cuma jawabannya, biar makin paham konsepnya.',
        'Konsisten belajar sedikit setiap hari lebih efektif daripada belajar borongan sekali waktu.',
        'Coba kategori yang belum pernah kamu mainkan untuk menambah wawasan baru.',
        'Ulangi soal yang pernah salah kamu jawab agar tidak terulang lagi.',
    ];

    private const TIPS_KEAMANAN = [
        'Gunakan password yang kuat dan jangan bagikan akunmu kepada siapa pun.',
        'Jangan gunakan password yang sama dengan akun lain di luar aplikasi ini.',
        'Selalu keluar (logout) setelah bermain di perangkat bersama, seperti komputer sekolah.',
        'Segera ubah password jika kamu merasa akunmu digunakan orang lain.',
        'Waspada terhadap pesan yang meminta password atau kode verifikasimu.',
    ];

    public function dailyTip(User $user, Collection $perKategori): string
    {
        $weakest = $perKategori
            ->where('total_dijawab', '>', 0)
            ->sortBy('akurasi')
            ->first();

        if ($weakest && $weakest['akurasi'] < self::AMBANG_AKURASI_RENDAH) {
            return sprintf(
                'Akurasimu di kategori "%s" masih %s%%. Latihan lagi di kategori itu supaya makin mantap!',
                $weakest['kategori'],
                rtrim(rtrim(number_format($weakest['akurasi'], 1), '0'), '.')
            );
        }

        $index = (now()->dayOfYear + $user->id) % count(self::TIPS_UMUM);

        return self::TIPS_UMUM[$index];
    }

    public function securityTip(): string
    {
        $index = now()->dayOfYear % count(self::TIPS_KEAMANAN);

        return self::TIPS_KEAMANAN[$index];
    }
}
