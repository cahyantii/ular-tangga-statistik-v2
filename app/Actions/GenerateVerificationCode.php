<?php

namespace App\Actions;

use App\Models\Certificate;
use Illuminate\Support\Str;

class GenerateVerificationCode
{
    /**
     * Generate kode verifikasi unik untuk sertifikat (dipakai di halaman verifikasi publik).
     */
    public function __invoke(): string
    {
        do {
            $code = strtoupper(Str::random(16));
        } while (Certificate::where('verification_code', $code)->exists());

        return $code;
    }
}
