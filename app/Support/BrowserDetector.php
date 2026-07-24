<?php

namespace App\Support;

/**
 * Deteksi nama browser dari header User-Agent memakai pencocokan string
 * sederhana (tanpa dependency tambahan) - cukup akurat untuk keperluan
 * tampilan "Browser: ..." di email notifikasi login, bukan untuk analitik
 * presisi tinggi. Urutan pengecekan penting: Edge/Opera berbasis Chromium
 * jadi ikut mengandung "Chrome/" di UA-nya, harus dicek lebih dulu.
 */
class BrowserDetector
{
    public static function name(?string $userAgent): string
    {
        if (blank($userAgent)) {
            return 'Tidak diketahui';
        }

        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Microsoft Edge',
            str_contains($userAgent, 'OPR/') || str_contains($userAgent, 'Opera') => 'Opera',
            str_contains($userAgent, 'Firefox/') => 'Mozilla Firefox',
            str_contains($userAgent, 'Chrome/') => 'Google Chrome',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Tidak diketahui',
        };
    }
}
