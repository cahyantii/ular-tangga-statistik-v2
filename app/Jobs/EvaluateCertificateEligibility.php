<?php

namespace App\Jobs;

use App\Actions\GenerateCertificateNumber;
use App\Actions\GenerateVerificationCode;
use App\Models\Certificate;
use App\Models\User;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

/**
 * Sertifikat Digital (Tahap 1, keputusan final): diberikan HANYA jika ketiganya
 * terpenuhi sekaligus — (a) semua kategori materi selesai, (b) akurasi
 * keseluruhan >=80%, (c) minimal satu permainan selesai. Dipanggil dari
 * GameSessionService::finishSession() untuk setiap partisipan manusia setiap
 * sesi selesai (Tahap 13a "Queue untuk proses non-inti" pattern yang sama).
 *
 * Satu jenis sertifikat, sekali seumur hidup per user — jika sudah punya,
 * job ini tidak pernah membuat yang kedua.
 */
class EvaluateCertificateEligibility implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    private const JENIS_SERTIFIKAT = 'penguasaan_statistik_dasar';

    private const AKURASI_MINIMAL = 80;

    public function __construct(public readonly User $user)
    {
    }

    public function handle(
        LearningProgressService $learningProgress,
        PlayerStatsService $playerStats,
        GenerateCertificateNumber $generateCertificateNumber,
        GenerateVerificationCode $generateVerificationCode,
    ): void {
        if (Certificate::query()->where('user_id', $this->user->id)->exists()) {
            return;
        }

        $progress = $learningProgress->overallAggregate($this->user);
        $stats = $playerStats->sessionSummary($this->user);

        $eligible = $progress['semua_kategori_selesai']
            && $progress['akurasi_keseluruhan'] >= self::AKURASI_MINIMAL
            && $stats['total_main'] >= 1;

        if (! $eligible) {
            return;
        }

        $verificationCode = $generateVerificationCode();
        $filePath = 'certificates/'.$verificationCode.'.pdf';

        $certificate = Certificate::create([
            'user_id' => $this->user->id,
            'jenis_sertifikat' => self::JENIS_SERTIFIKAT,
            'judul' => 'Sertifikat Penguasaan Statistik Dasar',
            'nomor_sertifikat' => $generateCertificateNumber(),
            'verification_code' => $verificationCode,
            'template_path' => 'certificates.template',
            'file_path' => $filePath,
            'issued_at' => now(),
        ]);

        Pdf::loadView($certificate->template_path, [
            'user' => $this->user,
            'certificate' => $certificate,
            'akurasi' => $progress['akurasi_keseluruhan'],
        ])->save($filePath, 'local');
    }
}
