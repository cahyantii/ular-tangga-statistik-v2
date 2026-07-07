<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\Achievement\AchievementEvaluationService;
use App\Services\Game\PlayerStatsService;
use App\Services\Progress\LearningProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly PlayerStatsService $playerStats,
        private readonly LearningProgressService $learningProgress,
        private readonly AchievementEvaluationService $achievementEvaluation,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = $this->playerStats->sessionSummary($user);
        $activeSession = $this->playerStats->activeSession($user);
        $progress = $this->learningProgress->overallAggregate($user);
        $progressPerKategori = $this->learningProgress->perKategori($user);
        $nearestAchievement = $this->achievementEvaluation->nearestUpcoming($user);
        $totalAchievementDiraih = $user->userAchievements()->count();
        $sudahPunyaSertifikat = Certificate::query()->where('user_id', $user->id)->exists();

        return view('player.dashboard', [
            'stats' => $stats,
            'activeSession' => $activeSession,
            'progress' => $progress,
            'progressPerKategori' => $progressPerKategori,
            'nearestAchievement' => $nearestAchievement,
            'totalAchievementDiraih' => $totalAchievementDiraih,
            'sudahPunyaSertifikat' => $sudahPunyaSertifikat,
            'sertifikatChecklist' => [
                'kategori_selesai_label' => "{$progress['kategori_dengan_progress']}/{$progress['total_kategori_aktif']}",
                'kategori_selesai_terpenuhi' => $progress['semua_kategori_selesai'],
                'akurasi' => $progress['akurasi_keseluruhan'],
                'akurasi_terpenuhi' => $progress['akurasi_keseluruhan'] >= 80,
                'sudah_main_terpenuhi' => $stats['total_main'] >= 1,
            ],
        ]);
    }
}
