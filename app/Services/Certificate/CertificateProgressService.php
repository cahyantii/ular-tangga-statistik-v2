<?php

namespace App\Services\Certificate;

use App\Models\User;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;

/**
 * Checklist syarat Sertifikat Penguasaan Statistik Dasar (halaman Sertifikat
 * Digital) — SATU-SATUNYA tempat yang menerjemahkan agregat progress/statistik
 * pemain (LearningProgressService, PlayerStatsService) menjadi 3 langkah
 * bertimeline beserta status & pesan aksinya. Ambang batas (akurasi minimal,
 * jumlah permainan minimal, hadiah poin) didefinisikan di sini karena baru
 * ada satu jenis sertifikat di aplikasi ini — bukan tabel referensi terpisah.
 */
class CertificateProgressService
{
    public const MINIMAL_AKURASI = 80;

    public const MINIMAL_PERMAINAN = 1;

    public const REWARD_POIN = 200;

    public function __construct(
        private readonly LearningProgressService $learningProgress,
        private readonly PlayerStatsService $playerStats,
    ) {
    }

    public function checklist(User $user): array
    {
        $progress = $this->learningProgress->overallAggregate($user);
        $stats = $this->playerStats->sessionSummary($user);

        $kategoriSelesai = $progress['kategori_dengan_progress'];
        $kategoriTotal = $progress['total_kategori_aktif'];
        $kategoriDone = $progress['semua_kategori_selesai'];
        $kategoriPercent = $kategoriTotal > 0
            ? min(100, (int) round($kategoriSelesai / $kategoriTotal * 100))
            : 0;
        $kategoriSisa = max($kategoriTotal - $kategoriSelesai, 0);

        $akurasi = $progress['akurasi_keseluruhan'];
        $akurasiDone = $akurasi >= self::MINIMAL_AKURASI;
        $akurasiPercent = min(100, (int) round($akurasi));

        $totalMain = $stats['total_main'];
        $mainDone = $totalMain >= self::MINIMAL_PERMAINAN;
        $mainPercent = min(100, (int) round($totalMain / self::MINIMAL_PERMAINAN * 100));

        $steps = [
            [
                'key' => 'kategori',
                'title' => 'Semua kategori materi selesai',
                'value_label' => "{$kategoriSelesai}/{$kategoriTotal}",
                'percent' => $kategoriPercent,
                'done' => $kategoriDone,
                'tone' => 'green',
                'action_icon' => $kategoriDone ? 'trophy' : 'book',
                'action_message' => $kategoriDone
                    ? 'Selesai! Mantap!'
                    : ($kategoriSisa > 0 ? "{$kategoriSisa} kategori lagi!" : 'Yuk mulai belajar!'),
            ],
            [
                'key' => 'akurasi',
                'title' => 'Akurasi keseluruhan &ge; '.self::MINIMAL_AKURASI.'%',
                'value_label' => $this->formatPercent($akurasi).'%',
                'percent' => $akurasiPercent,
                'done' => $akurasiDone,
                'tone' => 'purple',
                'action_icon' => $akurasiDone ? 'trophy' : 'trend-up',
                'action_message' => $akurasiDone ? 'Akurasimu mantap!' : 'Terus tingkatkan akurasimu!',
            ],
            [
                'key' => 'permainan',
                'title' => 'Minimal '.self::MINIMAL_PERMAINAN.' permainan selesai',
                'value_label' => $mainDone ? "{$totalMain} permainan" : 'Belum',
                'percent' => $mainPercent,
                'done' => $mainDone,
                'tone' => 'blue',
                'action_icon' => $mainDone ? 'trophy' : 'gamepad',
                'action_message' => $mainDone ? 'Sudah main, mantap!' : 'Yuk main dan selesaikan!',
            ],
        ];

        return [
            'steps' => $steps,
            'eligible' => $kategoriDone && $akurasiDone && $mainDone,
            'reward_poin' => self::REWARD_POIN,
        ];
    }

    private function formatPercent(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1), '0'), '.');
    }
}
