<?php

namespace App\Services\Profile;

use App\Models\User;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;
use App\Services\Progress\LevelService;

/**
 * Ringkasan tampilan halaman Profil — level/XP (LevelService), statistik
 * permainan (PlayerStatsService), dan akurasi (LearningProgressService)
 * SUDAH masing-masing dihitung & di-cache oleh service tersebut; kelas ini
 * hanya merangkainya menjadi bentuk siap-tampil, tanpa query baru.
 */
class ProfileSummaryService
{
    public function __construct(
        private readonly LevelService $level,
        private readonly PlayerStatsService $playerStats,
        private readonly LearningProgressService $learningProgress,
    ) {
    }

    public function summary(User $user): array
    {
        $level = $this->level->snapshot($user);
        $stats = $this->playerStats->sessionSummary($user);
        $progress = $this->learningProgress->overallAggregate($user);

        return [
            'level' => $level['level'],
            'level_name' => $level['level_name'],
            'badge_color' => $level['badge_color'],
            'poin' => $level['xp'],
            'total_permainan' => $stats['total_main'],
            'rata_rata_akurasi' => $progress['akurasi_keseluruhan'],
            'waktu_belajar_label' => $this->formatDurasi($stats['total_waktu_detik']),
        ];
    }

    private function formatDurasi(int $detik): string
    {
        $jam = intdiv($detik, 3600);
        $menit = intdiv($detik % 3600, 60);

        if ($jam > 0 && $menit > 0) {
            return "{$jam} Jam {$menit} Menit";
        }

        if ($jam > 0) {
            return "{$jam} Jam";
        }

        return "{$menit} Menit";
    }
}
