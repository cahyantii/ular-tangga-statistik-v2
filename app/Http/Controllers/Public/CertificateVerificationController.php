<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\View\View;

/**
 * Verifikasi keaslian sertifikat (Tahap 1/13b, keputusan final) — halaman
 * publik, tidak perlu login, ditujukan bagi siapa pun yang menerima
 * sertifikat cetak/PDF dan ingin memastikan keasliannya lewat kode verifikasi
 * yang tercetak di dalamnya. Sengaja hanya menampilkan ringkasan non-sensitif
 * (nama, judul, tanggal terbit) — bukan file PDF itu sendiri.
 */
class CertificateVerificationController extends Controller
{
    public function show(string $verificationCode): View
    {
        $certificate = Certificate::query()
            ->with('user')
            ->where('verification_code', strtoupper($verificationCode))
            ->first();

        return view('certificates.verify', [
            'certificate' => $certificate,
        ]);
    }
}
