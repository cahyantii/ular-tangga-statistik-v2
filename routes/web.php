<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Player\FeedbackController;
use App\Http\Controllers\Player\ProfileController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/public.php';

// Verifikasi email kini diwajibkan untuk SEMUA role (player & admin), bukan
// admin-only lagi seperti keputusan lama - middleware 'verified' dipasang di
// sini supaya dashboard, game, dan seluruh area pemain tercakup.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

    // Notifikasi Bell (Tahap 19): dipakai BERSAMA admin & player - beroperasi
    // murni pada auth()->user()->notifications(), lihat NotificationController.
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::patch('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::patch('/{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    require __DIR__.'/player.php';

    Route::prefix('main')->name('game.')->group(base_path('routes/game.php'));
});

// Verifikasi email hanya diwajibkan untuk Admin (Tahap 13) - middleware 'verified'
// sengaja TIDAK dipasang di grup player/game, hanya di grup admin ini.
Route::middleware(['auth', 'admin.verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

require __DIR__.'/auth.php';
