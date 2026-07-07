<?php

use App\Http\Controllers\Admin\Management\AchievementController;
use App\Http\Controllers\Admin\Management\GameSettingController;
use App\Http\Controllers\Admin\Management\KategoriMateriController;
use App\Http\Controllers\Admin\Management\MateriController;
use App\Http\Controllers\Admin\Management\PapanKonektorController;
use App\Http\Controllers\Admin\Management\PapanPermainanController;
use App\Http\Controllers\Admin\Management\PapanPreviewController;
use App\Http\Controllers\Admin\Management\PetakController;
use App\Http\Controllers\Admin\Management\SoalController;
use App\Http\Controllers\Admin\Management\SoalImportController;
use App\Http\Controllers\Admin\Management\UserController;
use Illuminate\Support\Facades\Route;

Route::patch('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
Route::resource('users', UserController::class)->except(['show']);

Route::patch('kategori-materi/{kategori_materi}/restore', [KategoriMateriController::class, 'restore'])->name('kategori-materi.restore');
Route::resource('kategori-materi', KategoriMateriController::class)->except(['show']);

Route::patch('materi/{materi}/restore', [MateriController::class, 'restore'])->name('materi.restore');
Route::resource('materi', MateriController::class)->except(['show']);

Route::get('soal/import', [SoalImportController::class, 'create'])->name('soal.import.create');
Route::post('soal/import/preview', [SoalImportController::class, 'preview'])->name('soal.import.preview');
Route::post('soal/import/confirm', [SoalImportController::class, 'confirm'])->name('soal.import.confirm');
Route::get('soal/export', [SoalController::class, 'export'])->name('soal.export');
Route::patch('soal/{soal}/restore', [SoalController::class, 'restore'])->name('soal.restore');
Route::resource('soal', SoalController::class)->except(['show']);

Route::patch('papan-permainan/{papan_permainan}/restore', [PapanPermainanController::class, 'restore'])->name('papan-permainan.restore');
Route::patch('papan-permainan/{papan_permainan}/toggle-active', [PapanPermainanController::class, 'toggleActive'])->name('papan-permainan.toggle-active');
Route::get('papan-permainan/{papan_permainan}/preview', [PapanPreviewController::class, 'show'])->name('papan-permainan.preview');

Route::get('papan-permainan/{papan_permainan}/petak', [PetakController::class, 'index'])->name('papan-permainan.petak.index');
Route::get('papan-permainan/{papan_permainan}/petak/{petak}/edit', [PetakController::class, 'edit'])->name('papan-permainan.petak.edit');
Route::put('papan-permainan/{papan_permainan}/petak/{petak}', [PetakController::class, 'update'])->name('papan-permainan.petak.update');

Route::resource('papan-permainan.konektor', PapanKonektorController::class)->except(['show']);

Route::resource('papan-permainan', PapanPermainanController::class)->except(['show']);

Route::patch('achievements/{achievement}/restore', [AchievementController::class, 'restore'])->name('achievements.restore');
Route::resource('achievements', AchievementController::class)->except(['show']);

Route::get('game-settings', [GameSettingController::class, 'index'])->name('game-settings.index');
Route::put('game-settings', [GameSettingController::class, 'update'])->name('game-settings.update');
