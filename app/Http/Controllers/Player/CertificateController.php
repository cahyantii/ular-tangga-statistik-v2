<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function __construct(
        private readonly LearningProgressService $learningProgress,
        private readonly PlayerStatsService $playerStats,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $progress = $this->learningProgress->overallAggregate($user);
        $stats = $this->playerStats->sessionSummary($user);

        return view('player.certificate', [
            'certificate' => Certificate::query()->where('user_id', $user->id)->first(),
            'checklist' => [
                'kategori_selesai_label' => "{$progress['kategori_dengan_progress']}/{$progress['total_kategori_aktif']}",
                'kategori_selesai_terpenuhi' => $progress['semua_kategori_selesai'],
                'akurasi' => $progress['akurasi_keseluruhan'],
                'akurasi_terpenuhi' => $progress['akurasi_keseluruhan'] >= 80,
                'sudah_main_terpenuhi' => $stats['total_main'] >= 1,
            ],
        ]);
    }

    /**
     * Hanya pemilik sertifikat yang boleh mengunduh (Tahap 13c) — file PDF
     * disimpan di disk privat, tidak pernah punya URL publik langsung.
     */
    public function download(Request $request, Certificate $certificate): StreamedResponse
    {
        abort_unless($certificate->user_id === $request->user()->id, 403);

        return Storage::disk('local')->download(
            $certificate->file_path,
            "{$certificate->nomor_sertifikat}.pdf"
        );
    }
}
