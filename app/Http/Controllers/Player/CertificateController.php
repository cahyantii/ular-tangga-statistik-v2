<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\Certificate\CertificateProgressService;
use App\Services\Progress\LearningProgressService;
use App\Services\Progress\TipsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    public function __construct(
        private readonly LearningProgressService $learningProgress,
        private readonly CertificateProgressService $certificateProgress,
        private readonly TipsService $tips,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $perKategori = $this->learningProgress->perKategori($user);

        return view('player.certificate', [
            'certificate' => Certificate::query()->where('user_id', $user->id)->first(),
            'checklist' => $this->certificateProgress->checklist($user),
            'tip' => $this->tips->dailyTip($user, $perKategori),
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
