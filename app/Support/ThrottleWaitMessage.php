<?php

namespace App\Support;

/**
 * Format pesan "tunggu X detik/menit" dalam Bahasa Indonesia dari sisa waktu
 * throttle (retry_after) yang dihitung dinamis - dipakai bersama oleh
 * PasswordResetLinkController (throttle internal Password broker) dan
 * bootstrap/app.php (ThrottleRequestsException dari middleware `throttle:6,1`
 * pada route forgot-password) supaya kedua sumber throttle menampilkan gaya
 * pesan yang sama alih-alih string bawaan Laravel ("Please wait before
 * retrying." / "Too Many Attempts.").
 */
class ThrottleWaitMessage
{
    public static function forgotPassword(int $retryAfterSeconds): string
    {
        return 'Anda telah meminta link reset password terlalu sering. Silakan tunggu '
            .self::formatDuration($retryAfterSeconds)
            .' sebelum mencoba lagi.';
    }

    private static function formatDuration(int $seconds): string
    {
        $seconds = max(1, $seconds);

        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return "{$minutes} menit {$remainingSeconds} detik";
    }
}
