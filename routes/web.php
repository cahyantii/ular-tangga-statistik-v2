<?php

use App\Http\Controllers\Player\ProfileController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/public.php';

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');

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
