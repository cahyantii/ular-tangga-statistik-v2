<?php

use App\Http\Controllers\Player\AchievementController;
use App\Http\Controllers\Player\CertificateController;
use App\Http\Controllers\Player\DashboardController;
use App\Http\Controllers\Player\LeaderboardController;
use App\Http\Controllers\Player\ProgressController;
use Illuminate\Support\Facades\Route;

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard');

Route::prefix('player')->name('player.')->group(function () {
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements');
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress');
    Route::get('/sertifikat', [CertificateController::class, 'index'])->name('certificate');
    Route::get('/sertifikat/{certificate}/unduh', [CertificateController::class, 'download'])->name('certificate.download');
});
