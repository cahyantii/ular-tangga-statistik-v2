<?php

namespace App\Services\Progress;

use App\Models\KategoriMateri;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Sumber tunggal agregat progress belajar seorang pemain.
 *
 * `overallAggregate()` dipakai BERSAMA oleh Job EvaluateCertificateEligibility
 * (Tahap Sertifikat) dan widget Progress/Sertifikat di Player Dashboard (Tahap 15,
 * keputusan final) — jangan duplikasi query ini di tempat lain.
 *
 * Definisi "kategori selesai": pemain sudah pernah menjawab minimal satu soal
 * pada kategori tersebut (ada baris `learning_progress` dengan total_dijawab > 0).
 * Tabel `learning_progress` pada ERD final hanya melacak progres menjawab soal,
 * bukan progres membaca materi, sehingga ini adalah interpretasi yang konsisten
 * dengan skema yang ada.
 *
 * Cache key `player.stats.progress.{userId}` (Tahap 15, keputusan final) HARUS
 * di-forget() di dalam Job UpdateLearningProgress pada tahap Game Engine, setelah
 * job tersebut commit.
 */
class LearningProgressService
{
    private const TTL_MINUTES = 5;

    public function cacheKey(User $user): string
    {
        return "player.stats.progress.{$user->id}";
    }

    public function overallAggregate(User $user): array
    {
        return Cache::remember(
            $this->cacheKey($user),
            now()->addMinutes(self::TTL_MINUTES),
            function () use ($user) {
                $totalKategoriAktif = KategoriMateri::query()->active()->count();

                $rows = $user->learningProgress()->get();
                $kategoriDenganProgress = $rows->filter(fn ($row) => $row->total_dijawab > 0)->count();
                $totalDijawab = (int) $rows->sum('total_dijawab');
                $totalBenar = (int) $rows->sum('total_benar');
                $akurasiKeseluruhan = $totalDijawab > 0
                    ? round(($totalBenar / $totalDijawab) * 100, 2)
                    : 0.0;

                return [
                    'total_kategori_aktif' => $totalKategoriAktif,
                    'kategori_dengan_progress' => $kategoriDenganProgress,
                    'semua_kategori_selesai' => $totalKategoriAktif > 0 && $kategoriDenganProgress >= $totalKategoriAktif,
                    'total_dijawab' => $totalDijawab,
                    'total_benar' => $totalBenar,
                    'akurasi_keseluruhan' => $akurasiKeseluruhan,
                ];
            }
        );
    }

    /**
     * Rincian progress per kategori aktif (untuk widget progress belajar),
     * termasuk kategori yang belum pernah dimainkan sama sekali (0%).
     *
     * `progress_percent` & `status` (Tahap Progress Belajar redesign) mengukur
     * CAKUPAN bank soal kategori (total_dijawab vs total soal aktif) — berbeda
     * dari `akurasi` (ketepatan jawaban). Keduanya dipakai berdampingan di
     * halaman Progress: kartu ringkasan menampilkan akurasi keseluruhan,
     * baris per kategori menampilkan progres cakupan.
     */
    public function perKategori(User $user): Collection
    {
        $progressByKategori = $user->learningProgress()->get()->keyBy('kategori_id');

        return KategoriMateri::query()
            ->active()
            ->withCount(['soal' => fn ($query) => $query->active()])
            ->orderBy('urutan')
            ->get()
            ->map(function (KategoriMateri $kategori) use ($progressByKategori) {
                $progress = $progressByKategori->get($kategori->id);
                $totalDijawab = $progress->total_dijawab ?? 0;
                $totalSoal = $kategori->soal_count;
                $progressPercent = $totalSoal > 0
                    ? min(100, (int) round($totalDijawab / $totalSoal * 100))
                    : 0;

                $status = $totalDijawab === 0
                    ? 'belum_dimulai'
                    : ($progressPercent >= 100 ? 'selesai' : 'sedang_belajar');

                return [
                    'kategori_id' => $kategori->id,
                    'kategori' => $kategori->nama,
                    'icon' => $kategori->icon ?? 'book',
                    'total_dijawab' => $totalDijawab,
                    'total_soal' => $totalSoal,
                    'akurasi' => $progress ? (float) $progress->accuracy : 0.0,
                    'progress_percent' => $progressPercent,
                    'status' => $status,
                ];
            });
    }
}
