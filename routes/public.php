<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\CertificateVerificationController;
use App\Http\Controllers\Public\FaqController;
use App\Http\Controllers\Public\HowToPlayController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Public\PrivacyController;
use App\Http\Controllers\Public\TermsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/how-to-play', [HowToPlayController::class, 'index'])->name('how-to-play');
Route::get('/faq', [FaqController::class, 'index'])->name('faq');
Route::get('/syarat-ketentuan', [TermsController::class, 'index'])->name('terms');
Route::get('/kebijakan-privasi', [PrivacyController::class, 'index'])->name('privacy');

Route::get('/sertifikat/verifikasi/{verificationCode}', [CertificateVerificationController::class, 'show'])
    ->name('certificate.verify');
